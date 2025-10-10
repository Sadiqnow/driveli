<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver Verification Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the driver verification system including API endpoints,
    | credentials, and verification settings.
    |
    */

    'api_providers' => [
        
        'nimc' => [
            'enabled' => env('NIMC_ENABLED', true),
            'base_url' => env('NIMC_API_URL', 'https://api.nimc.gov.ng/v1'),
            'api_key' => env('NIMC_API_KEY'),
            'client_id' => env('NIMC_CLIENT_ID'),
            'client_secret' => env('NIMC_CLIENT_SECRET'),
            'timeout' => env('NIMC_TIMEOUT', 30),
            'retry_attempts' => env('NIMC_RETRY_ATTEMPTS', 3),
            'cache_ttl' => env('NIMC_CACHE_TTL', 3600), // 1 hour
            'endpoints' => [
                'verify_nin' => '/nin/verify',
                'get_nin_data' => '/nin/data',
                'validate_demographics' => '/nin/demographics/validate'
            ]
        ],

        'frsc' => [
            'enabled' => env('FRSC_ENABLED', true),
            'base_url' => env('FRSC_API_URL', 'https://api.frsc.gov.ng/v1'),
            'api_key' => env('FRSC_API_KEY'),
            'client_id' => env('FRSC_CLIENT_ID'),
            'client_secret' => env('FRSC_CLIENT_SECRET'),
            'timeout' => env('FRSC_TIMEOUT', 30),
            'retry_attempts' => env('FRSC_RETRY_ATTEMPTS', 3),
            'cache_ttl' => env('FRSC_CACHE_TTL', 3600),
            'endpoints' => [
                'verify_license' => '/license/verify',
                'get_license_data' => '/license/data',
                'check_violations' => '/license/violations'
            ]
        ],

        'cbn' => [
            'enabled' => env('CBN_ENABLED', true),
            'base_url' => env('CBN_API_URL', 'https://api.cbn.gov.ng/v1'),
            'api_key' => env('CBN_API_KEY'),
            'client_id' => env('CBN_CLIENT_ID'),
            'client_secret' => env('CBN_CLIENT_SECRET'),
            'timeout' => env('CBN_TIMEOUT', 30),
            'retry_attempts' => env('CBN_RETRY_ATTEMPTS', 3),
            'cache_ttl' => env('CBN_CACHE_TTL', 3600)
        ],

        'nibss' => [
            'enabled' => env('NIBSS_ENABLED', true),
            'base_url' => env('NIBSS_API_URL', 'https://api.nibss-plc.com.ng/v1'),
            'api_key' => env('NIBSS_API_KEY'),
            'client_id' => env('NIBSS_CLIENT_ID'),
            'client_secret' => env('NIBSS_CLIENT_SECRET'),
            'timeout' => env('NIBSS_TIMEOUT', 30),
            'retry_attempts' => env('NIBSS_RETRY_ATTEMPTS', 3),
            'cache_ttl' => env('NIBSS_CACHE_TTL', 3600),
            'endpoints' => [
                'verify_bvn' => '/bvn/verify',
                'get_bvn_data' => '/bvn/data',
                'check_watchlist' => '/bvn/watchlist'
            ]
        ],

    ],

    'ocr_providers' => [
        
        'preferred_provider' => env('OCR_PREFERRED_PROVIDER', 'google_vision'),
        
        'google_vision' => [
            'enabled' => env('GOOGLE_VISION_ENABLED', true),
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'key_file_path' => env('GOOGLE_CLOUD_KEY_FILE'),
            'timeout' => env('GOOGLE_VISION_TIMEOUT', 60),
            'features' => ['TEXT_DETECTION', 'DOCUMENT_TEXT_DETECTION'],
            'supported_formats' => ['jpg', 'jpeg', 'png', 'pdf', 'gif', 'bmp', 'webp']
        ],

        'aws_textract' => [
            'enabled' => env('AWS_TEXTRACT_ENABLED', false),
            'access_key_id' => env('AWS_ACCESS_KEY_ID'),
            'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'role_arn' => env('AWS_TEXTRACT_ROLE_ARN'),
            'timeout' => env('AWS_TEXTRACT_TIMEOUT', 60),
            'supported_formats' => ['jpg', 'jpeg', 'png', 'pdf']
        ],

        'tesseract' => [
            'enabled' => env('TESSERACT_ENABLED', true),
            'binary_path' => env('TESSERACT_PATH', '/usr/bin/tesseract'),
            'language' => env('TESSERACT_LANG', 'eng'),
            'timeout' => env('TESSERACT_TIMEOUT', 30),
            'supported_formats' => ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'tiff']
        ],

    ],

    'file_storage' => [
        'disk' => env('DOCUMENTS_STORAGE_DISK', 'documents'),
        'max_file_size' => env('DOCUMENTS_MAX_SIZE', 10240), // KB
        'allowed_types' => explode(',', env('DOCUMENTS_ALLOWED_TYPES', 'jpg,jpeg,png,pdf')),
        'document_types' => [
            'nin_card' => ['jpg', 'jpeg', 'png'],
            'drivers_license' => ['jpg', 'jpeg', 'png'],
            'passport' => ['jpg', 'jpeg', 'png'],
            'bvn_slip' => ['jpg', 'jpeg', 'png', 'pdf'],
            'referee_id' => ['jpg', 'jpeg', 'png']
        ],
        'paths' => [
            'nin_cards' => 'documents/nin_cards',
            'licenses' => 'documents/licenses',
            'passports' => 'documents/passports',
            'bvn_slips' => 'documents/bvn_slips',
            'referee_documents' => 'documents/referee_documents'
        ]
    ],

    'notification_services' => [
        
        'sms' => [
            'default_provider' => env('SMS_DEFAULT_PROVIDER', 'termii'),
            
            'twilio' => [
                'enabled' => env('TWILIO_ENABLED', false),
                'sid' => env('TWILIO_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from_number' => env('TWILIO_FROM_NUMBER')
            ],
            
            'termii' => [
                'enabled' => env('TERMII_ENABLED', true),
                'api_key' => env('TERMII_API_KEY'),
                'sender_id' => env('TERMII_SENDER_ID', 'DriveLink'),
                'base_url' => 'https://api.ng.termii.com/api'
            ]
        ],

        'email' => [
            'default_provider' => env('EMAIL_DEFAULT_PROVIDER', 'sendgrid'),
            
            'sendgrid' => [
                'enabled' => env('SENDGRID_ENABLED', true),
                'api_key' => env('SENDGRID_API_KEY'),
                'from_email' => env('SENDGRID_FROM_EMAIL', 'noreply@drivelink.com'),
                'from_name' => env('SENDGRID_FROM_NAME', 'DriveLink Support')
            ]
        ]

    ],

    'verification_settings' => [
        
        'scoring_weights' => [
            'nin_verification' => 25,      // 25% of total score
            'license_verification' => 20,  // 20% of total score
            'bvn_verification' => 20,      // 20% of total score
            'document_ocr' => 20,          // 20% of total score
            'referee_verification' => 15   // 15% of total score
        ],

        'pass_thresholds' => [
            'verified' => 85,              // Auto-verify if score >= 85%
            'manual_review' => 70,         // Manual review if score >= 70%
            'failed' => 50                 // Auto-fail if score < 50%
        ],

        'retry_settings' => [
            'max_attempts' => 3,
            'retry_delay_minutes' => 5,
            'exponential_backoff' => true
        ],

        'cache_settings' => [
            'api_results_ttl' => 3600,     // Cache API results for 1 hour
            'ocr_results_ttl' => 86400,    // Cache OCR results for 24 hours
            'verification_ttl' => 604800   // Cache verification results for 7 days
        ],

        'timeout_settings' => [
            'ocr_processing' => 120,       // 2 minutes for OCR processing
            'api_verification' => 30,      // 30 seconds per API call
            'complete_workflow' => 300     // 5 minutes for complete workflow
        ],

        'required_documents' => [
            'nin_card' => true,
            'drivers_license' => true,
            'passport' => false,           // Optional
            'bvn_slip' => false,          // Optional
            'referee_id' => true
        ],

        'minimum_referee_count' => 1,
        'maximum_referee_count' => 3,

        'fuzzy_matching' => [
            'name_similarity_threshold' => 0.8,
            'date_format_tolerance' => true,
            'phone_number_normalization' => true,
            'address_matching_threshold' => 0.7
        ]

    ]

];