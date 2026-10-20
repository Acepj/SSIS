<?php

declare(strict_types=1);

namespace SSIS\Helpers;

use SSIS\Exceptions\AuthenticationException;
use SSIS\Exceptions\AuthorizationException;

/**
 * Centralized permission checks based on the server-side session only.
 * The browser never decides its own role.
 */
final class Authorization
{
    public static function userId(): ?string
    {
        $userId = Session::get('user_id');

        return is_string($userId) && $userId !== '' ? $userId : null;
    }

    public static function role(): ?string
    {
        $role = Session::get('role');

        return is_string($role) && $role !== '' ? $role : null;
    }

    public static function requireAuth(): string
    {
        $userId = self::userId();
        if ($userId === null) {
            throw new AuthenticationException();
        }

        return $userId;
    }

    public static function requireAdmin(): string
    {
        $userId = self::requireAuth();
        if (self::role() !== 'admin') {
            throw new AuthorizationException('Administrator access is required.', 'FORBIDDEN');
        }

        return $userId;
    }
}
