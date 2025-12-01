<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Asaas Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Asaas payment gateway integration
    |
    */

    'asaas' => [
        'api_url' => env('ASAAS_API_URL', 'https://www.asaas.com/api/v3'),
        'api_key' => env('ASAAS_API_KEY'),
        'webhook_token' => env('ASAAS_WEBHOOK_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mitt Fiscal Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for Mitt fiscal system integration
    |
    */

    'mitt' => [
        'api_url' => env('MITT_API_URL'),
        'api_key' => env('MITT_API_KEY'),
        'webhook_token' => env('MITT_WEBHOOK_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI integration for WhatsApp automation
    |
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WuzAPI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WuzAPI WhatsApp integration
    |
    */

    'wuzapi' => [
        'base_url' => env('WUZAPI_BASE_URL', 'http://wuzapi:8080'),
        'admin_token' => env('WUZAPI_ADMIN_TOKEN'),
        'user_token' => env('WUZAPI_USER_TOKEN'),
        'webhook_url' => env('WUZAPI_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Maps API integration
    |
    */

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

];
