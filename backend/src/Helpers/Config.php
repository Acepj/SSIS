<?php

declare(strict_types=1);

namespace SSIS\Helpers;

/**
 * Central configuration access with dotted keys, e.g. Config::get('app.name').
 */
final class Config
{
    /** @var array<string, mixed> */
    private static array $values = [];

    public static function load(string $baseDir): void
    {
        self::$values = [
            'app' => require $baseDir . '/config/app.php',
            'cors' => require $baseDir . '/config/cors.php',
            'storage' => require $baseDir . '/config/storage.php',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$values;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /** Replaces a configuration value (used by tests). */
    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $target = &self::$values;
        foreach ($segments as $index => $segment) {
            if ($index === count($segments) - 1) {
                $target[$segment] = $value;
                return;
            }
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }
            $target = &$target[$segment];
        }
    }
}
