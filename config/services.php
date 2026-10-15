<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Market data for signals (docs/logic/signal-lifecycle.md). "simulated" is for
    | local development only; a real provider driver is added later.
    */
    /*
    | Payment gateway (docs/logic/subscription.md). "simulated" is for local
    | development only; Midtrans/Xendit drivers are added with credentials.
    */
    'payments' => [
        'driver' => env('PAYMENT_DRIVER', 'simulated'),
        'checkout_expiry_hours' => 24,
    ],

    'price_feed' => [
        'driver' => env('PRICE_FEED_DRIVER', 'simulated'),

        'simulated' => [
            'base_prices' => [
                'EURUSD' => 1.0850, 'GBPUSD' => 1.2700, 'USDJPY' => 150.00, 'AUDUSD' => 0.6600,
                'USDCHF' => 0.8800, 'USDCAD' => 1.3600, 'NZDUSD' => 0.6000,
                'XAUUSD' => 2650.0, 'XAGUSD' => 31.00,
            ],
        ],
    ],

];
