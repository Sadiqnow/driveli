<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return $this->handleUnauthorized($request, 'Authentication required');
        }

        // Super admin bypass
        if ($user->hasRole('super_admin') || $user->role === 'Super Admin') {
            return $next($request);
        }

        // Check if user has any of the required permissions
        if (!$user->hasAnyPermission($permissions)) {
            return $this->handleUnauthorized($request, 'Insufficient permissions');
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
                'error_code' => 'INSUFFICIENT_PERMISSION'
            ], 403);
        }

        return redirect()->route('admin.dashboard')
                        ->with('error', $message);
    }
}