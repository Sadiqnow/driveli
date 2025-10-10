<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Response;
use Carbon\Carbon;

class EnhancedRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'default')
    {
        $key = $this->resolveRequestSignature($request, $type);
        $limits = $this->getLimitsForType($type);

        // Check if request is blocked
        if ($this->isBlocked($key)) {
            return $this->buildTooManyRequestsResponse($request);
        }

        // Apply rate limiting
        if (RateLimiter::tooManyAttempts($key, $limits['attempts'])) {
            // Implement progressive blocking
            $this->handleTooManyAttempts($key, $type);
            return $this->buildTooManyRequestsResponse($request);
        }

        // Increment attempt counter
        RateLimiter::attempt($key, $limits['attempts'], function () {
            return true;
        }, $limits['decay']);

        $response = $next($request);

        // Add rate limit headers (compatible with RedirectResponse and Response)
        return $this->addHeaders(
            $response,
            $limits['attempts'],
            RateLimiter::retriesLeft($key, $limits['attempts']),
            RateLimiter::availableIn($key)
        );
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request, string $type): string
    {
        $identifier = $request->user()?->id ?? $request->ip();
        return sprintf('rate_limit:%s:%s:%s', $type, $identifier, $request->path());
    }

    /**
     * Get rate limits based on type
     */
    protected function getLimitsForType(string $type): array
    {
        return match ($type) {
            'auth' => ['attempts' => 5, 'decay' => 900], // 5 attempts per 15 minutes
            'api' => ['attempts' => 60, 'decay' => 60], // 60 requests per minute
            'upload' => ['attempts' => 10, 'decay' => 300], // 10 uploads per 5 minutes
            'kyc' => ['attempts' => 3, 'decay' => 1800], // 3 KYC submissions per 30 minutes
            'admin' => ['attempts' => 100, 'decay' => 60], // 100 requests per minute for admin
            default => ['attempts' => 20, 'decay' => 60], // Default: 20 requests per minute
        };
    }

    /**
     * Check if request is temporarily blocked
     */
    protected function isBlocked(string $key): bool
    {
        $blockKey = "blocked:{$key}";
        return Cache::has($blockKey);
    }

    /**
     * Handle too many attempts with progressive blocking
     */
    protected function handleTooManyAttempts(string $key, string $type): void
    {
        $attemptsKey = "block_attempts:{$key}";
        $attempts = Cache::get($attemptsKey, 0) + 1;

        // Progressive blocking: 5 min, 15 min, 1 hour, 24 hours
        $blockDuration = match ($attempts) {
            1 => 300, // 5 minutes
            2 => 900, // 15 minutes
            3 => 3600, // 1 hour
            default => 86400, // 24 hours
        };

        Cache::put($attemptsKey, $attempts, $blockDuration * 2);
        Cache::put("blocked:{$key}", true, $blockDuration);

        // Log suspicious activity
        if ($attempts >= 3) {
            \Log::warning('Suspicious rate limit activity detected', [
                'key' => $key,
                'type' => $type,
                'attempts' => $attempts,
                'block_duration' => $blockDuration,
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ]);
        }
    }

    /**
     * Build rate limit exceeded response
     */
    protected function buildTooManyRequestsResponse(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $this->getRetryAfter($request),
            ], 429);
        }

        return response()->view('errors.429', [
            'retry_after' => $this->getRetryAfter($request),
        ], 429);
    }

    /**
     * Add rate limit headers to response
     */
    protected function addHeaders($response, int $maxAttempts, int $remainingAttempts, int $retryAfter)
    {
        // Add headers in a compatible way for both Response and RedirectResponse
        if (method_exists($response, 'headers')) {
            $response->headers->add([
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => max(0, $remainingAttempts),
                'X-RateLimit-Reset' => Carbon::now()->addSeconds($retryAfter)->timestamp,
            ]);
        }

        return $response;
    }

    /**
     * Get retry after time
     */
    protected function getRetryAfter(Request $request): int
    {
        $key = $this->resolveRequestSignature($request, 'default');
        return RateLimiter::availableIn($key);
    }
}