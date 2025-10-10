<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleBasedAccessControl
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type, string $value)
    {
        $user = Auth::guard('admin')->user();
        
        if (!$user) {
            Log::warning('RBAC: Unauthorized access attempt', [
                'ip' => $request->ip(),
                'route' => $request->route()->getName(),
                'user_agent' => $request->userAgent()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'error_code' => 'AUTH_REQUIRED'
                ], 401);
            }
            
            return redirect()->route('admin.login')->with('error', 'Please log in to access this area.');
        }

        // Check if user is active
        if (!$user->isActive()) {
            Auth::guard('admin')->logout();
            Log::warning('RBAC: Inactive user attempted access', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive',
                    'error_code' => 'ACCOUNT_INACTIVE'
                ], 403);
            }
            
            return redirect()->route('admin.login')->with('error', 'Your account is inactive.');
        }

        $hasAccess = false;

        switch ($type) {
            case 'role':
                $hasAccess = $user->hasRole($value);
                break;
                
            case 'permission':
                $hasAccess = $user->hasPermission($value);
                break;
                
            case 'role_or_permission':
                $roles = explode('|', $value);
                $hasAccess = $user->hasAnyRole($roles) || $user->hasAnyPermission($roles);
                break;
                
            case 'super_admin':
                $hasAccess = $user->isSuperAdmin();
                break;
                
            default:
                Log::error('RBAC: Invalid middleware type', [
                    'type' => $type,
                    'value' => $value,
                    'route' => $request->route()->getName()
                ]);
                $hasAccess = false;
        }

        if (!$hasAccess) {
            Log::warning('RBAC: Access denied', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_type' => $type,
                'required_value' => $value,
                'route' => $request->route()->getName(),
                'ip' => $request->ip()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }

            abort(403, 'You do not have permission to access this resource.');
        }

        // Log successful access for audit
        Log::info('RBAC: Access granted', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'route' => $request->route()->getName(),
            'ip' => $request->ip()
        ]);

        return $next($request);
    }
}