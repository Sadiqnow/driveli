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

    'ocr' => [
        'api_key' => env('OCR_API_KEY', 'demo_key'),
        'endpoint' => env('OCR_ENDPOINT', 'https://api.ocr.space/parse/image'),
        'engine' => env('OCR_ENGINE', '2'),
        'language' => env('OCR_LANGUAGE', 'eng'),
    ],

    'nimc' => [
        'api_url' => env('NIMC_API_URL', 'https://api.nimc.gov.ng/verify-nin'),
        'api_key' => env('NIMC_API_KEY'),
        'timeout' => env('NIMC_TIMEOUT', 30),
    ],

    'frsc' => [
        'api_url' => env('FRSC_API_URL', 'https://api.frsc.gov.ng/verify-license'),
        'api_key' => env('FRSC_API_KEY'),
        'timeout' => env('FRSC_TIMEOUT', 30),
    ],

    'smile_id' => [
        'partner_id' => env('SMILE_ID_PARTNER_ID'),
        'api_key' => env('SMILE_ID_API_KEY'),
        'sid_server' => env('SMILE_ID_SID_SERVER', 0), // 0 for sandbox, 1 for production
        'api_url' => env('SMILE_ID_API_URL', 'https://api.smileidentity.com/v2/verify'),
        'callback_url' => env('SMILE_ID_CALLBACK_URL'),
        'timeout' => env('SMILE_ID_TIMEOUT', 60),
    ],

    'face_id' => [
        'api_url' => env('FACE_ID_API_URL', 'https://api.face-recognition.local/verify'),
        'api_key' => env('FACE_ID_API_KEY'),
        'timeout' => env('FACE_ID_TIMEOUT', 30),
    ],

];
