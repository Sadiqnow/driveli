<?php

namespace App\Services;

use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Log a general activity
     */
    public static function log($user, string $action, string $description, Model $resource = null, array $oldValues = null, array $newValues = null, array $metadata = null): UserActivity
    {
        return UserActivity::log($action, $description, $resource, $oldValues, $newValues, $metadata, $user);
    }

    /**
     * Log model creation
     */
    public static function created($user, Model $model, string $description = null): UserActivity
    {
        $description = $description ?? "Created " . class_basename($model) . " (ID: {$model->getKey()})";
        return UserActivity::logModelChange($user, $model, 'created', [], $model->toArray());
    }

    /**
     * Log model update
     */
    public static function updated($user, Model $model, array $oldValues = [], array $newValues = []): UserActivity
    {
        $description = "Updated " . class_basename($model) . " (ID: {$model->getKey()})";
        return UserActivity::logModelChange($user, $model, 'updated', $oldValues, $newValues);
    }

    /**
     * Log model deletion
     */
    public static function deleted($user, Model $model): UserActivity
    {
        $description = "Deleted " . class_basename($model) . " (ID: {$model->getKey()})";
        return UserActivity::logModelChange($user, $model, 'deleted', $model->toArray(), []);
    }

    /**
     * Log login activity
     */
    public static function login($user): UserActivity
    {
        return self::log($user, 'login', 'User logged in to the system');
    }

    /**
     * Log logout activity
     */
    public static function logout($user): UserActivity
    {
        return self::log($user, 'logout', 'User logged out of the system');
    }

    /**
     * Log driver verification
     */
    public static function driverVerified($user, Model $driver): UserActivity
    {
        return self::log($user, 'verified', "Verified driver: {$driver->first_name} {$driver->surname} (ID: {$driver->driver_id})", [
            'driver_id' => $driver->id,
            'driver_name' => $driver->full_name
        ]);
    }

    /**
     * Log driver rejection
     */
    public static function driverRejected($user, Model $driver, string $reason): UserActivity
    {
        return self::log($user, 'rejected', "Rejected driver: {$driver->first_name} {$driver->surname} (ID: {$driver->driver_id}) - Reason: {$reason}", [
            'driver_id' => $driver->id,
            'driver_name' => $driver->full_name,
            'reason' => $reason
        ]);
    }

    /**
     * Log permission changes
     */
    public static function permissionChanged($user, Model $role, array $addedPermissions = [], array $removedPermissions = []): UserActivity
    {
        $description = "Modified permissions for role: {$role->display_name}";
        $metadata = [];

        if (!empty($addedPermissions)) {
            $metadata['added_permissions'] = $addedPermissions;
        }

        if (!empty($removedPermissions)) {
            $metadata['removed_permissions'] = $removedPermissions;
        }

        return self::log($user, 'permission_changed', $description, $metadata);
    }

    /**
     * Log bulk operations
     */
    public static function bulkAction($user, string $action, string $modelType, int $count): UserActivity
    {
        return self::log($user, 'bulk_' . $action, "Performed bulk {$action} on {$count} {$modelType} records", [
            'count' => $count,
            'model_type' => $modelType
        ]);
    }

    /**
     * Log system settings change
     */
    public static function settingsChanged($user, string $setting, $oldValue, $newValue): UserActivity
    {
        return self::log($user, 'settings_changed', "Changed system setting: {$setting}", [
            'setting' => $setting,
            'old_value' => $oldValue,
            'new_value' => $newValue
        ]);
    }

    /**
     * Get recent activities for dashboard
     */
    public static function getDashboardActivities($days = 7, $limit = 15)
    {
        return UserActivity::forDashboard($days)->take($limit);
    }

    /**
     * Get activities for a specific user
     */
    public static function getUserActivities($user, $limit = 50)
    {
        return UserActivity::where('user_id', $user->id)
                          ->where('user_type', get_class($user))
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get activities by date range
     */
    public static function getActivitiesByDateRange($startDate, $endDate, $userType = null, $limit = 100)
    {
        $query = UserActivity::dateRange($startDate, $endDate);

        if ($userType) {
            $query->userType($userType);
        }

        return $query->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * Clean old activities (for maintenance)
     */
    public static function cleanOldActivities($daysToKeep = 365): int
    {
        return UserActivity::where('created_at', '<', now()->subDays($daysToKeep))->delete();
    }
}
