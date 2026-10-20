<?php

declare(strict_types=1);

namespace SSIS\Middleware;

use SSIS\Exceptions\RateLimitException;
use SSIS\Helpers\Config;
use SSIS\Helpers\Request;

/**
 * Basic file-backed request limiting for sensitive endpoints (e.g. login).
 * Buckets are keyed per IP + submitted identifier inside a sliding window.
 * Attempts are persisted under storage/logs (never web-accessible).
 */
final class RateLimitMiddleware
{
    public function __construct(private readonly string $bucket)
    {
    }

    public function handle(Request $request): void
    {
        $limit = Config::get('app.rate_limits.' . $this->bucket);
        if (!is_array($limit)) {
            return;
        }

        $max = (int) ($limit['max'] ?? 0);
        $window = (int) ($limit['window'] ?? 900);
        if ($max <= 0) {
            return;
        }

        $key = sprintf(
            '%s|%s|%s',
            $this->bucket,
            $request->ip(),
            strtolower((string) $request->input('identifier', '')),
        );

        $dir = (string) Config::get('storage.logs_path', '');
        if ($dir === '') {
            return;
        }
        if (!is_dir($dir)) {
            @mkdir($dir, (int) Config::get('storage.dir_permissions', 0700), true);
        }

        $file = $dir . '/rate-limit.json';
        $handle = @fopen($file, 'c+');
        if ($handle === false) {
            // Fail open if the limiter itself cannot run; login still requires
            // valid credentials. The failure is not silently ignored by Logger
            // at this layer to avoid request overhead on every call.
            return;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return;
            }

            $now = time();
            $raw = stream_get_contents($handle);
            $buckets = is_string($raw) && trim($raw) !== '' ? json_decode($raw, true) : null;
            if (!is_array($buckets)) {
                $buckets = [];
            }

            // Prune expired timestamps for every bucket (bounded file size).
            foreach ($buckets as $bucketKey => $timestamps) {
                if (!is_array($timestamps)) {
                    unset($buckets[$bucketKey]);
                    continue;
                }
                $kept = array_values(array_filter($timestamps, static fn ($ts): bool => is_int($ts) && ($ts > $now - $window)));
                if ($kept === []) {
                    unset($buckets[$bucketKey]);
                } else {
                    $buckets[$bucketKey] = $kept;
                }
            }

            $attempts = $buckets[$key] ?? [];
            if (count($attempts) >= $max) {
                $oldest = min($attempts);
                $retryAfter = max(1, $window - ($now - $oldest));
                flock($handle, LOCK_UN);
                fclose($handle);

                throw new RateLimitException(
                    sprintf('Too many sign-in attempts. Please wait %d seconds and try again.', $retryAfter),
                    $retryAfter,
                );
            }

            $attempts[] = $now;
            $buckets[$key] = $attempts;

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($buckets, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR) ?: '[]');
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            @chmod($file, 0600);
        }
    }
}
