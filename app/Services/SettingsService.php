<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected $cacheKey = 'app_settings';
    protected $cacheDuration = 3600; // 1 hour

    /**
     * Get a setting value
     */
    public function get($key, $default = null, $group = 'general')
    {
        $settings = $this->getAllSettings();
        
        if (isset($settings[$group][$key])) {
            return $settings[$group][$key];
        }
        
        return $default;
    }

    /**
     * Set a setting value
     */
    public function set($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        $setting = Setting::set($key, $value, $type, $group, $description);
        $this->clearCache();
        
        return $setting;
    }

    /**
     * Get all settings grouped by category
     */
    public function getAllSettings()
    {
        return Cache::remember($this->cacheKey, $this->cacheDuration, function () {
            return Setting::getAllGroups();
        });
    }

    /**
     * Get settings for a specific group
     */
    public function getGroup($group)
    {
        $settings = $this->getAllSettings();
        return $settings[$group] ?? [];
    }

    /**
     * Check if a setting exists
     */
    public function has($key, $group = 'general')
    {
        $settings = $this->getAllSettings();
        return isset($settings[$group][$key]);
    }

    /**
     * Get multiple settings at once
     */
    public function getMultiple(array $keys, $group = 'general')
    {
        $settings = $this->getGroup($group);
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = $settings[$key] ?? null;
        }
        
        return $result;
    }

    /**
     * Update multiple settings at once
     */
    public function setMultiple(array $settings, $group = 'general')
    {
        foreach ($settings as $key => $value) {
            $existingSetting = Setting::where('key', $key)->where('group', $group)->first();
            $type = $existingSetting ? $existingSetting->type : 'string';
            
            Setting::set($key, $value, $type, $group);
        }
        
        $this->clearCache();
    }

    /**
     * Clear the settings cache
     */
    public function clearCache()
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Refresh the cache
     */
    public function refreshCache()
    {
        $this->clearCache();
        return $this->getAllSettings();
    }

    /**
     * Get application configuration settings
     */
    public function getAppSettings()
    {
        return [
            'app_name' => $this->get('app_name', config('app.name')),
            'app_tagline' => $this->get('app_tagline', 'Professional Driver Matching Platform'),
            'company_email' => $this->get('company_email', config('mail.from.address')),
            'company_phone' => $this->get('company_phone', '+234 800 000 0000'),
            'default_timezone' => $this->get('default_timezone', config('app.timezone')),
            'items_per_page' => (int) $this->get('items_per_page', 20),
        ];
    }

    /**
     * Get security settings
     */
    public function getSecuritySettings()
    {
        return [
            'password_min_length' => (int) $this->get('password_min_length', 8, 'security'),
            'max_login_attempts' => (int) $this->get('max_login_attempts', 5, 'security'),
            'lockout_duration' => (int) $this->get('lockout_duration', 15, 'security'),
            'session_timeout' => (int) $this->get('session_timeout', 120, 'security'),
            'require_2fa' => (bool) $this->get('require_2fa', false, 'security'),
            'allowed_file_extensions' => $this->get('allowed_file_extensions', ['jpg', 'jpeg', 'png', 'pdf'], 'security'),
            'max_file_size_mb' => (int) $this->get('max_file_size_mb', 10, 'security'),
        ];
    }

    /**
     * Get commission settings
     */
    public function getCommissionSettings()
    {
        return [
            'default_commission_rate' => (float) $this->get('default_commission_rate', 15.0, 'commission'),
            'platform_fee_percentage' => (float) $this->get('platform_fee_percentage', 2.5, 'commission'),
            'minimum_commission' => (float) $this->get('minimum_commission', 1000, 'commission'),
            'commission_payment_terms' => (int) $this->get('commission_payment_terms', 30, 'commission'),
        ];
    }

    /**
     * Get notification settings
     */
    public function getNotificationSettings()
    {
        return [
            'email_notifications_enabled' => (bool) $this->get('email_notifications_enabled', true, 'notification'),
            'sms_notifications_enabled' => (bool) $this->get('sms_notifications_enabled', true, 'notification'),
            'admin_notification_email' => $this->get('admin_notification_email', config('mail.from.address'), 'notification'),
            'notification_queue' => (bool) $this->get('notification_queue', true, 'notification'),
        ];
    }

    /**
     * Get integration settings
     */
    public function getIntegrationSettings()
    {
        return [
            'nimc_api_enabled' => (bool) $this->get('nimc_api_enabled', false, 'integration'),
            'frsc_api_enabled' => (bool) $this->get('frsc_api_enabled', false, 'integration'),
            'ocr_verification_enabled' => (bool) $this->get('ocr_verification_enabled', true, 'integration'),
            'auto_verification_enabled' => (bool) $this->get('auto_verification_enabled', false, 'integration'),
            'api_rate_limit' => (int) $this->get('api_rate_limit', 100, 'integration'),
        ];
    }

    /**
     * Get verification settings
     */
    public function getVerificationSettings()
    {
        return [
            'manual_verification_required' => (bool) $this->get('manual_verification_required', true, 'verification'),
            'document_retention_days' => (int) $this->get('document_retention_days', 2555, 'verification'),
            'auto_approve_threshold' => (int) $this->get('auto_approve_threshold', 90, 'verification'),
            'verification_timeout_hours' => (int) $this->get('verification_timeout_hours', 48, 'verification'),
        ];
    }

    /**
     * Get system settings
     */
    public function getSystemSettings()
    {
        return [
            'maintenance_mode' => (bool) $this->get('maintenance_mode', false, 'system'),
            'debug_mode' => (bool) $this->get('debug_mode', config('app.debug'), 'system'),
            'log_level' => $this->get('log_level', 'info', 'system'),
            'cache_enabled' => (bool) $this->get('cache_enabled', true, 'system'),
            'backup_enabled' => (bool) $this->get('backup_enabled', true, 'system'),
            'backup_retention_days' => (int) $this->get('backup_retention_days', 30, 'system'),
        ];
    }

    /**
     * Check if feature is enabled
     */
    public function isFeatureEnabled($feature)
    {
        $featureMap = [
            'ocr_verification' => $this->get('ocr_verification_enabled', true, 'integration'),
            'auto_verification' => $this->get('auto_verification_enabled', false, 'integration'),
            'email_notifications' => $this->get('email_notifications_enabled', true, 'notification'),
            'sms_notifications' => $this->get('sms_notifications_enabled', true, 'notification'),
            'nimc_api' => $this->get('nimc_api_enabled', false, 'integration'),
            'frsc_api' => $this->get('frsc_api_enabled', false, 'integration'),
            'manual_verification' => $this->get('manual_verification_required', true, 'verification'),
            '2fa' => $this->get('require_2fa', false, 'security'),
            'maintenance' => $this->get('maintenance_mode', false, 'system'),
            'debug' => $this->get('debug_mode', config('app.debug'), 'system'),
            'cache' => $this->get('cache_enabled', true, 'system'),
            'backup' => $this->get('backup_enabled', true, 'system'),
        ];

        return $featureMap[$feature] ?? false;
    }

    /**
     * Export settings for backup
     */
    public function exportSettings()
    {
        $settings = Setting::with(['creator', 'updater'])->get();
        
        return $settings->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
                'group' => $setting->group,
                'is_public' => $setting->is_public,
                'validation_rules' => $setting->validation_rules,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
            ];
        })->toArray();
    }

    /**
     * Import settings from backup
     */
    public function importSettings(array $settings)
    {
        foreach ($settings as $settingData) {
            Setting::updateOrCreate(
                [
                    'key' => $settingData['key'],
                    'group' => $settingData['group']
                ],
                [
                    'value' => $settingData['value'],
                    'type' => $settingData['type'],
                    'description' => $settingData['description'],
                    'is_public' => $settingData['is_public'] ?? false,
                    'validation_rules' => $settingData['validation_rules'] ?? null,
                    'updated_by' => auth('admin')->id()
                ]
            );
        }
        
        $this->clearCache();
    }
}