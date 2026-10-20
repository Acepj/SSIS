<?php

declare(strict_types=1);

/*
 * CORS settings. Credentials are only ever granted to explicitly allowed
 * origins - never with a wildcard. In development the Vite dev server proxies
 * /api to this backend, so browsers normally send same-origin requests.
 */

$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173')),
)));

return [
    'origins' => $origins,
    'methods' => ['GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'OPTIONS'],
    'headers' => ['Content-Type', 'X-CSRF-Token', 'Accept'],
    'max_age' => 600,
];
