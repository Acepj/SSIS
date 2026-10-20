<?php

declare(strict_types=1);

namespace SSIS\Middleware;

use SSIS\Exceptions\AuthorizationException;
use SSIS\Helpers\Csrf;
use SSIS\Helpers\Request;

/**
 * Validates the X-CSRF-Token header on every cookie-authenticated
 * state-changing request (POST/PATCH/PUT/DELETE).
 */
final class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $token = $request->header('X-CSRF-Token');
        if (!Csrf::validate($token)) {
            throw new AuthorizationException(
                'The security token is missing or invalid. Please refresh the page and try again.',
                'CSRF_TOKEN_INVALID',
            );
        }
    }
}
