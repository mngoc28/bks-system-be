<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration allows you to control which APIs require authentication
    | and which ones are public. You can easily toggle authentication for
    | different API endpoints.
    |
    */

    'require_auth_for_all' => env('API_REQUIRE_AUTH_FOR_ALL', false),

    'public_endpoints'     => [
        // Authentication endpoints
        'register',
        'login',
        'send-mail-reset-password',

        // Public read-only endpoints
        'buildings/search',
        'buildings/{id}',
        'rooms/search',
        'rooms/{id}',
    ],

    'protected_endpoints'  => [
        // Authentication endpoints
        'logout',

        // Admin endpoints
        'admin/*',

        // Protected CRUD endpoints
        'buildings',
        'buildings/{id}',
        'rooms',
        'rooms/{id}',
    ],

    'middleware'           => [
        'jwt'     => 'jwt.auth',
        'sanctum' => 'auth:sanctum',
        'session' => 'auth',
    ],
];
