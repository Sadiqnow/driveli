<?php

namespace App\Services;

use App\Models\DriverLocationTracking;
use App\Models\DriverNormalized;
use App\Models\AdminUser;
use App\Models\ActivityLog;
use App\Services\DeactivationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class LocationMonitoringService
{
    protected $deactivationService;

    public function __construct(DeactivationService $deactivationService)
    {
        $this->deactivationService = $deactivationService;
    }

    /**
     * Record driver location
     */
    public function recordLocation($driverId, $latitude, $longitude, $accuracy = null, $deviceInfo = null, $metadata = [])
    {
        return DriverLocationTracking::create([
            'driver_id' => $driverId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'device_info' => $deviceInfo,
            'metadata' => $metadata,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Get recent locations for a driver
     */
    public function getDriverLocations($driverId, $minutes = 60)
    {
        return DriverLocationTracking::forDriver($driverId)
            ->recent($minutes)
            ->orderBy('recorded_at', 'desc')
            ->get();
    }

    /**
     * Get locations within geographic bounds
     */
    public function getLocationsInBounds($lat1, $lon1, $lat2, $lon2, $minutes = 60)
    {
        return DriverLocationTracking::withinBounds($lat1, $lon1, $lat2, $lon2)
            ->recent($minutes)
            ->with('driver')
            ->get();
    }

    /**
     * Detect suspicious activity for a driver
     */
    public function detectSuspiciousActivity($driverId)
    {
        $locations = $this->getDriverLocations($driverId, 120); // Last 2 hours

        if ($locations->count() < 2) {
            return false;
        }

        $suspiciousPatterns = [];

        // Check for rapid location changes (possible teleportation)
        $rapidChanges = $this->detectRapidLocationChanges($locations);
        if ($rapidChanges) {
            $suspiciousPatterns[] = 'rapid_location_changes';
        }

        // Check for unusual timing patterns
        $unusualTiming = $this->detectUnusualTiming($locations);
        if ($unusualTiming) {
            $suspiciousPatterns[] = 'unusual_timing';
        }

        // Check for device anomalies
        $deviceAnomalies = $this->detectDeviceAnomalies($locations);
        if ($deviceAnomalies) {
            $suspiciousPatterns[] = 'device_anomalies';
        }

        return !empty($suspiciousPatterns) ? $suspiciousPatterns : false;
    }

    /**
     * Get monitoring dashboard data
     */
    public function getMonitoringDashboard()
    {
        $activeDrivers = DriverNormalized::where('is_current', true)->count();
        $totalLocations = DriverLocationTracking::recent(1440)->count(); // Last 24 hours
        $suspiciousActivities = $this->getSuspiciousActivities();

        return [
            'active_drivers' => $activeDrivers,
            'total_locations_recorded' => $totalLocations,
            'suspicious_activities' => $suspiciousActivities,
            'recent_locations' => DriverLocationTracking::with('driver')
                ->recent(30)
                ->orderBy('recorded_at', 'desc')
                ->limit(50)
                ->get(),
        ];
    }

    /**
     * Monitor driver in real-time (for Admin-II)
     */
    public function monitorDriver($driverId, AdminUser $admin)
    {
        $driver = DriverNormalized::findOrFail($driverId);
        $recentLocations = $this->getDriverLocations($driverId, 30); // Last 30 minutes
        $suspiciousActivity = $this->detectSuspiciousActivity($driverId);

        // Log monitoring activity
        ActivityLog::create([
            'user_type' => AdminUser::class,
            'user_id' => $admin->id,
            'action' => 'driver_monitoring_started',
            'description' => "Started monitoring driver: {$driver->full_name}",
            'metadata' => [
                'driver_id' => $driverId,
                'suspicious_activity_detected' => $suspiciousActivity,
            ],
        ]);

        return [
            'driver' => $driver,
            'recent_locations' => $recentLocations,
            'suspicious_activity' => $suspiciousActivity,
            'monitoring_started_at' => now(),
        ];
    }

    private function detectRapidLocationChanges(Collection $locations)
    {
        $maxReasonableSpeed = 120; // km/h (highway speeds)

        for ($i = 1; $i < $locations->count(); $i++) {
            $prev = $locations[$i - 1];
            $curr = $locations[$i];

            $distance = $this->calculateDistance(
                $prev->latitude, $prev->longitude,
                $curr->latitude, $curr->longitude
            );

            $timeDiff = $curr->recorded_at->diffInMinutes($prev->recorded_at);

            if ($timeDiff > 0) {
                $speed = ($distance / $timeDiff) * 60; // km/h

                if ($speed > $maxReasonableSpeed) {
                    return true;
                }
            }
        }

        return false;
    }

    private function detectUnusualTiming(Collection $locations)
    {
        // Check for locations recorded at unusual hours or patterns
        $unusualHours = $locations->filter(function ($location) {
            $hour = $location->recorded_at->hour;
            return $hour < 4 || $hour > 22; // Outside 4 AM - 10 PM
        });

        return $unusualHours->count() > $locations->count() * 0.3; // More than 30% unusual
    }

    private function detectDeviceAnomalies(Collection $locations)
    {
        // Check for multiple device types for same driver
        $deviceTypes = $locations->pluck('device_info')->unique();

        return $deviceTypes->count() > 3; // More than 3 different devices
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function getSuspiciousActivities()
    {
        // This would be implemented to track ongoing suspicious activities
        // For now, return empty array
        return [];
    }

    /**
     * Check if a driver is currently being monitored
     */
    public function isDriverBeingMonitored($driverId)
    {
        // Check if there are any active monitoring sessions or alerts for this driver
        $activeAlerts = \App\Models\TraceAlert::where('driver_id', $driverId)
            ->active()
            ->exists();

        // Check if driver has recent suspicious activity
        $recentSuspicious = \App\Models\ActivityLog::where('user_type', 'driver')
            ->where('user_id', $driverId)
            ->where('action', 'suspicious_activity')
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        return $activeAlerts || $recentSuspicious;
    }

    /**
     * Send OTP challenge for suspicious activity
     */
    public function sendOTPChallenge($driverId, $reason)
    {
        return $this->deactivationService->sendOTPChallenge('driver', $driverId, $reason);
    }
}
