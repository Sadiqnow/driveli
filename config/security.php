<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DriveLink Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | DriveLink application. These settings help protect against common
    | security vulnerabilities.
    |
    */

    // Password Policy
    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 12),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', true),
        'bcrypt_rounds' => env('BCRYPT_ROUNDS', 12),
    ],

    // Rate Limiting
    'rate_limiting' => [
        'enabled' => env('RATE_LIMIT_ENABLED', true),
        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 3),
        'lockout_duration' => env('LOCKOUT_DURATION_MINUTES', 30),
        'api_requests_per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
        'file_upload_per_hour' => env('FILE_UPLOAD_RATE_LIMIT', 10),
    ],

    // Session Security
    'session' => [
        'timeout_minutes' => env('SESSION_TIMEOUT_MINUTES', 60),
        'secure_cookies' => env('SESSION_SECURE_COOKIE', true),
        'same_site' => env('SESSION_SAME_SITE', 'strict'),
        'regenerate_on_login' => true,
        'invalidate_on_role_change' => true,
    ],

    // File Upload Security
    'file_uploads' => [
        'max_file_size_mb' => env('MAX_FILE_SIZE_MB', 2),
        'max_request_size_mb' => env('MAX_REQUEST_SIZE_MB', 10),
        'allowed_mime_types' => [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'application/pdf',
        ],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
        'scan_for_malware' => env('FILE_SCAN_MALWARE', true),
        'validate_file_signatures' => true,
        'strip_metadata' => true,
    ],

    // HTTPS and Transport Security
    'transport' => [
        'force_https' => env('FORCE_HTTPS', true),
        'hsts_enabled' => env('HSTS_ENABLED', true),
        'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
        'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'hsts_preload' => env('HSTS_PRELOAD', true),
    ],

    // Headers Security
    'headers' => [
        'csp_enabled' => env('CSP_ENABLED', true),
        'csp_report_only' => env('CSP_REPORT_ONLY', false),
        'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
    ],

    // Authentication Security
    'authentication' => [
        'otp_expiry_minutes' => env('OTP_EXPIRY_MINUTES', 10),
        'remember_me_duration' => env('REMEMBER_ME_DURATION', 2592000), // 30 days
        'require_email_verification' => env('REQUIRE_EMAIL_VERIFICATION', true),
        'require_phone_verification' => env('REQUIRE_PHONE_VERIFICATION', true),
        'multi_factor_auth' => env('MFA_ENABLED', false),
    ],

    // API Security
    'api' => [
        'require_api_key' => env('API_REQUIRE_KEY', true),
        'api_key_header' => env('API_KEY_HEADER', 'X-API-Key'),
        'cors_allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'https://yourdomain.com')),
        'cors_allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'cors_allowed_headers' => ['*'],
        'cors_exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining'],
    ],

    // Encryption and Hashing
    'encryption' => [
        'cipher' => env('APP_CIPHER', 'AES-256-CBC'),
        'key_rotation_enabled' => env('KEY_ROTATION_ENABLED', false),
        'hash_algorithm' => env('HASH_ALGORITHM', 'sha256'),
        'sensitive_data_encryption' => env('ENCRYPT_SENSITIVE_DATA', true),
    ],

    // Logging and Monitoring
    'monitoring' => [
        'log_failed_logins' => env('LOG_FAILED_LOGINS', true),
        'log_successful_logins' => env('LOG_SUCCESSFUL_LOGINS', true),
        'log_admin_actions' => env('LOG_ADMIN_ACTIONS', true),
        'log_file_uploads' => env('LOG_FILE_UPLOADS', true),
        'log_api_requests' => env('LOG_API_REQUESTS', true),
        'security_event_webhook' => env('SECURITY_EVENT_WEBHOOK', null),
    ],

    // Content Security Policy
    'csp' => [
        'default_src' => ["'self'"],
        'script_src' => ["'self'", "'unsafe-inline'", 'https://cdnjs.cloudflare.com'],
        'style_src' => ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com'],
        'font_src' => ["'self'", 'https://fonts.gstatic.com'],
        'img_src' => ["'self'", 'data:', 'https:'],
        'connect_src' => ["'self'"],
        'frame_ancestors' => ["'none'"],
        'base_uri' => ["'self'"],
        'form_action' => ["'self'"],
    ],

    // Data Protection
    'data_protection' => [
        'mask_sensitive_data' => env('MASK_SENSITIVE_DATA', true),
        'data_retention_days' => env('DATA_RETENTION_DAYS', 2555), // ~7 years
        'automatic_data_purging' => env('AUTO_DATA_PURGING', true),
        'anonymize_deleted_users' => env('ANONYMIZE_DELETED_USERS', true),
        'encrypt_pii_fields' => env('ENCRYPT_PII_FIELDS', true),
    ],

    // Emergency Security
    'emergency' => [
        'maintenance_mode_key' => env('EMERGENCY_MAINTENANCE_KEY', null),
        'security_lockdown_key' => env('SECURITY_LOCKDOWN_KEY', null),
        'emergency_contact_email' => env('EMERGENCY_CONTACT_EMAIL', null),
        'incident_response_enabled' => env('INCIDENT_RESPONSE_ENABLED', true),
    ],

];