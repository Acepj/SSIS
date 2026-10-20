<?php

declare(strict_types=1);

use SSIS\Controllers\AuthController;

/*
 * API route table. Each entry:
 *   [HTTP method, path pattern, [ControllerClass, action], [middleware...]]
 *
 * Middleware tokens:
 *   auth        - requires a valid active session (AuthMiddleware)
 *   admin       - requires an active administrator (AdminMiddleware)
 *   csrf        - validates X-CSRF-Token on state-changing requests
 *   rate:<key>  - applies a configured rate limit (e.g. rate:login)
 *
 * Student and admin route groups are appended in Phases 4 and 5.
 */

return [
    // --- Authentication -----------------------------------------------------
    ['POST', '/api/v1/auth/login', [AuthController::class, 'login'], ['csrf', 'rate:login']],
    ['POST', '/api/v1/auth/logout', [AuthController::class, 'logout'], ['csrf']],
    ['GET', '/api/v1/auth/me', [AuthController::class, 'me'], ['auth']],
    ['GET', '/api/v1/auth/csrf', [AuthController::class, 'csrf'], []],
];
