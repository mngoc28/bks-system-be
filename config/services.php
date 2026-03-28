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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token'  => env('TWILIO_AUTH_TOKEN'),
        'phone_number' => env('TWILIO_PHONE_NUMBER')
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT')
    ],

    'zoom' => [
        'client_id' => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
        'account_id' => env('ZOOM_ACCOUNT_ID'),
        'user_id' => env('ZOOM_USER_ID'),
        'timezone' => env('ZOOM_TIMEZONE', 'Asia/Ho_Chi_Minh'),
        'host_video' => env('ZOOM_HOST_VIDEO', true),
        'participant_video' => env('ZOOM_PARTICIPANT_VIDEO', true),
        'join_before_host' => env('ZOOM_JOIN_BEFORE_HOST', false),
        'mute_upon_entry' => env('ZOOM_MUTE_UPON_ENTRY', true),
        'waiting_room' => env('ZOOM_WAITING_ROOM', true),
        'meeting_authentication' => env('ZOOM_MEETING_AUTH', true),
        'oauth_url' => env('ZOOM_OAUTH_URL', 'https://zoom.us/oauth/token'),
        'api_url' => env('ZOOM_API_URL', 'https://api.zoom.us/v2'),
    ],
];
