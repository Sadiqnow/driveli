<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitOtpRequests
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
        $key = $this->resolveRequestSignature($request);

        // Allow 3 OTP requests per minute per driver/IP combination
        $maxAttempts = 3;
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('OTP rate limit exceeded', [
                'key' => $key,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'driver_id' => $request->route('driverId') ?? $request->input('driver_id'),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many OTP requests. Please try again in ' . $seconds . ' seconds.',
                    'retry_after' => $seconds,
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }

            return back()->withErrors([
                'otp' => 'Too many OTP requests. Please try again in ' . $seconds . ' seconds.'
            ])->withInput();
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $driverId = $request->route('driverId') ?? $request->input('driver_id');
        $ip = $request->ip();

        // Create a unique key combining driver ID and IP address
        return 'otp:' . $driverId . ':' . $ip;
    }
}
