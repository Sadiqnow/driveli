<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditTrailAccess
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
        $user = Auth::guard('admin')->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('admin.login')->withErrors(['error' => 'Please login to access this page.']);
        }

        // Check if user has super_admin role
        if (!$user->hasRole('super_admin')) {
            abort(403, 'Access denied. Only Super Admins can view audit trails.');
        }

        return $next($request);
    }
}
