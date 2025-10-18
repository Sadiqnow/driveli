<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return $this->handleUnauthorized($request, 'Authentication required');
        }

        // Super admin bypass for performance
        if ($this->isSuperAdmin($user)) {
            return $next($request);
        }

        // Check permission with caching for high-performance
        if (!$this->userHasPermission($user, $permission)) {
            $this->logAccessDenied($request, $user, $permission);
            return $this->handleUnauthorized($request, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }

    /**
     * Check if user has specific permission via user_roles → role_permissions
     * Optimized for high-performance with caching
     */
    private function userHasPermission($user, string $permission): bool
    {
        // Create cache key for user's permissions
        $cacheKey = "user_permissions:{$user->id}";
        $cacheTtl = 300; // 5 minutes cache for performance

        // Try to get permissions from cache first
        $userPermissions = Cache::get($cacheKey);

        if ($userPermissions === null) {
            // Cache miss - fetch from database with optimized query
            $userPermissions = $this->fetchUserPermissions($user);
            Cache::put($cacheKey, $userPermissions, $cacheTtl);
        }

        // Check if permission exists in user's permissions
        return in_array($permission, $userPermissions);
    }

    /**
     * Fetch user permissions from database via relationships
     * Optimized query to prevent N+1 problems
     */
    private function fetchUserPermissions($user): array
    {
        try {
            // Use eager loading to prevent N+1 queries
            $userWithPermissions = $user->load([
                'roles.permissions' => function ($query) {
                    $query->where('is_active', true)
                          ->select('permissions.id', 'permissions.name');
                }
            ]);

            $permissions = [];

            foreach ($userWithPermissions->roles as $role) {
                foreach ($role->permissions as $permission) {
                    $permissions[] = $permission->name;
                }
            }

            // Remove duplicates and return
            return array_unique($permissions);

        } catch (\Exception $e) {
            // Log error but don't break the system
            Log::error('Failed to fetch user permissions', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            // Return empty array as fallback
            return [];
        }
    }

    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'INSUFFICIENT_PERMISSION'
            ], 403);
        }

        return redirect()->route('admin.dashboard')
                        ->with('error', $message);
    }

    /**
     * Check if user is super admin
     */
    private function isSuperAdmin($user): bool
    {
        // Check legacy role field
        if ($user->role === 'Super Admin') {
            return true;
        }

        // Check normalized role
        $roleNormalized = strtolower(str_replace(' ', '_', $user->role ?? ''));
        if ($roleNormalized === 'super_admin') {
            return true;
        }

        // Check via roles relationship if available
        try {
            if (method_exists($user, 'roles')) {
                $superAdminRole = $user->roles()->where('name', 'super_admin')->first();
                if ($superAdminRole) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // Ignore errors and continue
        }

        return false;
    }

    /**
     * Log access denied for security monitoring
     */
    private function logAccessDenied(Request $request, $user, string $permission): void
    {
        Log::warning('Permission Check Failed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'route' => $request->route()?->getName(),
            'url' => $request->url(),
            'method' => $request->method(),
            'required_permission' => $permission,
            'timestamp' => now()->toISOString()
        ]);
    }
}
