<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LocationMonitoringService;
use App\Services\DeactivationService;
use App\Models\DriverNormalized;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LocationMonitoringController extends Controller
{
    protected $locationService;
    protected $deactivationService;

    public function __construct(
        LocationMonitoringService $locationService,
        DeactivationService $deactivationService
    ) {
        $this->locationService = $locationService;
        $this->deactivationService = $deactivationService;
    }

    /**
     * Display monitoring dashboard
     */
    public function index(Request $request)
    {
        Gate::authorize('monitor-drivers');

        $dashboard = $this->locationService->getMonitoringDashboard();

        return view('admin.monitoring.index', compact('dashboard'));
    }

    /**
     * Monitor specific driver
     */
    public function monitorDriver(Request $request, $driverId)
    {
        Gate::authorize('monitor-drivers');

        $driver = DriverNormalized::findOrFail($driverId);
        $monitoring = $this->locationService->monitorDriver($driverId, auth()->user());

        return view('admin.monitoring.driver', compact('driver', 'monitoring'));
    }

    /**
     * Get real-time location data for driver
     */
    public function getDriverLocations(Request $request, $driverId)
    {
        Gate::authorize('monitor-drivers');

        $minutes = $request->get('minutes', 60);
        $locations = $this->locationService->getDriverLocations($driverId, $minutes);

        return response()->json([
            'success' => true,
            'data' => $locations->map(function ($location) {
                return [
                    'id' => $location->id,
                    'coordinates' => $location->coordinates,
                    'accuracy' => $location->accuracy,
                    'recorded_at' => $location->recorded_at->toISOString(),
                    'device_info' => $location->device_info,
                ];
            }),
        ]);
    }

    /**
     * Send OTP challenge for suspicious activity
     */
    public function sendChallenge(Request $request, $driverId)
    {
        Gate::authorize('monitor-drivers');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $otp = $this->deactivationService->sendOTPChallenge(
                'driver',
                $driverId,
                $request->reason
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP challenge sent to driver',
                    'otp_id' => $otp->id,
                ]);
            }

            return redirect()->back()->with('success', 'OTP challenge sent to driver');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            return redirect()->back()->with('error', 'Failed to send OTP challenge: ' . $e->getMessage());
        }
    }

    /**
     * Get locations within bounds (for map display)
     */
    public function getLocationsInBounds(Request $request)
    {
        Gate::authorize('monitor-drivers');

        $request->validate([
            'lat1' => 'required|numeric',
            'lon1' => 'required|numeric',
            'lat2' => 'required|numeric',
            'lon2' => 'required|numeric',
            'minutes' => 'nullable|integer|min:1|max:1440',
        ]);

        $locations = $this->locationService->getLocationsInBounds(
            $request->lat1,
            $request->lon1,
            $request->lat2,
            $request->lon2,
            $request->minutes ?? 60
        );

        return response()->json([
            'success' => true,
            'data' => $locations->map(function ($location) {
                return [
                    'id' => $location->id,
                    'driver_id' => $location->driver_id,
                    'driver_name' => $location->driver->full_name,
                    'coordinates' => $location->coordinates,
                    'recorded_at' => $location->recorded_at->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * API endpoint for drivers to submit location
     */
    public function submitLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'device_info' => 'nullable|string|max:255',
        ]);

        // In a real app, you'd get driver ID from authentication
        $driverId = auth()->id(); // Assuming driver authentication

        $location = $this->locationService->recordLocation(
            $driverId,
            $request->latitude,
            $request->longitude,
            $request->accuracy,
            $request->device_info,
            $request->metadata ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Location recorded',
            'location_id' => $location->id,
        ]);
    }

    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity(Request $request, $driverId)
    {
        Gate::authorize('monitor-drivers');

        $suspicious = $this->locationService->detectSuspiciousActivity($driverId);

        return response()->json([
            'success' => true,
            'suspicious' => $suspicious,
            'driver_id' => $driverId,
        ]);
    }

    /**
     * Get monitoring dashboard statistics (API)
     */
    public function getStats()
    {
        Gate::authorize('monitor-drivers');

        $dashboard = $this->locationService->getMonitoringDashboard();

        return response()->json([
            'success' => true,
            'data' => [
                'active_drivers' => $dashboard['active_drivers'],
                'total_locations_recorded' => $dashboard['total_locations_recorded'],
                'suspicious_activities' => $dashboard['suspicious_activities'],
                'alerts_count' => count($dashboard['suspicious_activities']),
            ],
        ]);
    }

    /**
     * Get driver monitoring data for AJAX
     */
    public function getDriverData(Request $request, $driverId)
    {
        Gate::authorize('monitor-drivers');

        $driver = DriverNormalized::findOrFail($driverId);

        // Get last location
        $lastLocation = DriverLocationTracking::where('driver_id', $driverId)
            ->orderBy('recorded_at', 'desc')
            ->first();

        // Get last activity from activity log
        $lastActivity = \App\Models\ActivityLog::where('user_type', 'driver')
            ->where('user_id', $driverId)
            ->orderBy('created_at', 'desc')
            ->first();

        // Check if being monitored
        $isBeingMonitored = $this->locationService->isDriverBeingMonitored($driverId);

        return response()->json([
            'success' => true,
            'data' => [
                'driver_id' => $driverId,
                'last_location' => $lastLocation ? [
                    'latitude' => $lastLocation->latitude,
                    'longitude' => $lastLocation->longitude,
                    'accuracy' => $lastLocation->accuracy,
                    'recorded_at' => $lastLocation->recorded_at->toISOString(),
                    'device_info' => $lastLocation->device_info,
                ] : null,
                'last_activity' => $lastActivity ? $lastActivity->created_at->toISOString() : null,
                'device_info' => $lastLocation ? $lastLocation->device_info : null,
                'is_being_monitored' => $isBeingMonitored,
            ],
        ]);
    }

    /**
     * Get driver activity log
     */
    public function getDriverActivity(Request $request, $driverId)
    {
        Gate::authorize('monitor-drivers');

        $limit = $request->get('limit', 20);

        $activities = \App\Models\ActivityLog::where('user_type', 'driver')
            ->where('user_id', $driverId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['action', 'description', 'metadata', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * API endpoint for dashboard data
     */
    public function getDashboardData(Request $request)
    {
        Gate::authorize('monitor-drivers');

        $dashboard = $this->locationService->getMonitoringDashboard();

        return response()->json([
            'success' => true,
            'data' => $dashboard,
        ]);
    }

    /**
     * API endpoint for trace alerts
     */
    public function getTraceAlerts(Request $request)
    {
        Gate::authorize('monitor-drivers');

        $alerts = \App\Models\TraceAlert::with('driver')
            ->active()
            ->orderBy('triggered_at', 'desc')
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'driver_id' => $alert->driver_id,
                    'driver_name' => $alert->driver->full_name ?? 'Unknown',
                    'alert_type' => $alert->alert_type,
                    'alert_type_description' => $alert->alert_type_description,
                    'severity' => $alert->severity,
                    'triggered_at' => $alert->triggered_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    /**
     * Resolve trace alert
     */
    public function resolveAlert(Request $request, $alertId)
    {
        Gate::authorize('monitor-drivers');

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $alert = \App\Models\TraceAlert::findOrFail($alertId);
            $alert->resolve(auth()->id(), $request->notes);

            return response()->json([
                'success' => true,
                'message' => 'Alert resolved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve alert',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
