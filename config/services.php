<?php

return [
    'mercadopago' => [
        'public_key'     => env('MP_PUBLIC_KEY'),
        'access_token'   => env('MP_ACCESS_TOKEN'),
        'webhook_secret' => env('MP_WEBHOOK_SECRET'),
    ],

    'melhorenvio' => [
        'client_id'     => env('ME_CLIENT_ID'),
        'client_secret' => env('ME_CLIENT_SECRET'),
        'redirect_uri'  => env('ME_REDIRECT_URI'),
        'postal_code'   => env('ME_POSTAL_CODE', '01310100'),
        'sandbox'       => env('ME_SANDBOX', true),
        'base_url'      => env('ME_SANDBOX', true)
                            ? 'https://sandbox.melhorenvio.com.br'
                            : 'https://melhorenvio.com.br',
    ],
];
