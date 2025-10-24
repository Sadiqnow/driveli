<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Drivelink Application Settings
    |--------------------------------------------------------------------------
    */
    
    'driver_id_prefix' => 'DR',
    'request_id_prefix' => 'REQ',
    'commission_id_prefix' => 'COM',
    
    'default_commission_rate' => env('DEFAULT_COMMISSION_RATE', 15.00),
    'platform_fee_percentage' => env('PLATFORM_FEE_PERCENTAGE', 2.50),
    
    'supported_regions' => [
        'Lagos',
        'Port Harcourt', 
        'Abuja',
        'Kano',
        'Kaduna',
        'Ibadan',
        'Other'
    ],
    
    'vehicle_types' => [
        'Tanker',
        'Tipper', 
        'Trailer',
        'Flatbed',
        'Container',
        'Truck'
    ],
    
    'experience_levels' => [
        '1-2 years',
        '3-5 years', 
        '6-10 years',
        '10+ years'
    ],
    
    'license_classes' => [
        'Class A',
        'Class B',
        'Class C', 
        'Commercial'
    ],
    
    'companies' => [
        'Dangote Group',
        'BUA Group',
        'Mangal Industries',
        'Flour Mills Nigeria',
        'Lafarge Africa',
        'Honeywell Group',
        'Other'
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes in seconds
        'otp_expiry_minutes' => 15,
        'password_reset_token_expiry' => 24, // hours
        'session_timeout_minutes' => 120,
        'allowed_file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
        'max_file_size_mb' => 10,
        'max_request_size' => 10 * 1024 * 1024, // 10MB in bytes
        'rate_limiting' => [
            'login_attempts' => 5, // per minute
            'api_requests' => 60, // per minute
            'file_uploads' => 10, // per hour
        ],
        'csrf_protection' => true,
        'secure_headers' => true,
        'session_security' => [
            'secure_cookies' => env('SESSION_SECURE_COOKIE', false),
            'same_site' => env('SESSION_SAME_SITE', 'lax'),
            'http_only' => true,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Integrations (Secure)
    |--------------------------------------------------------------------------
    */
    'apis' => [
        'nimc' => [
            'url' => env('NIMC_API_URL'),
            'key' => env('NIMC_API_KEY'),
            'client_id' => env('NIMC_CLIENT_ID'),
            'client_secret' => env('NIMC_CLIENT_SECRET'),
            'timeout' => 30,
            'retry_attempts' => 3,
        ],
        'frsc' => [
            'url' => env('FRSC_API_URL'),
            'key' => env('FRSC_API_KEY'),
            'client_id' => env('FRSC_CLIENT_ID'),
            'client_secret' => env('FRSC_CLIENT_SECRET'),
            'timeout' => 30,
            'retry_attempts' => 3,
        ],
        'ocr' => [
            'preferred_provider' => env('OCR_PREFERRED_PROVIDER', 'tesseract'),
            'google_vision' => [
                'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
                'key_file' => env('GOOGLE_CLOUD_KEY_FILE'),
            ],
            'aws_textract' => [
                'access_key' => env('AWS_ACCESS_KEY_ID'),
                'secret_key' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'role_arn' => env('AWS_TEXTRACT_ROLE_ARN'),
            ],
            'tesseract' => [
                'path' => env('TESSERACT_PATH', 'C:\Program Files\Tesseract-OCR\tesseract.exe'),
                'language' => env('TESSERACT_LANG', 'eng'),
            ],
        ],
        'sms' => [
            'provider' => env('SMS_PROVIDER', 'termii'), // termii, twilio
            'termii' => [
                'api_key' => env('TERMII_API_KEY'),
                'sender_id' => env('TERMII_SENDER_ID', 'DriveLink'),
            ],
            'twilio' => [
                'sid' => env('TWILIO_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from_number' => env('TWILIO_FROM_NUMBER'),
            ],
        ],
    ],
];