<?php

declare(strict_types=1);

namespace SSIS\Helpers;

/**
 * Session-bound CSRF tokens (double-submit via the X-CSRF-Token header).
 * The frontend obtains a token from GET /api/v1/auth/csrf before any
 * state-changing request; CsrfMiddleware validates it.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public static function validate(?string $token): bool
    {
        $stored = Session::get(self::SESSION_KEY);

        return is_string($token)
            && $token !== ''
            && is_string($stored)
            && $stored !== ''
            && hash_equals($stored, $token);
    }
}
