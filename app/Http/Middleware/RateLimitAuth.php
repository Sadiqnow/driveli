<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        // Different limits for different endpoints
        $limits = $this->getLimitsForRequest($request);
        
        foreach ($limits as $limitKey => $limit) {
            $rateLimitKey = $key . ':' . $limitKey;
            
            if (RateLimiter::tooManyAttempts($rateLimitKey, $limit['max_attempts'])) {
                $seconds = RateLimiter::availableIn($rateLimitKey);
                
                return $this->buildResponse($limitKey, $limit, $seconds);
            }
            
            RateLimiter::hit($rateLimitKey, $limit['decay_minutes'] * 60);
        }
        
        return $next($request);
    }
    
    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Use IP + User-Agent for anonymous requests
        // Use IP + Email for login attempts
        $signature = $request->ip();
        
        if ($request->has('email')) {
            $signature .= ':' . $request->input('email');
        } elseif ($request->has('login')) {
            $signature .= ':' . $request->input('login');
        } else {
            $signature .= ':' . substr(md5($request->header('User-Agent', '')), 0, 10);
        }
        
        return 'rate_limit:' . md5($signature);
    }
    
    /**
     * Get rate limits based on request type
     */
    protected function getLimitsForRequest(Request $request): array
    {
        $route = $request->route()->getName();
        $limits = [];
        
        // Authentication endpoints - strict limits
        if (str_contains($route, 'login') || str_contains($request->path(), 'login')) {
            $limits['login'] = [
                'max_attempts' => config('drivelink.security.max_login_attempts', 5),
                'decay_minutes' => config('drivelink.security.lockout_duration', 900) / 60, // Convert to minutes
            ];
        }
        
        // Registration endpoints
        if (str_contains($route, 'register') || str_contains($request->path(), 'register')) {
            $limits['register'] = [
                'max_attempts' => 3,
                'decay_minutes' => 60, // 1 hour
            ];
        }
        
        // Password reset endpoints
        if (str_contains($route, 'password') || str_contains($request->path(), 'forgot-password')) {
            $limits['password_reset'] = [
                'max_attempts' => 3,
                'decay_minutes' => 60, // 1 hour
            ];
        }
        
        // OTP endpoints
        if (str_contains($request->path(), 'otp') || str_contains($request->path(), 'verify')) {
            $limits['otp'] = [
                'max_attempts' => 5,
                'decay_minutes' => 15,
            ];
        }
        
        // General API endpoints
        $limits['general'] = [
            'max_attempts' => 100,
            'decay_minutes' => 1, // Per minute
        ];
        
        return $limits;
    }
    
    /**
     * Build rate limit response
     */
    protected function buildResponse(string $limitKey, array $limit, int $retryAfter): Response
    {
        $messages = [
            'login' => 'Too many login attempts. Please try again later.',
            'register' => 'Too many registration attempts. Please try again later.',
            'password_reset' => 'Too many password reset attempts. Please try again later.',
            'otp' => 'Too many OTP requests. Please try again later.',
            'general' => 'Too many requests. Please slow down.',
        ];
        
        return response()->json([
            'success' => false,
            'message' => $messages[$limitKey] ?? $messages['general'],
            'error' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => $retryAfter,
            'limit' => $limit['max_attempts'],
            'reset_time' => now()->addSeconds($retryAfter)->toISOString(),
        ], 429);
    }
}