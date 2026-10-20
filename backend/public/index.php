<?php

declare(strict_types=1);

/*
 * SSIS API entry point / request dispatcher.
 *
 * Flow: CORS preflight -> route matching (routes/api.php) -> middleware chain
 * -> controller action. All failures are converted to the standard JSON error
 * envelope with appropriate HTTP status codes; internals are logged only.
 *
 * Run locally:
 *   php -S 127.0.0.1:8000 -t backend/public backend/public/index.php
 */

use SSIS\Controllers\AuthController;
use SSIS\Exceptions\AuthenticationException;
use SSIS\Exceptions\AuthorizationException;
use SSIS\Exceptions\NotFoundException;
use SSIS\Exceptions\RateLimitException;
use SSIS\Exceptions\StorageException;
use SSIS\Exceptions\ValidationException;
use SSIS\Helpers\Config;
use SSIS\Helpers\Request;
use SSIS\Helpers\Response;
use SSIS\Middleware\AdminMiddleware;
use SSIS\Middleware\AuthMiddleware;
use SSIS\Middleware\CsrfMiddleware;
use SSIS\Middleware\RateLimitMiddleware;

// PHP built-in server: serve existing static files directly.
if (PHP_SAPI === 'cli-server') {
    $requestUriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    if (is_string($requestUriPath)) {
        $staticFile = __DIR__ . $requestUriPath;
        if (is_file($staticFile)) {
            return false;
        }
    }
}

require dirname(__DIR__) . '/bootstrap/app.php';

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

// ---------------------------------------------------------------------------
// CORS: credentialed access only for explicitly allowed origins (no wildcard).
// ---------------------------------------------------------------------------

$origin = $_SERVER['HTTP_ORIGIN'] ?? null;
if (is_string($origin) && $origin !== '') {
    $allowedOrigins = (array) Config::get('cors.origins', []);
    if (in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: ' . implode(', ', (array) Config::get('cors.methods', [])));
        header('Access-Control-Allow-Headers: ' . implode(', ', (array) Config::get('cors.headers', [])));
        header('Access-Control-Max-Age: ' . (int) Config::get('cors.max_age', 600));
    } else {
        Response::error('ORIGIN_FORBIDDEN', 'Requests from this origin are not allowed.', 403);
    }
}

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------------------------------------------------------------------------
// Route matching
// ---------------------------------------------------------------------------

$uriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$routePath = '/' . ltrim(rawurldecode(is_string($uriPath) ? $uriPath : ''), '/');

/** @var array<int, array{0: string, 1: string, 2: array{0: class-string, 1: string}, 3: array<int, string>}> $routes */
$routes = require SSIS_BASE_PATH . '/routes/api.php';

$matchedRoute = null;
$routeParams = [];

foreach ($routes as $route) {
    if ($route[0] !== $method) {
        continue;
    }

    $segments = preg_split('/(\{[A-Za-z_][A-Za-z0-9_]*\})/', $route[1], -1, PREG_SPLIT_DELIM_CAPTURE);
    $regex = '';
    foreach ($segments ?: [] as $segment) {
        if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $nameMatch) === 1) {
            $regex .= '(?P<' . $nameMatch[1] . '>[^/]+)';
        } else {
            $regex .= preg_quote($segment, '#');
        }
    }

    if (preg_match('#^' . $regex . '$#', $routePath, $matches) === 1) {
        $matchedRoute = $route;
        foreach ($matches as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $routeParams[$key] = $value;
            }
        }
        break;
    }
}

if ($matchedRoute === null) {
    Response::error('ROUTE_NOT_FOUND', 'The requested endpoint does not exist.', 404);
}

// ---------------------------------------------------------------------------
// Middleware + controller execution
// ---------------------------------------------------------------------------

try {
    $request = Request::fromGlobals();
    $request->setRouteParams($routeParams);

    foreach ($matchedRoute[3] as $middlewareSpec) {
        resolve_middleware($middlewareSpec)->handle($request);
    }

    [$controllerClass, $action] = $matchedRoute[2];
    $controller = new $controllerClass();
    $controller->{$action}($request);

    // Controllers always answer through Response::*; reaching this line is a
    // programming error and must still produce valid JSON.
    Response::error('EMPTY_RESPONSE', 'The server produced no response.', 500);
} catch (Throwable $exception) {
    handle_api_exception($exception);
}

/**
 * Builds a middleware instance from its route-table token.
 */
function resolve_middleware(string $spec): object
{
    $parts = explode(':', $spec, 2);
    $name = $parts[0];
    $argument = $parts[1] ?? null;

    return match ($name) {
        'auth' => new AuthMiddleware($argument !== null && $argument !== '' ? $argument : null),
        'admin' => new AdminMiddleware(),
        'csrf' => new CsrfMiddleware(),
        'rate' => new RateLimitMiddleware((string) $argument),
        default => throw new RuntimeException('Unknown middleware: ' . $spec),
    };
}

/**
 * Maps typed exceptions to the standard error envelope + HTTP status codes.
 * Unexpected errors are logged with full detail and returned as a generic 500.
 */
function handle_api_exception(Throwable $exception): never
{
    if ($exception instanceof ValidationException) {
        Response::error($exception->errorCode(), $exception->getMessage(), $exception->status(), $exception->fields());
    }

    if ($exception instanceof AuthenticationException) {
        Response::error($exception->errorCode(), $exception->getMessage(), 401);
    }

    if ($exception instanceof AuthorizationException) {
        Response::error($exception->errorCode(), $exception->getMessage(), 403);
    }

    if ($exception instanceof NotFoundException) {
        Response::error($exception->errorCode(), $exception->getMessage(), 404);
    }

    if ($exception instanceof RateLimitException) {
        header('Retry-After: ' . $exception->retryAfter());
        Response::error('RATE_LIMITED', $exception->getMessage(), 429);
    }

    if ($exception instanceof StorageException) {
        \SSIS\Helpers\Logger::exception($exception);
        Response::error($exception->errorCode(), 'Stored data is temporarily unavailable. Please try again later.', 500);
    }

    if ($exception instanceof RuntimeException && str_starts_with($exception->getMessage(), 'Unknown middleware')) {
        \SSIS\Helpers\Logger::exception($exception);
        Response::error('SERVER_ERROR', 'An unexpected server error occurred.', 500);
    }

    \SSIS\Helpers\Logger::exception($exception);
    Response::error('SERVER_ERROR', 'An unexpected server error occurred.', 500);
}
