<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\DriverNormalized;
use App\Models\DriverLocationTracking;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DriverPingMonitor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $driverId;
    public $expectedPingInterval; // in minutes

    /**
     * Create a new job instance.
     *
     * @param int $driverId
     * @param int $expectedPingInterval
     * @return void
     */
    public function __construct(int $driverId, int $expectedPingInterval = 5)
    {
        $this->driverId = $driverId;
        $this->expectedPingInterval = $expectedPingInterval;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $driver = DriverNormalized::find($this->driverId);

            if (!$driver || !$driver->is_current) {
                Log::info("Skipping ping monitor for inactive driver {$this->driverId}");
                return;
            }

            // Check last activity timestamp
            $lastActivity = ActivityLog::where('user_type', 'driver')
                ->where('user_id', $this->driverId)
                ->whereIn('action', ['ping_received', 'location_update', 'app_opened'])
                ->orderBy('created_at', 'desc')
                ->first();

            $lastLocation = DriverLocationTracking::where('driver_id', $this->driverId)
                ->orderBy('recorded_at', 'desc')
                ->first();

            // Determine the most recent activity
            $lastActivityTime = null;
            if ($lastActivity && $lastLocation) {
                $lastActivityTime = max(
                    $lastActivity->created_at,
                    Carbon::parse($lastLocation->recorded_at)
                );
            } elseif ($lastActivity) {
                $lastActivityTime = $lastActivity->created_at;
            } elseif ($lastLocation) {
                $lastActivityTime = Carbon::parse($lastLocation->recorded_at);
            }

            if (!$lastActivityTime) {
                // No activity ever recorded - this might be a new driver
                Log::info("No activity recorded for driver {$this->driverId} - possibly new driver");
                return;
            }

            $minutesSinceLastActivity = $lastActivityTime->diffInMinutes(now());

            // Check if ping is missed
            if ($minutesSinceLastActivity > ($this->expectedPingInterval * 2)) {
                // Ping missed - log the event
                DriverActivityLogger::dispatch(
                    $this->driverId,
                    'ping_missed',
                    [
                        'minutes_since_last_activity' => $minutesSinceLastActivity,
                        'expected_interval' => $this->expectedPingInterval,
                        'last_activity_time' => $lastActivityTime->toISOString(),
                    ],
                    [
                        'severity' => 'medium',
                        'alert_type' => 'ping_timeout',
                    ]
                );

                // If no activity for extended period, trigger uninstall alert
                if ($minutesSinceLastActivity > ($this->expectedPingInterval * 6)) { // 30 minutes default
                    $this->checkForAppUninstall($lastActivityTime);
                }
            } else {
                // Ping is healthy - log successful ping
                DriverActivityLogger::dispatch(
                    $this->driverId,
                    'ping_received',
                    [
                        'minutes_since_last_activity' => $minutesSinceLastActivity,
                        'ping_status' => 'healthy',
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to monitor driver ping for driver {$this->driverId}: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Check if the app might have been uninstalled
     *
     * @param Carbon $lastActivityTime
     * @return void
     */
    private function checkForAppUninstall(Carbon $lastActivityTime)
    {
        $hoursSinceLastActivity = $lastActivityTime->diffInHours(now());

        // If no activity for more than 24 hours, likely app uninstalled
        if ($hoursSinceLastActivity > 24) {
            DriverActivityLogger::dispatch(
                $this->driverId,
                'app_uninstalled',
                [
                    'hours_since_last_activity' => $hoursSinceLastActivity,
                    'last_activity_time' => $lastActivityTime->toISOString(),
                    'uninstall_detected_at' => now()->toISOString(),
                ],
                [
                    'severity' => 'critical',
                    'alert_type' => 'app_uninstall',
                    'requires_immediate_action' => true,
                ]
            );

            Log::warning("App uninstall detected for driver {$this->driverId} - no activity for {$hoursSinceLastActivity} hours");
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
        Log::error("Driver ping monitor job failed for driver {$this->driverId}", [
            'exception' => $exception->getMessage(),
        ]);
    }
}
