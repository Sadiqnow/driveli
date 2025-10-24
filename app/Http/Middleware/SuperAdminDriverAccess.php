<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminDriverAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated as admin
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            return redirect()->route('admin.login')->with('error', 'Please login to access this area');
        }

        $user = Auth::guard('admin')->user();

        // Check if user is Super Admin (bypass all other checks)
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // For non-super admins, check if they have the required role
        if (!$user->hasRole('Super Admin') && !$user->hasRole('super_admin')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Super Admin privileges required.'
                ], 403);
            }
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        // Check if user has the specific permission for driver management
        if (!$user->hasPermission('manage_drivers')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Driver management permission required.'
                ], 403);
            }
            abort(403, 'Access denied. Driver management permission required.');
        }

        return $next($request);
    }
}
