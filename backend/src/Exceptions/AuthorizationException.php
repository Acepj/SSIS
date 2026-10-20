<?php

declare(strict_types=1);

namespace SSIS\Exceptions;

/** Authenticated but not permitted (or CSRF failure). Always maps to HTTP 403. */
class AuthorizationException extends \Exception
{
    public function __construct(
        string $message = 'You do not have permission to perform this action.',
        private readonly string $errorCode = 'FORBIDDEN',
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
