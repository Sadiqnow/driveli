<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class RoleSyncService
{
    /**
     * Cache key prefix for user permissions
     */
    const CACHE_KEY_PREFIX = 'user_permissions_';

    /**
     * Default cache TTL in minutes
     */
    const CACHE_TTL_MINUTES = 30;

    /**
     * Get cache key for user permissions
     */
    public static function getCacheKey(int $userId): string
    {
        return self::CACHE_KEY_PREFIX . $userId;
    }

    /**
     * Refresh user permission cache
     */
    public function refreshUserPermissions(AdminUser $user): array
    {
        try {
            $permissions = $this->fetchUserPermissions($user);
            $cacheKey = self::getCacheKey($user->id);

            Cache::put($cacheKey, $permissions, now()->addMinutes(self::CACHE_TTL_MINUTES));

            Log::info('User permission cache refreshed', [
                'user_id' => $user->id,
                'permissions_count' => count($permissions)
            ]);

            return $permissions;
        } catch (\Exception $e) {
            Log::error('Failed to refresh user permission cache', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Clear user permission cache
     */
    public function clearUserPermissions(int $userId): bool
    {
        try {
            $cacheKey = self::getCacheKey($userId);
            Cache::forget($cacheKey);

            Log::info('User permission cache cleared', ['user_id' => $userId]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear user permission cache', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Refresh permissions for multiple users
     */
    public function refreshMultipleUsers(Collection $users): void
    {
        foreach ($users as $user) {
            $this->refreshUserPermissions($user);
        }
    }

    /**
     * Clear permissions for multiple users
     */
    public function clearMultipleUsers(Collection $users): void
    {
        foreach ($users as $user) {
            $this->clearUserPermissions($user->id);
        }
    }

    /**
     * Refresh all users with a specific role
     */
    public function refreshUsersWithRole(Role $role): void
    {
        $users = $role->activeUsers()->get();
        $this->refreshMultipleUsers($users);
    }

    /**
     * Clear cache for all users with a specific role
     */
    public function clearUsersWithRole(Role $role): void
    {
        $users = $role->activeUsers()->get();
        $this->clearMultipleUsers($users);
    }

    /**
     * Refresh all users with a specific permission
     */
    public function refreshUsersWithPermission(Permission $permission): void
    {
        $users = $permission->users()->get();
        $this->refreshMultipleUsers($users);
    }

    /**
     * Clear cache for all users with a specific permission
     */
    public function clearUsersWithPermission(Permission $permission): void
    {
        $users = $permission->users()->get();
        $this->clearMultipleUsers($users);
    }

    /**
     * Get cached permissions for user (with fallback to fresh fetch)
     */
    public function getUserPermissions(AdminUser $user): array
    {
        $cacheKey = self::getCacheKey($user->id);
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        // Cache miss - fetch fresh and cache
        return $this->refreshUserPermissions($user);
    }

    /**
     * Check if user has permission (with caching)
     */
    public function userHasPermission(AdminUser $user, string $permission): bool
    {
        // Super admin bypass
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $permissions = $this->getUserPermissions($user);
        return in_array($permission, $permissions);
    }

    /**
     * Fetch fresh permissions from database
     */
    private function fetchUserPermissions(AdminUser $user): array
    {
        $permissions = [];

        // Get permissions from legacy array
        if ($user->permissions && is_array($user->permissions)) {
            $permissions = array_merge($permissions, $user->permissions);
        }

        // Get permissions from roles with inheritance
        $userWithRoles = $user->load([
            'activeRoles' => function ($query) {
                $query->with(['permissions' => function ($subQuery) {
                    $subQuery->where('is_active', true)
                             ->select('permissions.id', 'permissions.name');
                }]);
            }
        ]);

        foreach ($userWithRoles->activeRoles as $role) {
            // Get permissions including inherited ones from ancestors
            $rolePermissions = $role->getAllPermissionNames();
            $permissions = array_merge($permissions, $rolePermissions);
        }

        return array_unique($permissions);
    }

    /**
     * Check if user is super admin
     */
    private function isSuperAdmin(AdminUser $user): bool
    {
        return $user->hasRole('super_admin') ||
               $user->role === 'Super Admin' ||
               strtolower(str_replace(' ', '_', $user->role ?? '')) === 'super_admin';
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        // This would require Redis introspection or cache store specific methods
        // For now, return basic info
        return [
            'cache_key_prefix' => self::CACHE_KEY_PREFIX,
            'cache_ttl_minutes' => self::CACHE_TTL_MINUTES,
            'cache_driver' => config('cache.default')
        ];
    }

    /**
     * Force refresh all permission caches (admin operation)
     */
    public function refreshAllPermissionCaches(): int
    {
        $count = 0;
        AdminUser::chunk(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                $this->refreshUserPermissions($user);
                $count++;
            }
        });

        Log::info('All permission caches refreshed', ['users_processed' => $count]);

        return $count;
    }
}
