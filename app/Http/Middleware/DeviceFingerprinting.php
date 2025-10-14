<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class DeviceFingerprinting
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
        $driverId = $request->route('driverId') ?? $request->input('driver_id');

        if ($driverId) {
            $fingerprint = $this->generateDeviceFingerprint($request);

            // Check for device anomalies
            $this->checkDeviceAnomalies($driverId, $fingerprint, $request);

            // Store device fingerprint for tracking
            $this->storeDeviceFingerprint($driverId, $fingerprint, $request);
        }

        return $next($request);
    }

    /**
     * Generate device fingerprint from request data
     */
    protected function generateDeviceFingerprint(Request $request): string
    {
        $components = [
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
            $request->header('DNT'), // Do Not Track
            $request->header('Upgrade-Insecure-Requests'),
            $request->header('Sec-Ch-Ua'), // Chrome user agent hints
            $request->header('Sec-Ch-Ua-Mobile'),
            $request->header('Sec-Ch-Ua-Platform'),
            $request->header('Sec-Fetch-Dest'),
            $request->header('Sec-Fetch-Mode'),
            $request->header('Sec-Fetch-Site'),
            $request->header('Sec-Fetch-User'),
        ];

        // Filter out null values and create hash
        $filtered = array_filter($components, function ($value) {
            return $value !== null;
        });

        return Hash::make(implode('|', $filtered));
    }

    /**
     * Check for device anomalies
     */
    protected function checkDeviceAnomalies($driverId, string $fingerprint, Request $request): void
    {
        $knownFingerprints = Cache::get("driver_fingerprints:{$driverId}", []);

        // If this is a new fingerprint, check if we should flag it
        if (!in_array($fingerprint, $knownFingerprints)) {
            $fingerprintCount = count($knownFingerprints);

            // If driver has used more than 3 different devices recently, flag as suspicious
            if ($fingerprintCount >= 3) {
                Log::warning('Multiple device fingerprint detected', [
                    'driver_id' => $driverId,
                    'fingerprint_count' => $fingerprintCount,
                    'new_fingerprint' => substr($fingerprint, 0, 10) . '...',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                $this->createDeviceAnomalyAlert($driverId, $fingerprint, $request);
            }
        }
    }

    /**
     * Store device fingerprint for tracking
     */
    protected function storeDeviceFingerprint($driverId, string $fingerprint, Request $request): void
    {
        $key = "driver_fingerprints:{$driverId}";
        $fingerprints = Cache::get($key, []);

        // Add new fingerprint if not already present
        if (!in_array($fingerprint, $fingerprints)) {
            $fingerprints[] = $fingerprint;

            // Keep only last 5 fingerprints to prevent memory issues
            if (count($fingerprints) > 5) {
                $fingerprints = array_slice($fingerprints, -5);
            }

            Cache::put($key, $fingerprints, 86400); // Cache for 24 hours
        }

        // Store detailed device info for this fingerprint
        $deviceKey = "device_info:{$driverId}:{$fingerprint}";
        $deviceInfo = Cache::get($deviceKey, []);

        $deviceInfo[] = [
            'timestamp' => now()->toISOString(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => [
                'accept_language' => $request->header('Accept-Language'),
                'platform' => $request->header('Sec-Ch-Ua-Platform'),
                'mobile' => $request->header('Sec-Ch-Ua-Mobile'),
            ],
        ];

        // Keep only last 10 entries per device
        if (count($deviceInfo) > 10) {
            $deviceInfo = array_slice($deviceInfo, -10);
        }

        Cache::put($deviceKey, $deviceInfo, 86400);
    }

    /**
     * Create alert for device anomaly
     */
    protected function createDeviceAnomalyAlert($driverId, string $fingerprint, Request $request): void
    {
        try {
            \App\Models\TraceAlert::create([
                'driver_id' => $driverId,
                'alert_type' => 'device_anomaly',
                'alert_type_description' => 'Multiple device fingerprints detected',
                'severity' => 'high',
                'metadata' => [
                    'fingerprint_hash' => substr($fingerprint, 0, 10) . '...',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'detected_at' => now()->toISOString(),
                    'headers' => [
                        'accept_language' => $request->header('Accept-Language'),
                        'platform' => $request->header('Sec-Ch-Ua-Platform'),
                        'mobile' => $request->header('Sec-Ch-Ua-Mobile'),
                    ],
                ],
                'triggered_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create device anomaly alert', [
                'error' => $e->getMessage(),
                'driver_id' => $driverId,
                'fingerprint' => substr($fingerprint, 0, 10) . '...',
            ]);
        }
    }
}
