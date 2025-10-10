<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return $this->handleUnauthorized($request, 'Authentication required');
        }

        // Super admin bypass
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            return $this->handleUnauthorized($request, 'Insufficient role permissions');
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
                'error_code' => 'INSUFFICIENT_ROLE'
            ], 403);
        }

        return redirect()->route('admin.login')
                        ->with('error', $message);
    }
}