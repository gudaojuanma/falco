<?php

return [
    'name' => env('APP_NAME'),

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    'key' => env('APP_KEY'),

    'url' => env('APP_URL', '/'),

    'language' => env('APP_LANGUAGE', 'zh'),

    'languages' => ['zh', 'en'],

    'middleware' => [
        'auth' => \App\Http\Middleware\Authenticate::class,
    ],

    'providers' => [
        'global' => [
            \App\Providers\CacheServiceProvider::class,
            \App\Providers\DbServiceProvider::class,
            \App\Providers\RedisServiceProvider::class,
            \App\Providers\CryptServiceProvider::class,
            \App\Providers\LoggerServiceProvider::class,
            \App\Providers\BeanstalkServiceProvider::class,
        ],
        'http' => [
            // \App\Http\Providers\AgentServiceProvider::class,
            // \App\Http\Providers\AssetsServiceProvider::class,
            // \App\Http\Providers\VoltServiceProvider::class,
            // \App\Http\Providers\ViewServiceProvider::class,
            // \App\Http\Providers\SessionServiceProvider::class,
            // \App\Http\Providers\GuardServiceProvider::class,
            \App\Http\Providers\RouterServiceProvider::class,
            \App\Http\Providers\DispatcherServiceProvider::class,
            \App\Http\Providers\ResponseServiceProvider::class,
            \App\Http\Providers\LocaleServiceProvider::class,
            \App\Http\Providers\TranslatorServiceProvider::class,
        ],
        'console' => [
            \App\Console\Providers\DispatcherServiceProvider::class,
            \App\Console\Providers\OutputServiceProvider::class,
            \App\Console\Providers\LocaleServiceProvider::class,
        ]
    ]
];