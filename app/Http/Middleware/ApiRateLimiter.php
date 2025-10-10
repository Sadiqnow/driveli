<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $key = 'api', int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $rateLimitKey = $this->resolveRateLimitKey($request, $key);
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $this->logRateLimitExceeded($request, $rateLimitKey, $maxAttempts, $decayMinutes);
            
            return $this->buildRateLimitResponse($request, $rateLimitKey, $maxAttempts, $decayMinutes);
        }

        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $rateLimitKey, $maxAttempts);
    }

    /**
     * Resolve the rate limit key for the request.
     */
    protected function resolveRateLimitKey(Request $request, string $key): string
    {
        $identifier = $this->getClientIdentifier($request);
        
        return sprintf('rate_limit:%s:%s:%s', 
            $key, 
            $identifier,
            $request->route()?->getName() ?? 'unknown'
        );
    }

    /**
     * Get client identifier for rate limiting.
     */
    protected function getClientIdentifier(Request $request): string
    {
        // Try to get authenticated user ID first
        if ($user = $request->user()) {
            return 'user:' . $user->id;
        }

        if ($adminUser = $request->user('admin')) {
            return 'admin:' . $adminUser->id;
        }

        if ($driver = $request->user('driver')) {
            return 'driver:' . $driver->id;
        }

        // Fall back to IP address with additional fingerprinting
        $ip = $request->ip();
        $userAgent = $request->userAgent() ?? 'unknown';
        
        return 'guest:' . hash('sha256', $ip . '|' . $userAgent);
    }

    /**
     * Build rate limit exceeded response.
     */
    protected function buildRateLimitResponse(Request $request, string $key, int $maxAttempts, int $decayMinutes)
    {
        $availableAt = RateLimiter::availableAt($key);
        $retryAfter = $availableAt - time();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $retryAfter,
                'max_attempts' => $maxAttempts,
                'window_minutes' => $decayMinutes
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => $availableAt,
                'Retry-After' => $retryAfter,
            ]);
        }

        return response()->view('errors.rate-limit', [
            'retryAfter' => $retryAfter,
            'maxAttempts' => $maxAttempts
        ], 429)->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => $availableAt,
            'Retry-After' => $retryAfter,
        ]);
    }

    /**
     * Add rate limit headers to response.
     */
    protected function addRateLimitHeaders($response, string $key, int $maxAttempts)
    {
        $remaining = RateLimiter::remaining($key, $maxAttempts);
        $availableAt = RateLimiter::availableAt($key);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => $availableAt,
        ]);
    }

    /**
     * Log rate limit exceeded event.
     */
    protected function logRateLimitExceeded(Request $request, string $key, int $maxAttempts, int $decayMinutes): void
    {
        Log::warning('API Rate limit exceeded', [
            'rate_limit_key' => $key,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'max_attempts' => $maxAttempts,
            'window_minutes' => $decayMinutes,
            'user_id' => $request->user()?->id,
            'timestamp' => now()->toISOString()
        ]);

        // Track suspicious activity (potential abuse)
        $this->trackSuspiciousActivity($request, $key);
    }

    /**
     * Track suspicious activity patterns.
     */
    protected function trackSuspiciousActivity(Request $request, string $key): void
    {
        $suspiciousKey = 'suspicious_activity:' . $request->ip();
        $count = Cache::increment($suspiciousKey, 1);
        
        // Set expiry on first increment
        if ($count === 1) {
            Cache::put($suspiciousKey, 1, now()->addHour());
        }
        
        // If too many rate limit violations, log as potential attack
        if ($count >= 10) {
            Log::warning('Potential API abuse detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'violation_count' => $count,
                'rate_limit_key' => $key,
                'timestamp' => now()->toISOString()
            ]);
        }
    }
}