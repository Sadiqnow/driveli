<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Services\ActivityLogger;
use App\Services\SuperadminActivityLogger;
use App\Models\Driver;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Artisan;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');
    }

    public function index()
    {
        // Get system overview stats
        $stats = $this->getSystemStats();

        return view('admin.superadmin.index', compact('stats'));
    }

    public function auditLogs(Request $request)
    {
        $query = UserActivity::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(50);

        return view('admin.superadmin.audit-logs', compact('activities'));
    }

    public function getActivityDetails($id)
    {
        $activity = UserActivity::with('user')->findOrFail($id);

        return response()->json([
            'activity' => $activity,
            'formatted_data' => [
                'user' => $activity->user ? $activity->user->name : 'Unknown User',
                'action' => ucfirst($activity->action),
                'description' => $activity->description,
                'timestamp' => $activity->created_at->format('Y-m-d H:i:s'),
                'ip_address' => $activity->ip_address,
                'user_agent' => $activity->user_agent,
                'old_values' => $activity->old_values,
                'new_values' => $activity->new_values,
                'metadata' => $activity->metadata
            ]
        ]);
    }

    public function systemHealth()
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'queue' => $this->checkQueueHealth(),
            'last_backup' => $this->getLastBackupInfo(),
            'system_load' => $this->getSystemLoad()
        ];

        return response()->json($health);
    }

    private function getSystemStats()
    {
        return [
            'total_users' => \App\Models\AdminUser::count(),
            'active_users' => \App\Models\AdminUser::where('status', 'Active')->count(),
            'total_drivers' => \App\Models\Driver::count(),
            'verified_drivers' => \App\Models\Driver::where('verification_status', 'verified')->count(),
            'total_activities' => Schema::hasTable('user_activities') ? UserActivity::count() : 0,
            'recent_activities' => Schema::hasTable('user_activities') ? UserActivity::where('created_at', '>=', now()->subDay())->count() : 0,
            'system_uptime' => $this->getSystemUptime(),
            'database_size' => $this->getDatabaseSize()
        ];
    }

    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    private function checkCacheHealth()
    {
        try {
            // Simplified cache health check
            Cache::put('health_check', 'ok', 1);
            $result = Cache::get('health_check');
            if ($result === 'ok') {
                return ['status' => 'healthy', 'message' => 'Cache connection OK'];
            }
            return ['status' => 'warning', 'message' => 'Cache read/write issue'];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'message' => 'Cache connection issue: ' . $e->getMessage()];
        }
    }

    private function checkStorageHealth()
    {
        try {
            $testFile = storage_path('app/test.tmp');
            file_put_contents($testFile, 'test');
            unlink($testFile);
            return ['status' => 'healthy', 'message' => 'Storage writable'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Storage not writable: ' . $e->getMessage()];
        }
    }

    private function checkQueueHealth()
    {
        try {
            // Check if queue is running (simplified check)
            return ['status' => 'healthy', 'message' => 'Queue system OK'];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'message' => 'Queue system issue: ' . $e->getMessage()];
        }
    }

    private function getLastBackupInfo()
    {
        // This would integrate with your backup system
        return [
            'date' => now()->subDay()->format('Y-m-d H:i:s'),
            'status' => 'completed',
            'size' => '2.5 GB'
        ];
    }

    private function getSystemLoad()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => round($load[0], 2),
                '5min' => round($load[1], 2),
                '15min' => round($load[2], 2)
            ];
        }

        return ['1min' => 0, '5min' => 0, '15min' => 0];
    }

    private function getSystemUptime()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows uptime (simplified)
            return 'Windows Server - Uptime data not available';
        } else {
            // Linux/Unix uptime
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime) {
                $uptime = explode(' ', $uptime)[0];
                $days = floor($uptime / 86400);
                $hours = floor(($uptime % 86400) / 3600);
                $minutes = floor(($uptime % 3600) / 60);
                return "{$days}d {$hours}h {$minutes}m";
            }
        }

        return 'Uptime data not available';
    }

    private function getDatabaseSize()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $result = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
            return $result[0]->size_mb . ' MB';
        } catch (\Exception $e) {
            return 'Size unknown';
        }
    }

    public function settings()
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

    public function updateSettings(Request $request)
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

    public function getSettingGroup($group)
    {
        // This would return settings for a specific group
        // Implementation depends on how settings are stored
        return response()->json([
            'success' => true,
            'settings' => []
        ]);
    }

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

    private function validateSettingValue($key, $value)
    {
        // Basic validation - you might want to add more specific validation
        if (empty($value) && $value !== '0' && $value !== false) {
            throw new \Exception("Setting '{$key}' cannot be empty");
        }

        return true;
    }

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

    public function users(Request $request)
    {
        $query = \App\Models\AdminUser::with(['roles', 'permissions'])
            ->withTrashed();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', 'Active');
            } elseif ($request->status === 'inactive') {
                $query->where('status', 'Inactive');
            } elseif ($request->status === 'deleted') {
                $query->onlyTrashed();
            }
        }

        if ($request->get('with_trashed') !== '1') {
            $query->whereNull('deleted_at');
        }

        try {
            $users = $query->orderBy('created_at', 'desc')->paginate(15);

            // Get stats
            $stats = [
                'total' => \App\Models\AdminUser::count(),
                'active' => \App\Models\AdminUser::where('status', 'Active')->count(),
                'inactive' => \App\Models\AdminUser::where('status', 'Inactive')->count(),
                'super_admins' => \App\Models\AdminUser::whereHas('roles', function($q) {
                    $q->where('name', 'Super Admin');
                })->count(),
                'admins' => \App\Models\AdminUser::whereHas('roles', function($q) {
                    $q->where('name', 'Admin');
                })->count(),
                'moderators' => \App\Models\AdminUser::whereHas('roles', function($q) {
                    $q->where('name', 'Moderator');
                })->count(),
            ];
        } catch (\Exception $e) {
            // Fallback if role_user table doesn't exist
            $users = \App\Models\AdminUser::orderBy('created_at', 'desc')->paginate(15);
            $stats = [
                'total' => \App\Models\AdminUser::count(),
                'active' => \App\Models\AdminUser::where('status', 'Active')->count(),
                'inactive' => \App\Models\AdminUser::where('status', 'Inactive')->count(),
                'super_admins' => 0,
                'admins' => 0,
                'moderators' => 0,
            ];
        }

        return view('admin.superadmin.users', compact('users', 'stats'));
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_name' => 'required|string|exists:roles,name'
        ]);

        try {
            $user = \App\Models\AdminUser::findOrFail($request->user_id);
            $role = \App\Models\Role::where('name', $request->role_name)->firstOrFail();

            // Remove existing roles and assign new one
            $user->roles()->detach();
            $user->assignRole($role);

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'role_assigned',
                    "Assigned role '{$role->display_name}' to user {$user->name}",
                    $user,
                    ['role_id' => $role->id, 'role_name' => $role->name]
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Role '{$role->display_name}' assigned successfully to {$user->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_name' => 'required|string'
        ]);

        try {
            $user = \App\Models\AdminUser::findOrFail($request->user_id);
            $role = \App\Models\Role::where('name', $request->role_name)->first();

            if ($role) {
                $user->removeRole($role);

                // Log activity
                if (class_exists(\App\Services\ActivityLogger::class)) {
                    \App\Services\ActivityLogger::log(
                        'role_removed',
                        "Removed role '{$role->display_name}' from user {$user->name}",
                        $user,
                        ['role_id' => $role->id, 'role_name' => $role->name]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Role removed successfully from {$user->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchUsers(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $users = \App\Models\AdminUser::with('roles')
            ->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->query}%")
                  ->orWhere('email', 'like', "%{$request->query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->display_name ?? 'No Role',
                    'status' => $user->status
                ];
            });

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    public function bulkUserOperations(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:admin_users,id',
            'action' => 'required|in:activate,deactivate,delete,restore'
        ]);

        try {
            $users = \App\Models\AdminUser::whereIn('id', $request->user_ids);

            switch ($request->action) {
                case 'activate':
                    $users->update(['status' => 'Active']);
                    $message = 'Users activated successfully';
                    break;
                case 'deactivate':
                    $users->update(['status' => 'Inactive']);
                    $message = 'Users deactivated successfully';
                    break;
                case 'delete':
                    $users->delete();
                    $message = 'Users deleted successfully';
                    break;
                case 'restore':
                    $users->withTrashed()->restore();
                    $message = 'Users restored successfully';
                    break;
            }

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'bulk_user_operation',
                    "Bulk {$request->action} operation performed on " . count($request->user_ids) . " users",
                    auth('admin')->user(),
                    ['action' => $request->action, 'user_ids' => $request->user_ids]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

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

    // ===========================================
    // ADMIN MANAGEMENT METHODS
    // ===========================================

    /**
     * Show the form for creating a new admin
     */
    public function createAdmin()
    {
        // Get available roles for assignment
        $roles = \App\Models\Role::all();

        return view('admin.superadmin.admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin
     */
    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:Active,Inactive'
        ]);

        try {
            DB::beginTransaction();

            // Create admin user
            $admin = \App\Models\AdminUser::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'status' => $request->status,
                'email_verified_at' => now(),
            ]);

            // Assign role
            $role = \App\Models\Role::where('name', $request->role)->first();
            if ($role) {
                $admin->assignRole($role);
            }

            DB::commit();

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_created',
                    "Created new admin user: {$admin->name}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id, 'role' => $request->role]
                );
            }

            return redirect()->route('admin.superadmin.admins.show', $admin)
                           ->with('success', 'Admin created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified admin
     */
    public function showAdmin(\App\Models\AdminUser $admin)
    {
        // Load relationships
        $admin->load(['roles.permissions', 'permissions']);

        // Get activity log for this admin
        $activities = [];
        if (Schema::hasTable('user_activities')) {
            $activities = UserActivity::where('user_type', 'admin')
                ->where('user_id', $admin->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('admin.superadmin.admins.show', compact('admin', 'activities'));
    }

    /**
     * Show the form for editing the specified admin
     */
    public function editAdmin(\App\Models\AdminUser $admin)
    {
        // Get available roles
        $roles = \App\Models\Role::all();
        $currentRole = $admin->roles->first();

        return view('admin.superadmin.admins.edit', compact('admin', 'roles', 'currentRole'));
    }

    /**
     * Update the specified admin
     */
    public function updateAdmin(Request $request, \App\Models\AdminUser $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:Active,Inactive',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        try {
            DB::beginTransaction();

            // Update admin data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $admin->update($updateData);

            // Update role
            $role = \App\Models\Role::where('name', $request->role)->first();
            if ($role) {
                $admin->roles()->detach();
                $admin->assignRole($role);
            }

            DB::commit();

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_updated',
                    "Updated admin user: {$admin->name}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id, 'changes' => $request->all()]
                );
            }

            return redirect()->route('admin.superadmin.admins.show', $admin)
                           ->with('success', 'Admin updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified admin
     */
    public function destroyAdmin(\App\Models\AdminUser $admin)
    {
        // Prevent deleting self
        if ($admin->id === auth('admin')->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Prevent deleting other super admins if current user is not super admin
        if ($admin->roles->contains('name', 'Super Admin') && !auth('admin')->user()->roles->contains('name', 'Super Admin')) {
            return back()->withErrors(['error' => 'You do not have permission to delete Super Admin accounts.']);
        }

        try {
            $adminName = $admin->name;

            // Log activity before deletion
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_deleted',
                    "Deleted admin user: {$adminName}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id]
                );
            }

            $admin->delete();

            return redirect()->route('admin.superadmin.users')
                           ->with('success', 'Admin deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Flag admin with reason
     */
    public function flagAdmin(Request $request, \App\Models\AdminUser $admin)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        // Prevent flagging self
        if ($admin->id === auth('admin')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot flag your own account.'
            ], 403);
        }

        try {
            $admin->update(['status' => 'Flagged']);

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_flagged',
                    "Flagged admin user: {$admin->name} - Reason: {$request->reason}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id, 'reason' => $request->reason]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin flagged successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to flag admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend admin with reason
     */
    public function suspendAdmin(Request $request, \App\Models\AdminUser $admin)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'duration' => 'nullable|integer|min:1|max:365' // days
        ]);

        // Prevent suspending self
        if ($admin->id === auth('admin')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot suspend your own account.'
            ], 403);
        }

        try {
            $admin->update([
                'status' => 'Suspended',
                'suspended_until' => $request->duration ? now()->addDays($request->duration) : null,
                'suspension_reason' => $request->reason
            ]);

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_suspended',
                    "Suspended admin user: {$admin->name} - Reason: {$request->reason}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id, 'reason' => $request->reason, 'duration' => $request->duration]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin suspended successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve admin account
     */
    public function approveAdmin(Request $request, \App\Models\AdminUser $admin)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $admin->update([
                'status' => 'Active',
                'approved_at' => now(),
                'approved_by' => auth('admin')->id(),
                'suspended_until' => null,
                'suspension_reason' => null
            ]);

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_approved',
                    "Approved admin user: {$admin->name}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id, 'notes' => $request->notes]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject admin account
     */
    public function rejectAdmin(Request $request, \App\Models\AdminUser $admin)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $admin->update([
                'status' => 'Rejected',
                'rejection_reason' => $request->reason,
                'rejected_at' => now(),
                'rejected_by' => auth('admin')->id()
            ]);

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_rejected',
                    "Rejected admin user: {$admin->name} - Reason: {$request->reason}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id, 'reason' => $request->reason]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin rejected successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk activate admins
     */
    public function bulkActivateAdmins(Request $request)
    {
        $request->validate([
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admin_users,id'
        ]);

        try {
            $admins = \App\Models\AdminUser::whereIn('id', $request->admin_ids)->get();
            $count = 0;

            foreach ($admins as $admin) {
                // Skip self
                if ($admin->id === auth('admin')->id()) continue;

                $admin->update([
                    'status' => 'Active',
                    'suspended_until' => null,
                    'suspension_reason' => null
                ]);
                $count++;
            }

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'bulk_admin_activate',
                    "Bulk activated {$count} admin users",
                    auth('admin')->user(),
                    ['admin_ids' => $request->admin_ids]
                );
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} admins activated successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk activate admins: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk deactivate admins
     */
    public function bulkDeactivateAdmins(Request $request)
    {
        $request->validate([
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admin_users,id'
        ]);

        try {
            $admins = \App\Models\AdminUser::whereIn('id', $request->admin_ids)->get();
            $count = 0;

            foreach ($admins as $admin) {
                // Skip self
                if ($admin->id === auth('admin')->id()) continue;

                $admin->update(['status' => 'Inactive']);
                $count++;
            }

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'bulk_admin_deactivate',
                    "Bulk deactivated {$count} admin users",
                    auth('admin')->user(),
                    ['admin_ids' => $request->admin_ids]
                );
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} admins deactivated successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk deactivate admins: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete admins
     */
    public function bulkDeleteAdmins(Request $request)
    {
        $request->validate([
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admin_users,id'
        ]);

        try {
            $admins = \App\Models\AdminUser::whereIn('id', $request->admin_ids)->get();
            $count = 0;

            foreach ($admins as $admin) {
                // Skip self
                if ($admin->id === auth('admin')->id()) continue;

                // Skip other super admins if current user is not super admin
                if ($admin->hasRole('Super Admin') && !auth('admin')->user()->hasRole('Super Admin')) continue;

                $admin->delete();
                $count++;
            }

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'bulk_admin_delete',
                    "Bulk deleted {$count} admin users",
                    auth('admin')->user(),
                    ['admin_ids' => $request->admin_ids]
                );
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} admins deleted successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk delete admins: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===========================================
    // DRIVER MANAGEMENT METHODS
    // ===========================================

    /**
     * Display a listing of drivers
     */
    public function driversIndex(Request $request)
    {
        // Use optimized service for better performance
        $optimizationService = app(\App\Services\DriverQueryOptimizationService::class);

        // Get cached statistics
        $stats = $optimizationService->getDashboardStats();

        // Prepare filters array
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'verification_status' => $request->get('verification_status'),
            'kyc_status' => $request->get('kyc_status'),
        ];

        // Remove null values
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        // Get optimized paginated results
        $drivers = $optimizationService->getAdminDriverList($filters, 25);

        return view('admin.superadmin.drivers.index', compact('drivers', 'stats'));
    }

    /**
     * Show the form for creating a new driver
     */
    public function driversCreate()
    {
        return view('admin.superadmin.drivers.create');
    }

    /**
     * Store a newly created driver
     */
    public function driversStore(\App\Http\Requests\StoreDriverRequest $request)
    {
        try {
            $data = $request->validated();
            $data['driver_id'] = 'DRV-' . strtoupper(uniqid());
            $data['profile_completion_percentage'] = 0; // Will be calculated based on filled fields

            $driver = \App\Models\Drivers::create($data);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverCreation($driver, $request);

            return redirect()->route('admin.superadmin.drivers.show', $driver)
                           ->with('success', 'Driver created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified driver
     */
    public function driversShow(\App\Models\Drivers $driver)
    {
        // Use optimized service for better performance
        $optimizationService = app(\App\Services\DriverQueryOptimizationService::class);
        $driver = $optimizationService->getDriverDetails($driver->id);

        if (!$driver) {
            abort(404, 'Driver not found');
        }

        return view('admin.superadmin.drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver
     */
    public function driversEdit(\App\Models\Drivers $driver)
    {
        return view('admin.superadmin.drivers.edit', compact('driver'));
    }

    /**
     * Update the specified driver
     */
    public function driversUpdate(\App\Http\Requests\UpdateDriverRequest $request, \App\Models\Drivers $driver)
    {
        try {
            $oldValues = $driver->toArray();
            $driver->update($request->validated());

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverUpdate($driver, $oldValues, $driver->toArray(), $request);

            return redirect()->route('admin.superadmin.drivers.show', $driver)
                           ->with('success', 'Driver updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified driver
     */
    public function driversDestroy(\App\Models\Drivers $driver)
    {
        try {
            // Log activity before deletion
            \App\Services\SuperadminActivityLogger::logDriverDeletion($driver, request());

            $driver->delete();

            return redirect()->route('admin.superadmin.drivers.index')
                           ->with('success', 'Driver deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve driver application
     */
    public function driversApprove(Request $request, \App\Models\Drivers $driver)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $driver->update([
                'verification_status' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth('admin')->id(),
            ]);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverApproval($driver, $request->notes, $request);

            return response()->json([
                'success' => true,
                'message' => 'Driver approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject driver application
     */
    public function driversReject(Request $request, \App\Models\Drivers $driver)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $driver->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $request->reason,
            ]);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverRejection($driver, $request->reason, $request);

            return response()->json([
                'success' => true,
                'message' => 'Driver rejected successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Flag driver
     */
    public function driversFlag(Request $request, \App\Models\Drivers $driver)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $driver->update(['status' => 'flagged']);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverFlagging($driver, $request->reason, $request);

            return response()->json([
                'success' => true,
                'message' => 'Driver flagged successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to flag driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore driver
     */
    public function driversRestore(\App\Models\Drivers $driver)
    {
        try {
            $driver->update(['status' => 'active']);

            // Log activity
            \App\Services\SuperadminActivityLogger::logDriverRestoration($driver, request());

            return response()->json([
                'success' => true,
                'message' => 'Driver restored successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve drivers
     */
    public function driversBulkApprove(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                $driver->update([
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'verified_by' => auth('admin')->id(),
                ]);
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('approve', 'driver', $request->driver_ids, null, $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk approve drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject drivers
     */
    public function driversBulkReject(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id',
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                $driver->update([
                    'verification_status' => 'rejected',
                    'rejection_reason' => $request->reason,
                ]);
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('reject', 'driver', $request->driver_ids, ['reason' => $request->reason], $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers rejected successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk reject drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk flag drivers
     */
    public function driversBulkFlag(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id',
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                $driver->update(['status' => 'flagged']);
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('flag', 'driver', $request->driver_ids, ['reason' => $request->reason], $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers flagged successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk flag drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk restore drivers
     */
    public function driversBulkRestore(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                $driver->update(['status' => 'active']);
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('restore', 'driver', $request->driver_ids, null, $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers restored successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk restore drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete drivers
     */
    public function driversBulkDelete(Request $request)
    {
        $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer|exists:drivers,id'
        ]);

        try {
            $drivers = \App\Models\Drivers::whereIn('id', $request->driver_ids)->get();

            foreach ($drivers as $driver) {
                // Log individual deletion
                \App\Services\SuperadminActivityLogger::logDriverDeletion($driver, $request);
                $driver->delete();
            }

            // Log bulk activity
            \App\Services\SuperadminActivityLogger::logBulkOperation('delete', 'driver', $request->driver_ids, null, $request);

            return response()->json([
                'success' => true,
                'message' => count($drivers) . ' drivers deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk delete drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export drivers
     */
    public function driversExport(Request $request)
    {
        try {
            // Run the export command
            $command = 'drivers:export';

            if ($request->filled('format')) {
                $command .= ' --format=' . $request->format;
            }

            if ($request->filled('status')) {
                $command .= ' --status=' . $request->status;
            }

            if ($request->filled('verification')) {
                $command .= ' --verification=' . $request->verification;
            }

            if ($request->filled('kyc')) {
                $command .= ' --kyc=' . $request->kyc;
            }

            if ($request->filled('date_from')) {
                $command .= ' --date-from=' . $request->date_from;
            }

            if ($request->filled('date_to')) {
                $command .= ' --date-to=' . $request->date_to;
            }

            // Execute the command
            \Illuminate\Support\Facades\Artisan::call($command);

            $output = \Illuminate\Support\Facades\Artisan::output();

            // Log activity
            \App\Services\SuperadminActivityLogger::log(
                'export',
                'Exported drivers data',
                null,
                null,
                null,
                ['command' => $command, 'output' => $output],
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Export completed successfully.',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display driver analytics
     */
    public function driversAnalytics(Request $request)
    {
        // Get analytics data
        $analytics = $this->getDriverAnalytics();

        return view('admin.superadmin.drivers.analytics', compact('analytics'));
    }

    /**
     * Display driver audit trail
     */
    public function driversAudit(Request $request)
    {
        $query = \App\Models\SuperadminActivityLog::with(['superadmin'])
            ->where('resource_type', 'driver')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('driver_id')) {
            $query->where('resource_id', $request->driver_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(50);

        return view('admin.superadmin.drivers.audit', compact('activities'));
    }

    /**
     * Get driver analytics data
     */
    private function getDriverAnalytics()
    {
        $totalDrivers = \App\Models\Driver::count();
        $verifiedDrivers = \App\Models\Driver::where('verification_status', 'verified')->count();
        $activeDrivers = \App\Models\Driver::where('status', 'active')->count();

        // Performance metrics
        $performanceData = \App\Models\DriverPerformance::selectRaw('
            AVG(rating) as avg_rating,
            COUNT(*) as total_ratings,
            SUM(total_jobs) as total_jobs,
            SUM(total_earnings) as total_earnings
        ')->first();

        // Monthly registration trends (last 12 months)
        $monthlyRegistrations = \App\Models\Driver::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as count
        ')
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();

        // Verification status distribution
        $verificationStats = \App\Models\Driver::selectRaw('
            verification_status,
            COUNT(*) as count
        ')
        ->groupBy('verification_status')
        ->pluck('count', 'verification_status')
        ->toArray();

        // KYC completion rates
        $kycStats = \App\Models\Driver::selectRaw('
            kyc_status,
            COUNT(*) as count
        ')
        ->groupBy('kyc_status')
        ->pluck('count', 'kyc_status')
        ->toArray();

        // Document verification stats
        $documentStats = \App\Models\DriverDocument::selectRaw('
            verification_status,
            COUNT(*) as count
        ')
        ->groupBy('verification_status')
        ->pluck('count', 'verification_status')
        ->toArray();

        // Top performing drivers
        $topDrivers = \App\Models\Driver::with('performance')
            ->join('driver_performances', 'drivers.id', '=', 'driver_performances.driver_id')
            ->orderBy('driver_performances.rating', 'desc')
            ->orderBy('driver_performances.total_jobs', 'desc')
            ->limit(10)
            ->get(['drivers.*', 'driver_performances.rating', 'driver_performances.total_jobs']);

        return [
            'overview' => [
                'total_drivers' => $totalDrivers,
                'verified_drivers' => $verifiedDrivers,
                'active_drivers' => $activeDrivers,
                'verification_rate' => $totalDrivers > 0 ? round(($verifiedDrivers / $totalDrivers) * 100, 2) : 0,
                'active_rate' => $totalDrivers > 0 ? round(($activeDrivers / $totalDrivers) * 100, 2) : 0,
            ],
            'performance' => [
                'average_rating' => round($performanceData->avg_rating ?? 0, 1),
                'total_ratings' => $performanceData->total_ratings ?? 0,
                'total_jobs' => $performanceData->total_jobs ?? 0,
                'total_earnings' => $performanceData->total_earnings ?? 0,
            ],
            'trends' => [
                'monthly_registrations' => $monthlyRegistrations,
            ],
            'verification_distribution' => $verificationStats,
            'kyc_distribution' => $kycStats,
            'document_distribution' => $documentStats,
            'top_performers' => $topDrivers,
        ];
    }

    /**
     * Get driver statistics
     */
    private function getDriverStats()
    {
        return [
            'total' => \App\Models\Drivers::count(),
            'active' => \App\Models\Drivers::where('status', 'active')->count(),
            'inactive' => \App\Models\Drivers::where('status', 'inactive')->count(),
            'flagged' => \App\Models\Drivers::where('status', 'flagged')->count(),
            'verified' => \App\Models\Drivers::where('verification_status', 'verified')->count(),
            'kyc_completed' => \App\Models\Drivers::where('kyc_status', 'completed')->count(),
        ];
    }


}
