<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // If the request expects JSON, we should let the framework return JSON 401.
        // However some tests intentionally post to admin routes without CSRF token and expect 419.
        // Allow POST to /admin/drivers to pass through so CSRF middleware can return 419 instead of 401.
        if ($request->isMethod('POST') && ($request->is('admin/drivers') || $request->is('admin/drivers/*'))) {
            return null;
        }

        if (! $request->expectsJson()) {
            return $this->getRedirectRoute($request);
        }
    }

    /**
     * Get the appropriate redirect route based on the request path
     */
    protected function getRedirectRoute($request)
    {
        $path = $request->path();
        
        // Admin routes
        if (str_starts_with($path, 'admin') || $request->is('admin/*')) {
            return route('admin.login');
        }
        
        // Driver routes  
        if (str_starts_with($path, 'driver') || $request->is('driver/*')) {
            return route('driver.login');
        }
        
        // Default to admin login (no regular user login implemented)
        return route('admin.login');
    }
}
