<?php

declare(strict_types=1);

namespace SSIS\Helpers;

/**
 * Centralized secure session configuration.
 *
 * - HttpOnly + SameSite=Lax cookies (Secure under HTTPS)
 * - Strict mode: uninitialized session IDs are rejected
 * - Session ID is regenerated after login (handled by AuthService)
 * - Server-side data is the only authentication state
 */
final class Session
{
    public static function start(): void
    {
        if (PHP_SAPI === 'cli' || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = (int) Config::get('app.session.lifetime', 604800);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) $lifetime);

        session_name((string) Config::get('app.session.name', 'ssis_session'));
        session_set_cookie_params([
            'lifetime' => 0, // browser-session cookie unless "remember me" re-issues it
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => (string) Config::get('app.session.same_site', 'Lax'),
        ]);

        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Regenerates the session ID, destroying the old one (post-login hygiene). */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Re-issues the session cookie with a longer expiry for "remember me".
     * The session ID itself does not change.
     */
    public static function rememberCookie(int $lifetime): void
    {
        if (headers_sent() || session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        setcookie(session_name(), session_id(), [
            'expires' => time() + $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => (string) Config::get('app.session.same_site', 'Lax'),
        ]);
    }

    /** Destroys all server-side session data and expires the cookie. */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]);
            }
            session_destroy();
        }
    }

    private static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }
}
