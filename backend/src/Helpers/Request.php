<?php

declare(strict_types=1);

namespace SSIS\Helpers;

use SSIS\Exceptions\ValidationException;

/**
 * Parsed HTTP request: method, path, headers, query and decoded JSON body.
 * Never trusts identifiers/roles sent by the browser for authorization -
 * those are resolved server-side from the session and stored records.
 */
final class Request
{
    /** @var array<string, string> */
    private array $routeParams = [];

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, string> $headers lower-cased header names
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $headers,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $uriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $path = '/' . ltrim(is_string($uriPath) ? $uriPath : '/', '/');

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_') && is_string($value)) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE']) && is_string($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        $raw = file_get_contents('php://input');
        $raw = $raw === false ? '' : $raw;

        $body = [];
        if ($raw !== '') {
            $contentType = $headers['content-type'] ?? '';
            if (str_contains($contentType, 'application/json')) {
                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    throw new ValidationException(
                        'The request body is not valid JSON.',
                        [],
                        'INVALID_JSON',
                        400,
                    );
                }
                $body = $decoded;
            } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
                parse_str($raw, $body);
            }
        } elseif (!empty($_POST)) {
            $body = $_POST;
        }

        return new self($method, $path, $_GET, $body, $headers);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /** Body first, then query string. */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    /** @param array<string, string> $params */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $name, ?string $default = null): ?string
    {
        return $this->routeParams[$name] ?? $default;
    }

    /** @return array<string, string> */
    public function routeParams(): array
    {
        return $this->routeParams;
    }
}
