<?php

declare(strict_types=1);

namespace SSIS\Exceptions;

/** Requested resource does not exist. Always maps to HTTP 404. */
class NotFoundException extends \Exception
{
    public function __construct(
        string $message = 'The requested resource was not found.',
        private readonly string $errorCode = 'NOT_FOUND',
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
