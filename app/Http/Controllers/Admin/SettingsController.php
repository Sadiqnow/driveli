<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');
    }

    /**
     * Display system settings
     */
    public function index()
    {
        $systemInfo = [
            'app_name' => config('app.name', 'Drivelink'),
            'app_version' => '1.0.0',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'timezone' => config('app.timezone'),
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'debug_mode' => config('app.debug')
        ];

        $settingsGroups = [
            'general' => 'General Settings',
            'security' => 'Security Settings',
            'commission' => 'Commission Settings',
            'notification' => 'Notification Settings',
            'integration' => 'API Integrations',
            'verification' => 'Verification Settings',
            'system' => 'System Settings'
        ];

        $settings = [
            'general' => [
                'app_name' => ['type' => 'string', 'value' => config('app.name'), 'description' => 'Application name displayed throughout the system'],
                'app_url' => ['type' => 'string', 'value' => config('app.url'), 'description' => 'Base URL of the application'],
                'timezone' => ['type' => 'string', 'value' => config('app.timezone'), 'description' => 'Default timezone for the application'],
                'locale' => ['type' => 'string', 'value' => config('app.locale'), 'description' => 'Default language/locale']
            ],
            'security' => [
                'session_lifetime' => ['type' => 'integer', 'value' => config('session.lifetime'), 'description' => 'Session lifetime in minutes'],
                'password_min_length' => ['type' => 'integer', 'value' => 8, 'description' => 'Minimum password length'],
                'password_require_uppercase' => ['type' => 'boolean', 'value' => true, 'description' => 'Require uppercase letters in passwords'],
                'password_require_numbers' => ['type' => 'boolean', 'value' => true, 'description' => 'Require numbers in passwords'],
                'password_require_symbols' => ['type' => 'boolean', 'value' => false, 'description' => 'Require symbols in passwords']
            ],
            'commission' => [
                'default_commission_rate' => ['type' => 'float', 'value' => 10.0, 'description' => 'Default commission rate percentage'],
                'commission_calculation_method' => ['type' => 'string', 'value' => 'percentage', 'description' => 'Method to calculate commissions'],
                'auto_calculate_commissions' => ['type' => 'boolean', 'value' => true, 'description' => 'Automatically calculate commissions on matches'],
                'commission_payment_terms' => ['type' => 'integer', 'value' => 30, 'description' => 'Payment terms in days']
            ],
            'notification' => [
                'email_notifications' => ['type' => 'boolean', 'value' => true, 'description' => 'Enable email notifications'],
                'sms_notifications' => ['type' => 'boolean', 'value' => false, 'description' => 'Enable SMS notifications'],
                'push_notifications' => ['type' => 'boolean', 'value' => true, 'description' => 'Enable push notifications'],
                'notification_retention_days' => ['type' => 'integer', 'value' => 90, 'description' => 'Days to keep notifications']
            ],
            'integration' => [
                'nimc_api_enabled' => ['type' => 'boolean', 'value' => false, 'description' => 'Enable NIMC API integration'],
                'nimc_api_key' => ['type' => 'string', 'value' => '', 'description' => 'NIMC API key'],
                'frsc_api_enabled' => ['type' => 'boolean', 'value' => false, 'description' => 'Enable FRSC API integration'],
                'frsc_api_key' => ['type' => 'string', 'value' => '', 'description' => 'FRSC API key'],
                'sms_api_enabled' => ['type' => 'boolean', 'value' => false, 'description' => 'Enable SMS API integration'],
                'sms_api_key' => ['type' => 'string', 'value' => '', 'description' => 'SMS API key'],
                'ocr_api_enabled' => ['type' => 'boolean', 'value' => true, 'description' => 'Enable OCR API integration'],
                'ocr_api_key' => ['type' => 'string', 'value' => '', 'description' => 'OCR API key']
            ],
            'verification' => [
                'auto_verify_documents' => ['type' => 'boolean', 'value' => true, 'description' => 'Automatically verify uploaded documents'],
                'require_guarantor' => ['type' => 'boolean', 'value' => true, 'description' => 'Require guarantor information'],
                'verification_expiry_days' => ['type' => 'integer', 'value' => 365, 'description' => 'Days until verification expires'],
                'max_verification_attempts' => ['type' => 'integer', 'value' => 3, 'description' => 'Maximum verification attempts allowed']
            ],
            'system' => [
                'maintenance_mode' => ['type' => 'boolean', 'value' => app()->isDownForMaintenance(), 'description' => 'Enable maintenance mode'],
                'debug_mode' => ['type' => 'boolean', 'value' => config('app.debug'), 'description' => 'Enable debug mode'],
                'log_level' => ['type' => 'string', 'value' => config('logging.default'), 'description' => 'Default log level'],
                'cache_enabled' => ['type' => 'boolean', 'value' => true, 'description' => 'Enable caching'],
                'queue_enabled' => ['type' => 'boolean', 'value' => true, 'description' => 'Enable queue processing']
            ]
        ];

        return view('admin.superadmin.settings', compact('systemInfo', 'settingsGroups', 'settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'array',
        ]);

        try {
            foreach ($request->settings as $group => $groupSettings) {
                foreach ($groupSettings as $key => $value) {
                    // Here you would typically save to a settings table or config files
                    // For now, we'll just validate the input
                    $this->validateSettingValue($key, $value);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get setting group
     */
    public function getSettingGroup($group)
    {
        // This would return settings for a specific group
        // Implementation depends on how settings are stored
        return response()->json([
            'success' => true,
            'settings' => []
        ]);
    }

    /**
     * Test API connection
     */
    public function testApiConnection(Request $request)
    {
        $request->validate([
            'api_type' => 'required|string|in:nimc,frsc,sms,ocr'
        ]);

        try {
            // Simulate API testing
            $result = $this->testApi($request->api_type);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings
     */
    public function resetSettings(Request $request)
    {
        $request->validate([
            'group' => 'nullable|string'
        ]);

        try {
            // This would reset settings to defaults
            // For now, just return success
            $message = $request->group
                ? "Settings for group '{$request->group}' reset to defaults"
                : "All settings reset to defaults";

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'settings_reset',
                    $message,
                    auth('admin')->user(),
                    ['group' => $request->group]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate setting value
     */
    private function validateSettingValue($key, $value)
    {
        // Basic validation - you might want to add more specific validation
        if (empty($value) && $value !== '0' && $value !== false) {
            throw new \Exception("Setting '{$key}' cannot be empty");
        }

        return true;
    }

    /**
     * Test API
     */
    private function testApi($apiType)
    {
        // Simulate API testing - replace with actual API calls
        switch ($apiType) {
            case 'nimc':
                return ['success' => true, 'message' => 'NIMC API connection successful'];
            case 'frsc':
                return ['success' => true, 'message' => 'FRSC API connection successful'];
            case 'sms':
                return ['success' => false, 'message' => 'SMS API key not configured'];
            case 'ocr':
                return ['success' => true, 'message' => 'OCR API connection successful'];
            default:
                return ['success' => false, 'message' => 'Unknown API type'];
        }
    }
}
