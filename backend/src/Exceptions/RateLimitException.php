<?php

declare(strict_types=1);

namespace SSIS\Exceptions;

/** Too many requests on a limited endpoint. Always maps to HTTP 429. */
class RateLimitException extends \Exception
{
    public function __construct(
        string $message = 'Too many requests. Please wait a moment and try again.',
        private readonly int $retryAfter = 60,
    ) {
        parent::__construct($message);
    }

    public function retryAfter(): int
    {
        return $this->retryAfter;
    }
}
