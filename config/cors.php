<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://asy-syifaa.com',
        'https://www.asy-syifaa.com',
        'https://erp.asy-syifaa.com',
        'http://localhost:8080',
        'http://localhost:3000',
    ],

    'allowed_origins_patterns' => [
        '#^https://.*\.asy-syifaa\.com$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
