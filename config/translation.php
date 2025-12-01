<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Translation Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default translation driver that will be used
    | by the framework. You may set this to any of the drivers defined
    | in the "drivers" array below.
    |
    | Supported: "file", "database"
    |
    */

    'driver' => env('TRANSLATION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Translation Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the translation drivers for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses the file driver. You are free to change this as
    | you wish.
    |
    */

    'drivers' => [

        'file' => [
            'driver' => 'file',
            'path' => resource_path('lang'),
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'translations',
            'connection' => null,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Cache
    |--------------------------------------------------------------------------
    |
    | Here you may configure the translation cache settings for your
    | application. This will determine how long translations are cached
    | for performance.
    |
    */

    'cache' => [
        'enabled' => env('TRANSLATION_CACHE_ENABLED', true),
        'ttl' => env('TRANSLATION_CACHE_TTL', 3600),
    ],

];