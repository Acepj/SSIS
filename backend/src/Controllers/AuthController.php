<?php

declare(strict_types=1);

namespace SSIS\Controllers;

use SSIS\Helpers\Csrf;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Services\AuthService;
use SSIS\Validators\LoginValidator;

/**
 * Authentication endpoints:
 *   POST /api/v1/auth/login   - create a session for valid credentials
 *   POST /api/v1/auth/logout  - destroy the server-side session
 *   GET  /api/v1/auth/me      - current authenticated user
 *   GET  /api/v1/auth/csrf    - CSRF token for state-changing requests
 */
final class AuthController
{
    private readonly AuthService $auth;
    private readonly LoginValidator $validator;

    public function __construct(?AuthService $auth = null, ?LoginValidator $validator = null)
    {
        $this->auth = $auth ?? new AuthService();
        $this->validator = $validator ?? new LoginValidator();
    }

    public function login(Request $request): never
    {
        $input = $this->validator->validate($request->all());
        $result = $this->auth->login($input['identifier'], $input['password'], $input['remember']);

        Response::success($result, 'Signed in successfully.');
    }

    public function logout(Request $request): never
    {
        $this->auth->logout();

        Response::success(null, 'Signed out successfully.');
    }

    public function me(Request $request): never
    {
        Response::success($this->auth->currentUser());
    }

    public function csrf(Request $request): never
    {
        Response::success(['token' => Csrf::token()]);
    }
}
