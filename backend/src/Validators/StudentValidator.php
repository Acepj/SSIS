<?php

declare(strict_types=1);

namespace SSIS\Validators;

use SSIS\Exceptions\ValidationException;

/**
 * Validation for student profile updates. Only contact fields are accepted -
 * official academic information is never student-editable.
 */
final class StudentValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, string> normalized permitted fields
     *
     * @throws ValidationException when input is invalid (HTTP 422)
     */
    public function validateProfileUpdate(array $input): array
    {
        $errors = [];
        $clean = [];

        if (array_key_exists('email', $input)) {
            $email = is_string($input['email']) ? trim($input['email']) : '';
            if ($email === '') {
                $errors['email'] = 'Email address is required.';
            } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Enter a valid email address.';
            } elseif (mb_strlen($email) > 190) {
                $errors['email'] = 'The email address must be 190 characters or fewer.';
            } else {
                $clean['email'] = $email;
            }
        }

        if (array_key_exists('contactNumber', $input)) {
            $contactNumber = is_string($input['contactNumber']) ? trim($input['contactNumber']) : '';
            if ($contactNumber === '') {
                $errors['contactNumber'] = 'Contact number is required.';
            } elseif (preg_match('/^[0-9+\s()\-]{7,25}$/', $contactNumber) !== 1) {
                $errors['contactNumber'] = 'Enter a valid contact number (digits, spaces, +, -, parentheses).';
            } else {
                $clean['contactNumber'] = $contactNumber;
            }
        }

        if ($clean === [] && $errors === []) {
            throw new ValidationException(
                'No valid fields were provided.',
                ['email' => 'Nothing to update.'],
            );
        }

        if ($errors !== []) {
            throw new ValidationException('Please review the submitted information.', $errors);
        }

        return $clean;
    }
}
