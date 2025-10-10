<?php

use App\Services\SettingsService;

if (!function_exists('settings')) {
    /**
     * Get settings service instance or specific setting value
     *
     * @param string|null $key
     * @param mixed $default
     * @param string $group
     * @return SettingsService|mixed
     */
    function settings($key = null, $default = null, $group = 'general')
    {
        $service = app(SettingsService::class);
        
        if ($key === null) {
            return $service;
        }
        
        return $service->get($key, $default, $group);
    }
}

if (!function_exists('app_setting')) {
    /**
     * Get application setting
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function app_setting($key, $default = null)
    {
        return settings($key, $default, 'general');
    }
}

if (!function_exists('security_setting')) {
    /**
     * Get security setting
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function security_setting($key, $default = null)
    {
        return settings($key, $default, 'security');
    }
}

if (!function_exists('commission_setting')) {
    /**
     * Get commission setting
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function commission_setting($key, $default = null)
    {
        return settings($key, $default, 'commission');
    }
}

if (!function_exists('is_feature_enabled')) {
    /**
     * Check if a feature is enabled
     *
     * @param string $feature
     * @return bool
     */
    function is_feature_enabled($feature)
    {
        return app(SettingsService::class)->isFeatureEnabled($feature);
    }
}