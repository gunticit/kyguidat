<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
    ],

    'zalo' => [
        'client_id' => env('ZALO_APP_ID'),
        'client_secret' => env('ZALO_APP_SECRET'),
        'redirect' => env('ZALO_REDIRECT_URL'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URL'),
    ],

    'sepay' => [
        'api_key' => env('SEPAY_API_KEY', 'ANlxGJkKFDoB6uy5BEGjfTjsbUEJPOxu6MBvuEjklS4='),
    ],

    'webhook' => [
        'consignment_secret' => env('CONSIGNMENT_WEBHOOK_SECRET', ''),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_API_MODEL', 'gemini-2.5-flash'),
        'api_url' => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent'),
    ],

    'ai_api' => [
        'url'   => env('AI_API_URL', 'http://103.90.226.30:20128/v1/responses'),
        'model' => env('AI_API_MODEL', 'cx/gpt-5-codex-mini'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
        'model'   => env('OPENAI_API_MODEL', 'gpt-4o-mini'),
    ],
];

