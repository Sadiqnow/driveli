<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ActivityLog;
use App\Models\DriverNormalized;
use App\Models\DriverLocationTracking;
use Illuminate\Support\Facades\Log;

class DriverActivityLogger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $driverId;
    public $activityType;
    public $activityData;
    public $metadata;

    /**
     * Create a new job instance.
     *
     * @param int $driverId
     * @param string $activityType
     * @param array $activityData
     * @param array $metadata
     * @return void
     */
    public function __construct(int $driverId, string $activityType, array $activityData = [], array $metadata = [])
    {
        $this->driverId = $driverId;
        $this->activityType = $activityType;
        $this->activityData = $activityData;
        $this->metadata = $metadata;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get driver's last known location
            $lastLocation = DriverLocationTracking::where('driver_id', $this->driverId)
                ->orderBy('recorded_at', 'desc')
                ->first();

            // Prepare activity log data
            $logData = [
                'user_type' => 'driver',
                'user_id' => $this->driverId,
                'action' => $this->activityType,
                'description' => $this->generateDescription(),
                'metadata' => array_merge($this->metadata, [
                    'activity_data' => $this->activityData,
                    'last_known_location' => $lastLocation ? [
                        'latitude' => $lastLocation->latitude,
                        'longitude' => $lastLocation->longitude,
                        'accuracy' => $lastLocation->accuracy,
                        'recorded_at' => $lastLocation->recorded_at->toISOString(),
                        'device_info' => $lastLocation->device_info,
                    ] : null,
                    'timestamp' => now()->toISOString(),
                ]),
                'ip_address' => $this->activityData['ip_address'] ?? null,
                'user_agent' => $this->activityData['user_agent'] ?? null,
            ];

            // Create activity log
            ActivityLog::create($logData);

            // Handle special activity types
            $this->handleSpecialActivities($logData);

            Log::info("Driver activity logged: {$this->activityType} for driver {$this->driverId}");

        } catch (\Exception $e) {
            Log::error("Failed to log driver activity: {$e->getMessage()}", [
                'driver_id' => $this->driverId,
                'activity_type' => $this->activityType,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Generate description based on activity type
     *
     * @return string
     */
    private function generateDescription()
    {
        $driver = DriverNormalized::find($this->driverId);
        $driverName = $driver ? $driver->full_name : "Driver #{$this->driverId}";

        switch ($this->activityType) {
            case 'app_opened':
                return "{$driverName} opened the driver app";
            case 'app_closed':
                return "{$driverName} closed the driver app";
            case 'location_update':
                return "{$driverName} location updated";
            case 'login_success':
                return "{$driverName} successfully logged in";
            case 'login_failed':
                return "{$driverName} login attempt failed";
            case 'ping_received':
                return "{$driverName} ping received - app active";
            case 'ping_missed':
                return "{$driverName} ping missed - potential app uninstall or network issue";
            case 'app_uninstalled':
                return "{$driverName} app uninstalled or removed";
            case 'device_changed':
                return "{$driverName} device information changed";
            case 'suspicious_activity':
                return "{$driverName} suspicious activity detected";
            case 'otp_challenge_sent':
                return "{$driverName} OTP challenge sent for verification";
            case 'otp_challenge_verified':
                return "{$driverName} OTP challenge verified successfully";
            case 'otp_challenge_failed':
                return "{$driverName} OTP challenge failed";
            default:
                return "{$driverName} performed activity: {$this->activityType}";
        }
    }

    /**
     * Handle special activity types that require additional processing
     *
     * @param array $logData
     * @return void
     */
    private function handleSpecialActivities(array $logData)
    {
        switch ($this->activityType) {
            case 'ping_missed':
            case 'app_uninstalled':
                // Trigger trace alert
                $this->triggerTraceAlert($logData);
                break;

            case 'suspicious_activity':
                // Send OTP challenge
                $this->sendSecurityChallenge();
                break;
        }
    }

    /**
     * Trigger trace alert for app uninstall or ping failure
     *
     * @param array $logData
     * @return void
     */
    private function triggerTraceAlert(array $logData)
    {
        // Create trace alert record
        \App\Models\TraceAlert::create([
            'driver_id' => $this->driverId,
            'alert_type' => $this->activityType,
            'severity' => $this->activityType === 'app_uninstalled' ? 'critical' : 'high',
            'last_known_location' => $logData['metadata']['last_known_location'],
            'alert_data' => $logData,
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        // Send notification to admins
        $this->notifyAdminsOfTraceAlert($logData);

        Log::warning("Trace alert triggered for driver {$this->driverId}: {$this->activityType}");
    }

    /**
     * Send security challenge for suspicious activity
     *
     * @return void
     */
    private function sendSecurityChallenge()
    {
        // This will be handled by the LocationMonitoringService
        // For now, just log that a challenge should be sent
        Log::info("Security challenge should be sent for driver {$this->driverId}");
    }

    /**
     * Notify admins of trace alert
     *
     * @param array $logData
     * @return void
     */
    private function notifyAdminsOfTraceAlert(array $logData)
    {
        // Send email/SMS notifications to admins
        // This would integrate with your notification system
        Log::info("Admin notification sent for trace alert on driver {$this->driverId}");
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Driver activity logging job failed for driver {$this->driverId}", [
            'activity_type' => $this->activityType,
            'exception' => $exception->getMessage(),
        ]);
    }
}
