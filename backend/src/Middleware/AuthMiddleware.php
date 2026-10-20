<?php

declare(strict_types=1);

namespace SSIS\Middleware;

use SSIS\Exceptions\AuthenticationException;
use SSIS\Exceptions\AuthorizationException;
use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Helpers\Session;
use SSIS\Repositories\UserRepository;

/**
 * Rejects requests without a valid, active authenticated session, optionally
 * requiring a specific role (e.g. "auth:student"). The role in the session
 * is refreshed from the stored account on every request, so browser-supplied
 * data never grants access.
 */
final class AuthMiddleware
{
    public function __construct(
        private readonly ?string $requiredRole = null,
        private readonly ?UserRepository $users = null,
    ) {
    }

    public function handle(Request $request): void
    {
        $userId = Authorization::requireAuth();

        $users = $this->users ?? new UserRepository();
        $user = $users->findById($userId);

        if ($user === null || ($user['active'] ?? false) !== true) {
            Session::destroy();
            throw new AuthenticationException('Your session is no longer valid.', 'UNAUTHENTICATED');
        }

        // Authoritative role always comes from stored records.
        Session::set('role', (string) $user['role']);

        if ($this->requiredRole !== null && ($user['role'] ?? '') !== $this->requiredRole) {
            throw new AuthorizationException('You do not have permission to perform this action.', 'FORBIDDEN');
        }
    }
}
