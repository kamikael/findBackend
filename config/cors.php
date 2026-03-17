<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'fedapay/*', 'sanctum/csrf-cookie'],

    // ✅ Origines autorisées (DEV + fallback .env)
    'allowed_origins' => array_filter([
        env('FRONTEND_URL'), // si défini dans .env
        'http://localhost:5174',
        'http://127.0.0.1:5174',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ]),

    'allowed_methods' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ⚠️ nécessaire si tu utilises Sanctum / cookies
    'supports_credentials' => true,

];