<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CustomRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $limits = $this->getLimitsForType($type);
        $key = $this->generateKey($request, $type);

        $attempts = Cache::get($key, 0);

        if ($attempts >= $limits['max_attempts']) {
            $this->logRateLimitExceeded($request, $type, $attempts);

            return response()->json([
                'success' => false,
                'message' => $limits['message'],
                'retry_after' => $limits['window_minutes'] * 60
            ], 429);
        }

        $response = $next($request);

        // Increment attempts only for failed requests (4xx, 5xx status codes)
        if ($response->status() >= 400) {
            $this->incrementAttempts($key, $limits['window_minutes']);
        }

        return $response;
    }

    private function getLimitsForType(string $type): array
    {
        $limits = [
            'auth' => [
                'max_attempts' => 5,
                'window_minutes' => 15,
                'message' => 'Too many authentication attempts. Please try again later.'
            ],
            'password_reset' => [
                'max_attempts' => 3,
                'window_minutes' => 60,
                'message' => 'Too many password reset attempts. Please try again later.'
            ],
            'otp' => [
                'max_attempts' => 5,
                'window_minutes' => 30,
                'message' => 'Too many OTP verification attempts. Please try again later.'
            ],
            'registration' => [
                'max_attempts' => 3,
                'window_minutes' => 60,
                'message' => 'Too many registration attempts. Please try again later.'
            ],
            'default' => [
                'max_attempts' => 60,
                'window_minutes' => 1,
                'message' => 'Too many requests. Please slow down.'
            ]
        ];

        return $limits[$type] ?? $limits['default'];
    }

    private function generateKey(Request $request, string $type): string
    {
        $identifier = $this->getIdentifier($request);
        return "rate_limit:{$type}:{$identifier}";
    }

    private function getIdentifier(Request $request): string
    {
        // Try to get authenticated user ID first
        if ($user = $request->user()) {
            return "user:{$user->id}";
        }

        // Fall back to IP address
        return "ip:{$request->ip()}";
    }

    private function incrementAttempts(string $key, int $windowMinutes): void
    {
        $expiry = now()->addMinutes($windowMinutes);
        Cache::put($key, Cache::get($key, 0) + 1, $expiry);
    }

    private function logRateLimitExceeded(Request $request, string $type, int $attempts): void
    {
        Log::warning('Rate limit exceeded', [
            'type' => $type,
            'attempts' => $attempts,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'timestamp' => now()->toISOString()
        ]);
    }
}