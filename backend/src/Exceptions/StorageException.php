<?php

declare(strict_types=1);

namespace SSIS\Exceptions;

/**
 * JSON storage failure (corrupted data, unreadable/unwritable files).
 * Maps to HTTP 500 with a generic message - internals are logged only.
 */
class StorageException extends \Exception
{
    public function __construct(
        string $message = 'Stored data is temporarily unavailable.',
        private readonly string $errorCode = 'STORAGE_ERROR',
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
