<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJson
{
    /**
     * Force the request to expect JSON for API routes so Laravel returns JSON errors.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
