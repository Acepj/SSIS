<?php

declare(strict_types=1);

namespace SSIS\Exceptions;

/** Missing/invalid session or failed credentials. Always maps to HTTP 401. */
class AuthenticationException extends \Exception
{
    public function __construct(
        string $message = 'Your session has expired. Please sign in again.',
        private readonly string $errorCode = 'UNAUTHENTICATED',
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
