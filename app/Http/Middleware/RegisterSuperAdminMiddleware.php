<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RegisterSuperAdminMiddleware
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
        // Register the SuperAdminDriverAccess middleware alias if not already registered
        if (!app('router')->getMiddleware()['superadmin.driver.access']) {
            app('router')->aliasMiddleware('superadmin.driver.access', \App\Http\Middleware\SuperAdminDriverAccess::class);
        }

        return $next($request);
    }
}
