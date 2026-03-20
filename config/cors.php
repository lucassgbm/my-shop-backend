<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'livewire/*', 'filament/*', '*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
    ],

    'allowed_origins_patterns' => [
        // Aceita qualquer subdomínio do Vercel
        '#^https://.*\.vercel\.app$#',
        // Aceita qualquer subdomínio do Render
        '#^https://.*\.onrender\.com$#',
        // Localhost em qualquer porta
        '#^http://localhost:\d+$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => true,
];
