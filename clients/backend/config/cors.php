<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter([
        env('FRONTEND_URL', 'http://localhost:3015'),
        env('APP_URL', 'http://localhost:8015'),
        env('APP_URL_SANDAT', 'https://khodat.com'),
        env('APP_URL_ADMIN', 'https://admin.khodat.com'),
        'https://backend.khodat.com',
        // Dev origins (only available when set in .env)
        env('CORS_DEV_ORIGIN_1'),
        env('CORS_DEV_ORIGIN_2'),
    ]),

    'allowed_origins_patterns' => array_filter([
        // Only allow localhost patterns in non-production
        env('APP_ENV') !== 'production' ? '#^http://localhost:\d+$#' : null,
        env('APP_ENV') !== 'production' ? '#^http://127\.0\.0\.1:\d+$#' : null,
    ]),

    'allowed_headers' => ['Content-Type', 'Accept', 'Authorization', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 86400, // 24 hours

    'supports_credentials' => true,

];
