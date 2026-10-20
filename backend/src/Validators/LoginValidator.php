<?php

declare(strict_types=1);

namespace SSIS\Validators;

use SSIS\Exceptions\ValidationException;

/**
 * Server-side validation for login attempts. Field keys match the frontend
 * login form so errors can be displayed inline.
 */
final class LoginValidator
{
    /**
     * @param array<string, mixed> $input raw request payload
     * @return array{identifier: string, password: string, remember: bool}
     *
     * @throws ValidationException when input is invalid (HTTP 422)
     */
    public function validate(array $input): array
    {
        $errors = [];

        $identifier = isset($input['identifier']) && is_string($input['identifier']) ? trim($input['identifier']) : '';
        $password = isset($input['password']) && is_string($input['password']) ? $input['password'] : '';

        if ($identifier === '') {
            $errors['identifier'] = 'Student ID or administrator username is required.';
        } elseif (strlen($identifier) > 64) {
            $errors['identifier'] = 'The identifier must be 64 characters or fewer.';
        } elseif (preg_match('/^[A-Za-z0-9._@-]+$/', $identifier) !== 1) {
            $errors['identifier'] = 'Only letters, numbers, and the characters . _ - @ are allowed.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) > 128) {
            $errors['password'] = 'The password must be 128 characters or fewer.';
        }

        if ($errors !== []) {
            throw new ValidationException('Please review the submitted information.', $errors);
        }

        return [
            'identifier' => $identifier,
            'password' => $password,
            'remember' => filter_var($input['remember'] ?? false, FILTER_VALIDATE_BOOL),
        ];
    }
}
