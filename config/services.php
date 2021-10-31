<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'api' => [
        'appid' => env('CLIENT_ID'),
        'secret' => env('CLIENT_SECRET'),
        'callback' => 'https://myteachceshi.herokuapp.com/auth/callback'
    ],
    'student_api' => [
        'appid' => env('CLIENT_ID'),
        'secret' => env('CLIENT_SECRET'),
        'callback' => 'https://myteachceshi.herokuapp.com/auth/callback'
    ],
    'line_api' => [
        'client_id' => env('CLIENT_ID2'),
        'client_secret' => env('CLIENT_SECRET2'),
        'redirect' => 'https://myteachceshi.herokuapp.com/auth/callback',
    ],
    'Line' => [
        'client_id' => env('Line_KEY'),
        'client_secret' => env('Line_SECRET'),
        'redirect' => env('Line_REDIRECT_URI'),
    ],

];
