<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class KycRateLimit
{
    /**
     * Maximum attempts per IP per hour
     */
    private const MAX_ATTEMPTS_PER_HOUR = 10;

    /**
     * Maximum attempts per driver per hour
     */
    private const MAX_DRIVER_ATTEMPTS_PER_HOUR = 5;

    /**
     * Blocking duration in minutes
     */
    private const BLOCK_DURATION_MINUTES = 60;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $driver = auth()->guard('driver')->user();
        
        // Check IP-based rate limiting
        $ipKey = "kyc_rate_limit:ip:{$ip}:" . now()->format('Y-m-d-H');
        $ipAttempts = Cache::get($ipKey, 0);
        
        if ($ipAttempts >= self::MAX_ATTEMPTS_PER_HOUR) {
            Log::warning('KYC rate limit exceeded by IP', [
                'ip_address' => $ip,
                'attempts' => $ipAttempts,
                'driver_id' => $driver ? $driver->id : null
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many attempts. Please try again later.',
                    'retry_after' => self::BLOCK_DURATION_MINUTES * 60
                ], 429);
            }

            return back()->withErrors([
                'rate_limit' => 'Too many attempts from your IP address. Please try again in ' . self::BLOCK_DURATION_MINUTES . ' minutes.'
            ]);
        }

        // Check driver-based rate limiting if authenticated
        if ($driver) {
            $driverKey = "kyc_rate_limit:driver:{$driver->id}:" . now()->format('Y-m-d-H');
            $driverAttempts = Cache::get($driverKey, 0);
            
            if ($driverAttempts >= self::MAX_DRIVER_ATTEMPTS_PER_HOUR) {
                Log::warning('KYC rate limit exceeded by driver', [
                    'driver_id' => $driver->id,
                    'attempts' => $driverAttempts,
                    'ip_address' => $ip
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many KYC attempts. Please try again later.',
                        'retry_after' => self::BLOCK_DURATION_MINUTES * 60
                    ], 429);
                }

                return back()->withErrors([
                    'rate_limit' => 'Too many KYC attempts. Please try again in ' . self::BLOCK_DURATION_MINUTES . ' minutes.'
                ]);
            }
        }

        // Increment counters
        Cache::put($ipKey, $ipAttempts + 1, 3600); // 1 hour TTL
        
        if ($driver) {
            $driverKey = "kyc_rate_limit:driver:{$driver->id}:" . now()->format('Y-m-d-H');
            $driverAttempts = Cache::get($driverKey, 0);
            Cache::put($driverKey, $driverAttempts + 1, 3600); // 1 hour TTL
        }

        return $next($request);
    }
}