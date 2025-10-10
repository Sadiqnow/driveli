<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

/**
 * Rate Limiting Middleware
 * 
 * Provides API rate limiting functionality with configurable limits
 * for different user types and endpoints.
 * 
 * @package App\Http\Middleware
 */
class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $key Rate limiting key/identifier
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Time window in minutes
     * @return mixed
     * @throws ThrottleRequestsException
     */
    public function handle(Request $request, Closure $next, string $key = 'api', int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $rateLimitKey = $this->resolveRequestSignature($request, $key);

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $retryAfter,
                'limit' => $maxAttempts,
                'window' => $decayMinutes . ' minutes',
            ], 429)->header('Retry-After', $retryAfter);
        }

        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers to response
        $remaining = RateLimiter::remaining($rateLimitKey, $maxAttempts);
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->timestamp,
        ]);

        return $response;
    }

    /**
     * Resolve the rate limiting key for the request.
     *
     * @param Request $request
     * @param string $key
     * @return string
     */
    protected function resolveRequestSignature(Request $request, string $key): string
    {
        // Different rate limiting strategies based on key
        switch ($key) {
            case 'auth':
                // Authentication attempts - by IP
                return 'auth:' . $request->ip();
                
            case 'admin':
                // Admin API calls - by authenticated admin user
                $admin = $request->user('admin');
                return 'admin:' . ($admin ? $admin->id : $request->ip());
                
            case 'driver':
                // Driver API calls - by authenticated driver
                $driver = $request->user('driver');
                return 'driver:' . ($driver ? $driver->id : $request->ip());
                
            case 'registration':
                // Registration attempts - by IP (stricter)
                return 'registration:' . $request->ip();
                
            case 'upload':
                // File upload attempts - by user or IP
                $user = $request->user() ?? $request->user('admin') ?? $request->user('driver');
                return 'upload:' . ($user ? $user->id : $request->ip());
                
            case 'search':
                // Search requests - by user or IP
                $user = $request->user() ?? $request->user('admin') ?? $request->user('driver');
                return 'search:' . ($user ? $user->id : $request->ip());
                
            default:
                // General API calls - by IP or authenticated user
                $user = $request->user() ?? $request->user('admin') ?? $request->user('driver');
                return $key . ':' . ($user ? $user->id : $request->ip());
        }
    }

    /**
     * Get rate limit configuration based on user type and endpoint.
     *
     * @param Request $request
     * @param string $type
     * @return array [maxAttempts, decayMinutes]
     */
    public static function getLimitsFor(Request $request, string $type): array
    {
        $configs = [
            'auth' => [10, 1], // 10 attempts per minute for auth
            'registration' => [5, 15], // 5 attempts per 15 minutes for registration
            'admin' => [1000, 1], // 1000 requests per minute for admin users
            'driver' => [500, 1], // 500 requests per minute for drivers
            'upload' => [20, 1], // 20 uploads per minute
            'search' => [100, 1], // 100 searches per minute
            'api' => [60, 1], // Default: 60 requests per minute
        ];

        return $configs[$type] ?? $configs['api'];
    }
}