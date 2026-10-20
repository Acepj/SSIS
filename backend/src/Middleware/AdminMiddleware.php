<?php

declare(strict_types=1);

namespace SSIS\Middleware;

use SSIS\Exceptions\AuthenticationException;
use SSIS\Exceptions\AuthorizationException;
use SSIS\Helpers\Authorization;
use SSIS\Helpers\Request;
use SSIS\Repositories\UserRepository;

/**
 * Requires an authenticated, active administrator account.
 * The role is re-read from stored records - never from the request.
 */
final class AdminMiddleware
{
    public function __construct(private readonly ?UserRepository $users = null)
    {
    }

    public function handle(Request $request): void
    {
        $userId = Authorization::requireAuth();

        $users = $this->users ?? new UserRepository();
        $user = $users->findById($userId);

        if ($user === null || ($user['active'] ?? false) !== true) {
            throw new AuthenticationException('Your session is no longer valid.', 'UNAUTHENTICATED');
        }

        if (($user['role'] ?? '') !== 'admin') {
            throw new AuthorizationException('Administrator access is required.', 'FORBIDDEN');
        }
    }
}
