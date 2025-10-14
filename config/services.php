'device_verification' => [
        'vpn_ranges' => env('DEVICE_VERIFICATION_VPN_RANGES', []),
        'suspicious_threshold' => env('DEVICE_VERIFICATION_SUSPICIOUS_THRESHOLD', 3),
        'max_distance_km' => env('DEVICE_VERIFICATION_MAX_DISTANCE_KM', 50),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
        'sms_enabled' => env('TWILIO_SMS_ENABLED', false),
    ],

];
