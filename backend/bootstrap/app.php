<?php

declare(strict_types=1);

/*
 * Application bootstrap: environment loading, autoloading, configuration,
 * fail-closed error handling and sessions.
 *
 * Required by public/index.php (HTTP) and every CLI script under scripts/.
 */

define('SSIS_BASE_PATH', dirname(__DIR__));

// ---------------------------------------------------------------------------
// Environment
// ---------------------------------------------------------------------------

/**
 * Reads an environment variable with an optional default.
 * Real process environment variables take precedence over .env entries.
 */
function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

// Minimal .env parser (no dependency). .env is git-ignored; .env.example has
// placeholders only. Lines: KEY=VALUE, # comments, optional quotes.
(function (): void {
    $envFile = SSIS_BASE_PATH . '/.env';
    if (!is_file($envFile) || !is_readable($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $name = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if ($name === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
            continue;
        }
        if (preg_match('/^(["\'])(.*)\1$/', $value, $matches) === 1) {
            $value = $matches[2];
        }
        if (getenv($name) === false) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
})();

// ---------------------------------------------------------------------------
// Fail-closed output configuration: PHP warnings/notices must never corrupt
// JSON API responses - they are converted to exceptions and logged instead.
// ---------------------------------------------------------------------------

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// ---------------------------------------------------------------------------
// Autoloading: Composer when installed, PSR-4 fallback otherwise
// ---------------------------------------------------------------------------

$composerAutoload = SSIS_BASE_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'SSIS\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = SSIS_BASE_PATH . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// ---------------------------------------------------------------------------
// Configuration + timezone
// ---------------------------------------------------------------------------

SSIS\Helpers\Config::load(SSIS_BASE_PATH);
date_default_timezone_set((string) SSIS\Helpers\Config::get('app.timezone', 'UTC'));

// ---------------------------------------------------------------------------
// Error handling -> structured JSON only; details go to storage/logs/app.log
// ---------------------------------------------------------------------------

set_error_handler(static function (int $severity, string $message, string $file = '', int $line = 0): bool {
    if ((error_reporting() & $severity) === 0) {
        return false; // silenced with @ - let PHP's default handling proceed
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $exception): void {
    try {
        SSIS\Helpers\Logger::exception($exception);
        if (!headers_sent()) {
            SSIS\Helpers\Response::error('SERVER_ERROR', 'An unexpected server error occurred.', 500);
        }
    } catch (Throwable) {
        // Never let the error handler itself fail.
    }
    exit(1);
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }
    try {
        SSIS\Helpers\Logger::error('Fatal error: ' . $error['message'], [
            'file' => $error['file'],
            'line' => $error['line'],
        ]);
        if (!headers_sent()) {
            SSIS\Helpers\Response::error('SERVER_ERROR', 'An unexpected server error occurred.', 500);
        }
    } catch (Throwable) {
        // Nothing more can be done during shutdown.
    }
});

// ---------------------------------------------------------------------------
// Sessions (authoritative authentication state). Skipped for CLI scripts.
// ---------------------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    SSIS\Helpers\Session::start();
}

return SSIS_BASE_PATH;
