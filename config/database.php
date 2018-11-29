<?php

return [
    'mysql' => [
        'host' => env('DATABASE_HOST', 'localhost'),
        'port' => env('DATABASE_PORT', 3306),
        'username' => env('DATABASE_USERNAME', 'www'),
        'password' => env('DATABASE_PASSWORD', '123456'),
        'dbname' => env('DATABASE_SCHEMA', 'root'),
        'charset' => env('DATABASE_CHARSET', 'utf8mb')
    ],

    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379)
    ]
];