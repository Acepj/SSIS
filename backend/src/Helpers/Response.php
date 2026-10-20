<?php

declare(strict_types=1);

namespace SSIS\Helpers;

/**
 * Standard JSON responses using the project-wide envelope:
 *   success: { success: true,  data, message? }
 *   error:   { success: false, error: { code, message, fields? } }
 *
 * Every method emits the response and terminates the request.
 */
final class Response
{
    public static function success(mixed $data = null, string $message = 'Request completed successfully.', int $status = 200): never
    {
        self::emit(['success' => true, 'data' => $data, 'message' => $message], $status);
    }

    /** @param array<string, string>|null $fields field-level validation messages */
    public static function error(string $code, string $message, int $status = 400, ?array $fields = null): never
    {
        $error = ['code' => $code, 'message' => $message];
        if ($fields !== null && $fields !== []) {
            $error['fields'] = $fields;
        }

        self::emit(['success' => false, 'error' => $error], $status);
    }

    /** @param array<string, mixed> $payload */
    private static function emit(array $payload, int $status): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: no-store');
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR,
        );
        echo $json === false ? '{"success":false,"error":{"code":"ENCODING_ERROR","message":"An unexpected server error occurred."}}' : $json;
        exit;
    }
}
