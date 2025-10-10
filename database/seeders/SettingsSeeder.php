<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $defaultSettings = [
            'general' => [
                'app_name' => ['value' => config('app.name', 'DriveLink'), 'type' => 'string', 'description' => 'Application name'],
                'app_tagline' => ['value' => 'Professional Driver Matching Platform', 'type' => 'string', 'description' => 'Application tagline'],
                'company_email' => ['value' => config('mail.from.address', 'admin@drivelink.com'), 'type' => 'string', 'description' => 'Company contact email'],
                'company_phone' => ['value' => '+234 800 000 0000', 'type' => 'string', 'description' => 'Company contact phone'],
                'default_timezone' => ['value' => config('app.timezone', 'Africa/Lagos'), 'type' => 'string', 'description' => 'Default timezone'],
                'items_per_page' => ['value' => 20, 'type' => 'integer', 'description' => 'Default items per page'],
            ],
            'security' => [
                'password_min_length' => ['value' => 8, 'type' => 'integer', 'description' => 'Minimum password length'],
                'max_login_attempts' => ['value' => 5, 'type' => 'integer', 'description' => 'Maximum login attempts before lockout'],
                'lockout_duration' => ['value' => 15, 'type' => 'integer', 'description' => 'Account lockout duration in minutes'],
                'session_timeout' => ['value' => 120, 'type' => 'integer', 'description' => 'Session timeout in minutes'],
                'require_2fa' => ['value' => false, 'type' => 'boolean', 'description' => 'Require two-factor authentication'],
                'allowed_file_extensions' => ['value' => ['jpg', 'jpeg', 'png', 'pdf'], 'type' => 'array', 'description' => 'Allowed file upload extensions'],
                'max_file_size_mb' => ['value' => 10, 'type' => 'integer', 'description' => 'Maximum file upload size in MB'],
            ],
            'commission' => [
                'default_commission_rate' => ['value' => 15.0, 'type' => 'float', 'description' => 'Default commission rate percentage'],
                'platform_fee_percentage' => ['value' => 2.5, 'type' => 'float', 'description' => 'Platform fee percentage'],
                'minimum_commission' => ['value' => 1000, 'type' => 'float', 'description' => 'Minimum commission amount in Naira'],
                'commission_payment_terms' => ['value' => 30, 'type' => 'integer', 'description' => 'Commission payment terms in days'],
            ],
            'notification' => [
                'email_notifications_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable email notifications'],
                'sms_notifications_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable SMS notifications'],
                'admin_notification_email' => ['value' => config('mail.from.address'), 'type' => 'string', 'description' => 'Admin notification email'],
                'notification_queue' => ['value' => true, 'type' => 'boolean', 'description' => 'Queue notifications for better performance'],
            ],
            'integration' => [
                'nimc_api_enabled' => ['value' => false, 'type' => 'boolean', 'description' => 'Enable NIMC API integration'],
                'frsc_api_enabled' => ['value' => false, 'type' => 'boolean', 'description' => 'Enable FRSC API integration'],
                'ocr_verification_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable OCR verification'],
                'auto_verification_enabled' => ['value' => false, 'type' => 'boolean', 'description' => 'Enable automatic verification'],
                'api_rate_limit' => ['value' => 100, 'type' => 'integer', 'description' => 'API requests per hour limit'],
            ],
            'verification' => [
                'manual_verification_required' => ['value' => true, 'type' => 'boolean', 'description' => 'Require manual verification'],
                'document_retention_days' => ['value' => 2555, 'type' => 'integer', 'description' => 'Document retention period in days (7 years)'],
                'auto_approve_threshold' => ['value' => 90, 'type' => 'integer', 'description' => 'Auto-approval confidence threshold percentage'],
                'verification_timeout_hours' => ['value' => 48, 'type' => 'integer', 'description' => 'Verification timeout in hours'],
            ],
            'system' => [
                'maintenance_mode' => ['value' => false, 'type' => 'boolean', 'description' => 'Enable maintenance mode'],
                'debug_mode' => ['value' => config('app.debug', false), 'type' => 'boolean', 'description' => 'Enable debug mode'],
                'log_level' => ['value' => 'info', 'type' => 'string', 'description' => 'System log level'],
                'cache_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable caching'],
                'backup_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable automated backups'],
                'backup_retention_days' => ['value' => 30, 'type' => 'integer', 'description' => 'Backup retention period in days'],
            ]
        ];

        foreach ($defaultSettings as $group => $settings) {
            foreach ($settings as $key => $config) {
                Setting::updateOrCreate(
                    [
                        'key' => $key,
                        'group' => $group
                    ],
                    [
                        'value' => $config['value'],
                        'type' => $config['type'],
                        'description' => $config['description'],
                        'is_public' => false,
                        'created_by' => null,
                        'updated_by' => null
                    ]
                );
            }
        }

        $this->command->info('Default settings seeded successfully!');
    }
}