<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IpSecurityCheck
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
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $driverId = $request->route('driverId') ?? $request->input('driver_id');

        // Check if IP is in blocked list
        if ($this->isBlockedIp($ip)) {
            Log::warning('Blocked IP attempted access', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'driver_id' => $driverId,
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied from this IP address.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Access denied from this IP address.');
        }

        // Check for suspicious activity patterns
        if ($this->detectSuspiciousActivity($ip, $driverId)) {
            Log::warning('Suspicious activity detected', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'driver_id' => $driverId,
                'url' => $request->fullUrl(),
            ]);

            // Create trace alert for suspicious IP activity
            $this->createTraceAlert($driverId, $ip, 'suspicious_ip_activity');
        }

        // Track IP activity for this driver
        $this->trackIpActivity($ip, $driverId);

        return $next($request);
    }

    /**
     * Check if IP is blocked
     */
    protected function isBlockedIp(string $ip): bool
    {
        $blockedIps = Cache::remember('blocked_ips', 3600, function () {
            // In production, this could come from a database or config
            return config('security.blocked_ips', []);
        });

        return in_array($ip, $blockedIps);
    }

    /**
     * Detect suspicious activity patterns
     */
    protected function detectSuspiciousActivity(string $ip, $driverId): bool
    {
        if (!$driverId) {
            return false;
        }

        // Check if IP has been used by multiple drivers recently
        $recentDrivers = Cache::remember("ip_drivers:{$ip}", 3600, function () use ($ip) {
            return \App\Models\ActivityLog::where('metadata->ip', $ip)
                ->where('created_at', '>=', now()->subHours(24))
                ->distinct('user_id')
                ->pluck('user_id')
                ->toArray();
        });

        // If IP used by more than 3 different drivers in 24 hours, flag as suspicious
        return count($recentDrivers) > 3;
    }

    /**
     * Track IP activity for monitoring
     */
    protected function trackIpActivity(string $ip, $driverId): void
    {
        if (!$driverId) {
            return;
        }

        $key = "ip_activity:{$ip}:{$driverId}";
        $activity = Cache::get($key, []);

        $activity[] = [
            'timestamp' => now()->toISOString(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ];

        // Keep only last 10 activities
        if (count($activity) > 10) {
            $activity = array_slice($activity, -10);
        }

        Cache::put($key, $activity, 3600); // Cache for 1 hour
    }

    /**
     * Create trace alert for suspicious activity
     */
    protected function createTraceAlert($driverId, string $ip, string $alertType): void
    {
        try {
            \App\Models\TraceAlert::create([
                'driver_id' => $driverId,
                'alert_type' => $alertType,
                'alert_type_description' => 'Suspicious IP activity detected',
                'severity' => 'medium',
                'metadata' => [
                    'ip_address' => $ip,
                    'user_agent' => request()->userAgent(),
                    'detected_at' => now()->toISOString(),
                ],
                'triggered_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create trace alert', [
                'error' => $e->getMessage(),
                'driver_id' => $driverId,
                'alert_type' => $alertType,
            ]);
        }
    }
}
