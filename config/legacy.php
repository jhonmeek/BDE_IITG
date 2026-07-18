<?php

return [
    'root' => env('LEGACY_BDE_ROOT', base_path('../BDE-web')),

    'mysql' => [
        'host' => env('LEGACY_BDE_MYSQL_HOST', '127.0.0.1'),
        'port' => env('LEGACY_BDE_MYSQL_PORT', 3307),
        'database' => env('LEGACY_BDE_MYSQL_DATABASE'),
        'username' => env('LEGACY_BDE_MYSQL_USERNAME', 'root'),
        'password' => env('LEGACY_BDE_MYSQL_PASSWORD'),
    ],
];
