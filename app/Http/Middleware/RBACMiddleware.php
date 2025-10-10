<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RBACMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $type (role|permission|level)
     * @param  string  ...$values
     */
    public function handle(Request $request, Closure $next, string $type, ...$values): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return $this->handleUnauthorized($request, 'Authentication required');
        }

        // Log access attempt for audit
        $this->logAccessAttempt($request, $user, $type, $values);

        // Super admin bypass (but still log)
        if ($user->hasRole('super_admin') || $user->role === 'Super Admin') {
            return $next($request);
        }

        $hasAccess = false;

        switch ($type) {
            case 'role':
                $hasAccess = $user->hasAnyRole($values);
                break;
                
            case 'permission':
                $hasAccess = $user->hasAnyPermission($values);
                break;
                
            case 'level':
                $requiredLevel = (int) ($values[0] ?? 1);
                $hasAccess = $user->getHighestRoleLevel() >= $requiredLevel;
                break;
                
            case 'role_and_permission':
                // Format: role_and_permission:role1,role2|permission1,permission2
                $parts = explode('|', $values[0] ?? '');
                $roles = explode(',', $parts[0] ?? '');
                $permissions = explode(',', $parts[1] ?? '');
                $hasAccess = $user->hasAnyRole($roles) && $user->hasAnyPermission($permissions);
                break;
                
            case 'role_or_permission':
                // Format: role_or_permission:role1,role2|permission1,permission2
                $parts = explode('|', $values[0] ?? '');
                $roles = explode(',', $parts[0] ?? '');
                $permissions = explode(',', $parts[1] ?? '');
                $hasAccess = $user->hasAnyRole($roles) || $user->hasAnyPermission($permissions);
                break;
                
            default:
                $hasAccess = false;
                break;
        }

        if (!$hasAccess) {
            $this->logAccessDenied($request, $user, $type, $values);
            return $this->handleUnauthorized($request, 'Access denied: Insufficient privileges');
        }

        return $next($request);
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
                'error_code' => 'ACCESS_DENIED',
                'timestamp' => now()->toISOString()
            ], 403);
        }

        // Determine redirect based on user state
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                            ->with('error', 'Please login to access this resource');
        }

        return redirect()->route('admin.dashboard')
                        ->with('error', $message);
    }

    /**
     * Log access attempt for audit trail
     */
    private function logAccessAttempt(Request $request, $user, string $type, array $values): void
    {
        // Handle both new RBAC system and legacy role system
        $userRoles = [];
        $userPermissions = [];
        
        try {
            // Try new RBAC system first
            if (method_exists($user, 'activeRoles')) {
                $userRoles = $user->activeRoles()->pluck('name')->toArray();
            } else {
                // Fall back to legacy role system
                $userRoles = [$user->role ?? 'Unknown'];
            }
            
            $userPermissions = array_slice($user->getAllPermissions(), 0, 20); // Limit to prevent huge logs
        } catch (\Exception $e) {
            // If there's any error, use safe defaults
            $userRoles = [$user->role ?? 'Unknown'];
            $userPermissions = [];
        }

        Log::info('RBAC Access Attempt', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'route' => $request->route()?->getName(),
            'url' => $request->url(),
            'method' => $request->method(),
            'type' => $type,
            'values' => $values,
            'user_roles' => $userRoles,
            'user_permissions' => $userPermissions,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log access denied for security monitoring
     */
    private function logAccessDenied(Request $request, $user, string $type, array $values): void
    {
        // Handle both new RBAC system and legacy role system
        $userRoles = [];
        $userLevel = 0;
        
        try {
            // Try new RBAC system first
            if (method_exists($user, 'activeRoles')) {
                $userRoles = $user->activeRoles()->pluck('name')->toArray();
            } else {
                // Fall back to legacy role system
                $userRoles = [$user->role ?? 'Unknown'];
            }
            
            $userLevel = $user->getHighestRoleLevel();
        } catch (\Exception $e) {
            // If there's any error, use safe defaults
            $userRoles = [$user->role ?? 'Unknown'];
            $userLevel = 0;
        }

        Log::warning('RBAC Access Denied', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'route' => $request->route()?->getName(),
            'url' => $request->url(),
            'method' => $request->method(),
            'type' => $type,
            'required' => $values,
            'user_roles' => $userRoles,
            'user_level' => $userLevel,
            'timestamp' => now()->toISOString()
        ]);
    }
}