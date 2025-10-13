<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\LocationMonitoringService;
use App\Models\DriverLocationTracking;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class ProcessLocationUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $locationData;
    public $driverId;

    /**
     * Create a new job instance.
     *
     * @param array $locationData
     * @param int $driverId
     * @return void
     */
    public function __construct(array $locationData, int $driverId)
    {
        $this->locationData = $locationData;
        $this->driverId = $driverId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LocationMonitoringService $locationService)
    {
        try {
            // Record the location
            $location = DriverLocationTracking::create([
                'driver_id' => $this->driverId,
                'latitude' => $this->locationData['latitude'],
                'longitude' => $this->locationData['longitude'],
                'accuracy' => $this->locationData['accuracy'] ?? null,
                'device_info' => $this->locationData['device_info'] ?? null,
                'metadata' => isset($this->locationData['metadata']) ? json_encode($this->locationData['metadata']) : null,
                'recorded_at' => $this->locationData['recorded_at'] ?? now(),
            ]);

            // Check for suspicious activity
            $isSuspicious = $locationService->detectSuspiciousActivity($this->driverId);

            if ($isSuspicious) {
                // Send OTP challenge
                $locationService->sendOTPChallenge($this->driverId, 'suspicious_location_activity');

                // Log suspicious activity
                ActivityLog::create([
                    'user_type' => 'driver',
                    'user_id' => $this->driverId,
                    'action' => 'suspicious_activity_detected',
                    'description' => 'Suspicious location activity detected during location update',
                    'metadata' => [
                        'location_id' => $location->id,
                        'coordinates' => [
                            'lat' => $this->locationData['latitude'],
                            'lng' => $this->locationData['longitude'],
                        ],
                        'accuracy' => $this->locationData['accuracy'] ?? null,
                        'device_info' => $this->locationData['device_info'] ?? null,
                    ],
                    'ip_address' => request()->ip(),
                ]);

                Log::warning("Suspicious activity detected for driver {$this->driverId}", [
                    'location_id' => $location->id,
                    'coordinates' => $this->locationData,
                ]);
            }

            // Clean up old location data (keep last 30 days)
            DriverLocationTracking::where('driver_id', $this->driverId)
                ->where('recorded_at', '<', now()->subDays(30))
                ->delete();

        } catch (\Exception $e) {
            Log::error("Failed to process location update for driver {$this->driverId}: {$e->getMessage()}", [
                'location_data' => $this->locationData,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Location update job failed for driver {$this->driverId}", [
            'exception' => $exception->getMessage(),
            'location_data' => $this->locationData,
        ]);
    }
}
