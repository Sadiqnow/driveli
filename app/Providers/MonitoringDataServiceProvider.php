<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\DriverLocationTracking;
use App\Models\TraceAlert;
use App\Models\ActivityLog;

class MonitoringDataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register monitoring data services
        $this->app->singleton('monitoring.cache', function ($app) {
            return new class {
                public function getDashboardStats()
                {
                    return Cache::remember('monitoring_dashboard_stats', 300, function () {
                        return [
                            'active_drivers' => DriverLocationTracking::where('recorded_at', '>=', now()->subMinutes(30))
                                ->distinct('driver_id')
                                ->count('driver_id'),
                            'locations_recorded' => DriverLocationTracking::where('recorded_at', '>=', now()->subDay())
                                ->count(),
                            'suspicious_activities' => TraceAlert::where('severity', 'high')
                                ->where('created_at', '>=', now()->subDay())
                                ->count(),
                            'active_alerts' => TraceAlert::where('resolved_at', null)
                                ->count(),
                        ];
                    });
                }

                public function getDriverMonitoringData($driverId)
                {
                    $cacheKey = "driver_monitoring_data:{$driverId}";
                    return Cache::remember($cacheKey, 60, function () use ($driverId) {
                        return [
                            'last_location' => DriverLocationTracking::where('driver_id', $driverId)
                                ->latest('recorded_at')
                                ->first(),
                            'location_count_24h' => DriverLocationTracking::where('driver_id', $driverId)
                                ->where('recorded_at', '>=', now()->subDay())
                                ->count(),
                            'activity_logs' => ActivityLog::where('user_id', $driverId)
                                ->where('user_type', 'driver')
                                ->latest()
                                ->take(10)
                                ->get(),
                            'active_alerts' => TraceAlert::where('driver_id', $driverId)
                                ->where('resolved_at', null)
                                ->get(),
                        ];
                    });
                }

                public function invalidateDriverCache($driverId)
                {
                    Cache::forget("driver_monitoring_data:{$driverId}");
                    Cache::forget('monitoring_dashboard_stats');
                }

                public function getActivityLogs($driverId, $limit = 50)
                {
                    $cacheKey = "driver_activity_logs:{$driverId}:{$limit}";
                    return Cache::remember($cacheKey, 300, function () use ($driverId, $limit) {
                        return ActivityLog::where('user_id', $driverId)
                            ->where('user_type', 'driver')
                            ->latest()
                            ->take($limit)
                            ->get();
                    });
                }
            };
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Set up database query optimizations for monitoring
        DB::whenQueryingForLongerThan(500, function () {
            logger()->warning('Slow monitoring query detected');
        });

        // Only preload data if migrations are run
        if (\Schema::hasTable('driver_locations') && \Schema::hasTable('trace_alerts')) {
            $this->preloadMonitoringData();
        }
    }

    /**
     * Preload frequently accessed monitoring data
     */
    protected function preloadMonitoringData()
    {
        // Cache dashboard statistics
        if (!Cache::has('monitoring_dashboard_stats')) {
            Cache::remember('monitoring_dashboard_stats', 300, function () {
                return [
                    'active_drivers' => DriverLocationTracking::where('recorded_at', '>=', now()->subMinutes(30))
                        ->distinct('driver_id')
                        ->count('driver_id'),
                    'locations_recorded' => DriverLocationTracking::where('recorded_at', '>=', now()->subDay())
                        ->count(),
                    'suspicious_activities' => TraceAlert::where('severity', 'high')
                        ->where('created_at', '>=', now()->subDay())
                        ->count(),
                    'active_alerts' => TraceAlert::where('resolved_at', null)
                        ->count(),
                ];
            });
        }

        // Cache active alerts
        if (!Cache::has('active_trace_alerts')) {
            Cache::remember('active_trace_alerts', 300, function () {
                return TraceAlert::with('driver')
                    ->where('resolved_at', null)
                    ->orderBy('severity', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
            });
        }
    }
}
