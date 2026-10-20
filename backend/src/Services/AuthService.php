<?php

declare(strict_types=1);

namespace SSIS\Services;

use SSIS\Exceptions\AuthenticationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\Logger;
use SSIS\Helpers\Session;
use SSIS\Repositories\StudentRepository;
use SSIS\Repositories\UserRepository;

/**
 * Authentication business rules: credential verification, session
 * establishment/teardown and safe user presentation.
 *
 * Security invariants:
 * - Passwords only ever checked with password_verify() against stored hashes.
 * - Role is read from users.json, never from the request.
 * - Session ID is regenerated on successful login.
 * - Inactive accounts cannot authenticate (and lose their session).
 */
final class AuthService
{
    private readonly UserRepository $users;
    private readonly StudentRepository $students;

    public function __construct(?UserRepository $users = null, ?StudentRepository $students = null)
    {
        $this->users = $users ?? new UserRepository();
        $this->students = $students ?? new StudentRepository();
    }

    /**
     * @return array{user: array<string, mixed>}
     *
     * @throws AuthenticationException on bad credentials or inactive account
     */
    public function login(string $identifier, string $password, bool $remember): array
    {
        $user = $this->users->findByUsername($identifier);

        $valid = $user !== null
            && is_string($user['password_hash'] ?? null)
            && password_verify($password, $user['password_hash']);

        if (!$valid) {
            Logger::info('Rejected login attempt with invalid credentials.', [
                'identifier' => strtolower(trim($identifier)),
            ]);
            throw new AuthenticationException(
                'The identifier or password you entered is incorrect.',
                'INVALID_CREDENTIALS',
            );
        }

        if (($user['active'] ?? false) !== true) {
            Logger::info('Rejected login attempt for deactivated account.', [
                'identifier' => strtolower(trim($identifier)),
            ]);
            throw new AuthenticationException(
                'This account has been deactivated. Please contact Student Services for assistance.',
                'ACCOUNT_INACTIVE',
            );
        }

        // Opportunistic rehash when PHP's default algorithm changes.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->users->update((string) $user['id'], [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }

        // Fresh session ID prevents fixation; role comes from stored records.
        Session::regenerate();
        Session::set('user_id', (string) $user['id']);
        Session::set('role', (string) $user['role']);

        if ($remember) {
            Session::rememberCookie((int) Config::get('app.session.lifetime', 604800));
        }

        Logger::info('User signed in.', ['user_id' => $user['id'], 'role' => $user['role']]);

        return ['user' => $this->presentUser($user)];
    }

    public function logout(): void
    {
        $userId = Session::get('user_id');
        Session::destroy();

        if (is_string($userId) && $userId !== '') {
            Logger::info('User signed out.', ['user_id' => $userId]);
        }
    }

    /**
     * Current authenticated user from the session + stored records.
     *
     * @return array{user: array<string, mixed>}
     *
     * @throws AuthenticationException when the session is missing/invalid
     */
    public function currentUser(): array
    {
        $userId = Session::get('user_id');
        if (!is_string($userId) || $userId === '') {
            throw new AuthenticationException();
        }

        $user = $this->users->findById($userId);
        if ($user === null || ($user['active'] ?? false) !== true) {
            Session::destroy();
            throw new AuthenticationException('Your session is no longer valid.', 'UNAUTHENTICATED');
        }

        return ['user' => $this->presentUser($user)];
    }

    /** Builds an explicit payload - password hashes never leave the service. */
    private function presentUser(array $user): array
    {
        $displayName = (string) ($user['display_name'] ?? '');
        if (($user['role'] ?? '') === 'student') {
            $student = $this->students->findByUserId((string) $user['id']);
            $displayName = (string) ($student['full_name'] ?? '');
        }
        if ($displayName === '') {
            $displayName = (string) $user['username'];
        }

        return [
            'id' => (string) $user['id'],
            'role' => (string) $user['role'],
            'displayName' => $displayName,
            'identifier' => (string) $user['username'],
            'active' => (bool) $user['active'],
        ];
    }
}
