<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\PermissionLog;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $role, string $permission): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return $this->handleUnauthorized($request, 'Authentication required');
        }

        // Super admin bypass - they have access to everything
        if ($this->isSuperAdmin($user)) {
            return $next($request);
        }

        // Check if user has the required role
        if (!$this->userHasRole($user, $role)) {
            $this->logAccessDenied($request, $user, $role, $permission, 'insufficient_role');
            return $this->handleUnauthorized($request, 'Access denied. Insufficient role permissions.');
        }

        // Check if user has the required permission
        if (!$this->userHasPermission($user, $permission)) {
            $this->logAccessDenied($request, $user, $role, $permission, 'insufficient_permission');
            return $this->handleUnauthorized($request, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }

    /**
     * Check if user has the required role
     */
    private function userHasRole($user, string $role): bool
    {
        // Check legacy role field
        if ($user->role === $role) {
            return true;
        }

        // Check normalized role
        $roleNormalized = strtolower(str_replace(' ', '_', $user->role ?? ''));
        $requiredNormalized = strtolower(str_replace(' ', '_', $role));
        if ($roleNormalized === $requiredNormalized) {
            return true;
        }

        // Check via roles relationship if available
        try {
            if (method_exists($user, 'roles')) {
                $userRole = $user->roles()->where('name', $role)->first();
                if ($userRole) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // Ignore errors and continue
        }

        return false;
    }

    /**
     * Check if user has the required permission
     */
    private function userHasPermission($user, string $permission): bool
    {
        // Check in direct permissions array (legacy)
        $userPermissions = $user->permissions ?? [];
        if (is_array($userPermissions) && in_array($permission, $userPermissions)) {
            return true;
        }

        // Check via roles relationship if available
        try {
            if (method_exists($user, 'roles')) {
                foreach ($user->roles as $role) {
                    if (method_exists($role, 'permissions')) {
                        $rolePermissions = $role->permissions()->where('name', $permission)->first();
                        if ($rolePermissions) {
                            return true;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore errors and continue
        }

        return false;
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
                $superAdminRole = $user->roles()->where('name', 'super_admin')->orWhere('name', 'Super Admin')->first();
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
     * Handle unauthorized access
     */
    private function handleUnauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'ACCESS_DENIED'
            ], 403);
        }

        return redirect()->route('admin.dashboard')
                        ->with('error', $message);
    }

    /**
     * Log access denied for security monitoring
     */
    private function logAccessDenied(Request $request, $user, string $role, string $permission, string $reason): void
    {
        // Log to Laravel log
        Log::warning('RolePermissionMiddleware Access Denied', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'route' => $request->route()?->getName(),
            'url' => $request->url(),
            'method' => $request->method(),
            'required_role' => $role,
            'required_permission' => $permission,
            'reason' => $reason,
            'timestamp' => now()->toISOString()
        ]);

        // Log to audit_logs table if it exists
        try {
            if (Schema::hasTable('audit_logs')) {
                \App\Models\AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'access_denied',
                    'resource_type' => 'route',
                    'resource_id' => $request->route()?->getName() ?? 'unknown',
                    'old_values' => null,
                    'new_values' => json_encode([
                        'required_role' => $role,
                        'required_permission' => $permission,
                        'reason' => $reason,
                        'url' => $request->url(),
                        'method' => $request->method(),
                        'ip' => $request->ip()
                    ]),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if audit logging fails
            Log::debug('Failed to log to audit_logs table: ' . $e->getMessage());
        }

        // Log to permission_logs table for analytics
        try {
            if (Schema::hasTable('permission_logs')) {
                \App\Models\PermissionLog::create([
                    'user_id' => $user->id,
                    'permission_name' => $permission,
                    'result' => 'denied',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'route_name' => $request->route()?->getName(),
                    'method' => $request->method(),
                    'metadata' => json_encode([
                        'required_role' => $role,
                        'reason' => $reason,
                        'url' => $request->url()
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if permission logging fails
            Log::debug('Failed to log to permission_logs table: ' . $e->getMessage());
        }
    }
}
