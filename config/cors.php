<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5273',
        'http://192.168.1.93:5273',
        'https://h.gpsmonitoreorada.site',
        'https://p.gpsmonitoreorada.site',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ⚠️ IMPORTANTE: si usas JWT con Authorization Header, déjalo en false.
    // Si en algún momento quieres usar cookies con Sanctum → ponlo en true.
    'supports_credentials' => false,

];
