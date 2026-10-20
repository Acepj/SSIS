<?php

declare(strict_types=1);

/*
 * Application settings. Environment values are read through env() (see
 * bootstrap/app.php). Never place secrets that must not be exposed to
 * developers in this file - keep them in .env (which is git-ignored).
 */

return [
    'name' => 'SSIS',
    'env' => env('APP_ENV', 'production'),
    // Debug detail must never leak into API responses; it only affects logging.
    'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'url' => env('APP_URL', 'http://127.0.0.1:8000'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    'session' => [
        'name' => env('SESSION_NAME', 'ssis_session'),
        // Server-side session lifetime (also applied to "remember me" cookies).
        'lifetime' => (int) env('SESSION_LIFETIME', (string) (60 * 60 * 24 * 7)),
        'same_site' => 'Lax',
    ],

    // Basic request limits for sensitive endpoints (per IP + identifier).
    'rate_limits' => [
        'login' => [
            'max' => (int) env('LOGIN_RATE_MAX', '5'),
            'window' => (int) env('LOGIN_RATE_WINDOW', '900'),
        ],
    ],

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    // Current academic period shown across the application (overridable via .env).
    'academic' => [
        'year' => env('ACADEMIC_YEAR', '2026-2027'),
        'semester' => env('ACADEMIC_SEMESTER', 'First Semester'),
    ],
];
