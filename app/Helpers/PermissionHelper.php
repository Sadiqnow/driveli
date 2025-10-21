<?php

namespace App\Helpers;

use App\Services\RoleSyncService;
use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    protected static $roleSyncService;

    /**
     * Initialize the RoleSyncService
     */
    protected static function getRoleSyncService()
    {
        if (!self::$roleSyncService) {
            self::$roleSyncService = app(RoleSyncService::class);
        }
        return self::$roleSyncService;
    }

    /**
     * Check if the current admin user has a specific permission
     * Uses caching for performance
     */
    public static function hasPermission(string $permission): bool
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return false;
        }

        // For debugging - check if RoleSyncService has permissions
        $roleSync = self::getRoleSyncService();
        $permissions = $roleSync->getUserPermissions($user);

        // If RoleSyncService returns permissions, use it
        if (!empty($permissions) && in_array($permission, $permissions)) {
            return true;
        }

        // Fallback to legacy permissions array
        if ($user->permissions && is_array($user->permissions)) {
            return in_array($permission, $user->permissions);
        }

        // For admin role, grant basic permissions
        $userRole = $user->role ?? '';
        if (strtolower($userRole) === 'admin') {
            $adminPermissions = [
                'view_dashboard', 'view_drivers', 'view_companies',
                'manage_matching', 'view_reports', 'manage_settings',
                'manage_drivers', 'manage_requests', 'send_notifications' // Include existing permissions
            ];
            if (in_array($permission, $adminPermissions)) {
                return true;
            }
        }

        // DEBUG: Force return true for all admin permissions to test menu filtering
        if (strtolower($user->role ?? '') === 'admin') {
            $adminPermissions = [
                'view_dashboard', 'view_drivers', 'view_companies',
                'manage_matching', 'view_reports', 'manage_settings',
                'manage_drivers', 'manage_requests', 'send_notifications'
            ];
            if (in_array($permission, $adminPermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions
     */
    public static function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public static function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user has a specific role
     */
    public static function hasRole(string $role): bool
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return false;
        }

        // DEBUG: Force return true for Admin role to test menu filtering
        if (strtolower($role) === 'admin' && strtolower($user->role ?? '') === 'admin') {
            return true;
        }

        // Check via roles relationship
        try {
            if (method_exists($user, 'roles')) {
                return $user->roles()->where('name', $role)->exists();
            }
        } catch (\Exception $e) {
            // Ignore errors and continue to fallback
        }

        // Fallback to legacy role field - check exact match first, then normalized
        $userRole = $user->role ?? '';
        if ($userRole === $role) {
            return true;
        }

        // Also check case-insensitive match
        if (strtolower($userRole) === strtolower($role)) {
            return true;
        }

        // Also check normalized version
        return strtolower(str_replace(' ', '_', $userRole)) === strtolower($role);
    }

    /**
     * Simplified role check for debugging
     */
    public static function checkRole(string $role): bool
    {
        $user = Auth::guard('admin')->user();
        if (!$user) return false;

        $userRole = $user->role ?? '';
        return strtolower($userRole) === strtolower($role);
    }

    /**
     * Force set role for testing - temporary method
     */
    public static function forceSetRole(string $role): void
    {
        $user = Auth::guard('admin')->user();
        if ($user) {
            $user->role = $role;
        }
    }

    /**
     * Debug function to check user role and permissions
     */
    public static function debugUserAccess(): array
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return ['error' => 'No user logged in'];
        }

        $roleSync = self::getRoleSyncService();
        $permissions = $roleSync->getUserPermissions($user);

        $filteredMenus = self::getFilteredMenus();
        return [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role_field' => $user->role ?? 'null',
            'permissions_from_service' => $permissions,
            'permissions_count' => count($permissions),
            'has_dashboard_permission' => self::hasPermission('view_dashboard'),
            'has_admin_role' => self::hasRole('Admin'),
            'is_super_admin' => self::isSuperAdmin(),
            'filtered_menus_count' => count($filteredMenus),
            'filtered_menus' => array_keys($filteredMenus),
            'debug_dashboard_access' => self::canAccessMenu(config('menus.dashboard', [])),
            'debug_reports_access' => self::canAccessMenu(config('menus.reports', [])),
        ];
    }

    /**
     * Check if user has any of the given roles
     */
    public static function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if (self::hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get user's current role name
     */
    public static function getCurrentRole(): ?string
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return null;
        }

        // Try to get from roles relationship
        try {
            if (method_exists($user, 'roles')) {
                $role = $user->roles()->first();
                if ($role) {
                    return $role->name;
                }
            }
        } catch (\Exception $e) {
            // Ignore errors and continue to fallback
        }

        // Fallback to legacy role field
        return $user->role ?? null;
    }

    /**
     * Check if current user is super admin
     */
    public static function isSuperAdmin(): bool
    {
        return self::hasRole('super_admin') ||
               self::hasRole('Super Admin') ||
               strtolower(str_replace(' ', '_', self::getCurrentRole() ?? '')) === 'super_admin';
    }

    /**
     * Get filtered menu items based on user permissions
     */
    public static function getFilteredMenus(): array
    {
        $allMenus = config('menus', []);
        $filteredMenus = [];

        foreach ($allMenus as $key => $menu) {
            if (self::canAccessMenu($menu)) {
                $filteredMenu = $menu;

                // Filter submenu items if they exist
                if (isset($menu['submenu'])) {
                    $filteredSubmenu = [];
                    foreach ($menu['submenu'] as $subKey => $subMenu) {
                        if (self::canAccessMenu($subMenu)) {
                            $filteredSubmenu[$subKey] = $subMenu;
                        }
                    }
                    $filteredMenu['submenu'] = $filteredSubmenu;
                }

                $filteredMenus[$key] = $filteredMenu;
            }
        }

        return $filteredMenus;
    }

    /**
     * Check if user can access a menu item
     */
    protected static function canAccessMenu(array $menu): bool
    {
        // Super admin can access everything
        if (self::isSuperAdmin()) {
            return true;
        }

        // DEBUG: For admin users, allow access to dashboard and reports
        $user = Auth::guard('admin')->user();
        if ($user && strtolower($user->role ?? '') === 'admin') {
            $allowedPermissions = ['view_dashboard', 'view_reports'];
            if (isset($menu['permission']) && in_array($menu['permission'], $allowedPermissions)) {
                return true;
            }
        }

        // Check roles first (simpler check)
        if (isset($menu['roles']) && !self::hasAnyRole($menu['roles'])) {
            return false;
        }

        // Check permission
        if (isset($menu['permission']) && !self::hasPermission($menu['permission'])) {
            return false;
        }

        return true;
    }

    /**
     * Clear permission cache for current user
     */
    public static function clearPermissionCache(): bool
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return false;
        }

        return self::getRoleSyncService()->clearUserPermissions($user->id);
    }

    /**
     * Refresh permission cache for current user
     */
    public static function refreshPermissionCache(): array
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return [];
        }

        return self::getRoleSyncService()->refreshUserPermissions($user);
    }
}
