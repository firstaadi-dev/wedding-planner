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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'workos' => [
        'api_key' => env('WORKOS_API_KEY'),
        'client_id' => env('WORKOS_CLIENT_ID'),
        'redirect_uri' => env('WORKOS_REDIRECT_URI'),
        'connect_timeout_seconds' => (int) env('WORKOS_CONNECT_TIMEOUT_SECONDS', 10),
        'timeout_seconds' => (int) env('WORKOS_TIMEOUT_SECONDS', 20),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY', 're_xxxxxxxxx'),
        'api_key' => env('RESEND_API_KEY', 're_xxxxxxxxx'),
    ],

];
