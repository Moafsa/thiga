<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Production Environment Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration specific to the production environment.
    | These settings optimize performance and security for production use.
    |
    */

    'app' => [
        'debug' => false,
        'env' => 'production',
        'url' => env('APP_URL', 'https://tms.thiga.com.br'),
    ],

    'database' => [
        'connection' => 'pgsql',
        'host' => env('DB_HOST', 'pgsql'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'tms_saas'),
        'username' => env('DB_USERNAME', 'tms_user'),
        'password' => env('DB_PASSWORD', 'tms_password'),
    ],

    'cache' => [
        'driver' => 'redis',
        'host' => env('REDIS_HOST', 'redis'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],

    'session' => [
        'driver' => 'redis',
        'lifetime' => 120,
        'secure' => true,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    'queue' => [
        'connection' => 'redis',
        'retry_after' => 90,
        'block_for' => null,
    ],

    'mail' => [
        'driver' => 'smtp',
        'host' => env('MAIL_HOST', 'smtp.gmail.com'),
        'port' => env('MAIL_PORT', '587'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@thiga.com.br'),
            'name' => env('MAIL_FROM_NAME', 'TMS SaaS'),
        ],
    ],

    'logging' => [
        'channel' => 'stack',
        'level' => 'error',
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                'channels' => ['single', 'slack'],
            ],
            'single' => [
                'driver' => 'single',
                'path' => storage_path('logs/laravel.log'),
                'level' => 'error',
            ],
            'slack' => [
                'driver' => 'slack',
                'url' => env('LOG_SLACK_WEBHOOK_URL'),
                'username' => 'TMS SaaS',
                'emoji' => ':boom:',
                'level' => 'critical',
            ],
        ],
    ],

    'security' => [
        'force_https' => true,
        'hsts' => true,
        'csp' => true,
        'rate_limiting' => true,
    ],

    'performance' => [
        'opcache' => true,
        'gzip' => true,
        'minify' => true,
        'cdn' => env('CDN_URL'),
    ],
];