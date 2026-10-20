<?php

declare(strict_types=1);

namespace SSIS\Exceptions;

/**
 * Invalid user input. 422 by default; 400 for malformed requests.
 *
 * @phpstan-consistent-constructor
 */
class ValidationException extends \Exception
{
    /** @param array<string, string> $fields field name => message */
    public function __construct(
        string $message = 'Please review the submitted information.',
        private readonly array $fields = [],
        private readonly string $errorCode = 'VALIDATION_ERROR',
        private readonly int $status = 422,
    ) {
        parent::__construct($message);
    }

    /** @return array<string, string> */
    public function fields(): array
    {
        return $this->fields;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function status(): int
    {
        return $this->status;
    }
}
