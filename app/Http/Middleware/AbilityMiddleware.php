<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbilityMiddleware
{
    public function handle(Request $request, Closure $next, $ability = null)
    {
        // If no ability required, allow
        if (is_null($ability)) {
            return $next($request);
        }

        // Check if user is authenticated
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if token has the required ability
        if (!$user->currentAccessToken()->can($ability)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
