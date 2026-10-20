<?php

declare(strict_types=1);

namespace SSIS\Helpers;

/**
 * Safe application logging to storage/logs/app.log (never web-accessible).
 * Secrets (passwords, tokens, cookies) are redacted before writing.
 * Log details stay server-side and are never returned in API responses.
 */
final class Logger
{
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function exception(\Throwable $exception, array $context = []): void
    {
        self::log('error', sprintf('%s: %s in %s:%d', $exception::class, $exception->getMessage(), $exception->getFile(), $exception->getLine()), $context);
    }

    private static function log(string $level, string $message, array $context): void
    {
        try {
            $dir = (string) Config::get('storage.logs_path', '');
            if ($dir === '') {
                return;
            }
            if (!is_dir($dir)) {
                @mkdir($dir, (int) Config::get('storage.dir_permissions', 0700), true);
            }

            $line = sprintf(
                "[%s] %s: %s%s%s",
                gmdate('Y-m-d\TH:i:s\Z'),
                strtoupper($level),
                $message,
                $context !== [] ? ' ' . (json_encode(self::redact($context), JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR) ?: '') : '',
                PHP_EOL,
            );

            $handle = @fopen($dir . '/app.log', 'ab');
            if ($handle === false) {
                return;
            }
            flock($handle, LOCK_EX);
            fwrite($handle, $line);
            flock($handle, LOCK_UN);
            fclose($handle);
            @chmod($dir . '/app.log', 0600);
        } catch (\Throwable) {
            // Logging must never break a request.
        }
    }

    private static function redact(array $context, int $depth = 0): array
    {
        if ($depth > 3) {
            return $context;
        }

        $secrets = ['password', 'password_hash', 'token', 'csrf', 'cookie', 'secret', 'authorization'];
        $clean = [];
        foreach ($context as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $secrets, true)) {
                $clean[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $clean[$key] = self::redact($value, $depth + 1);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
