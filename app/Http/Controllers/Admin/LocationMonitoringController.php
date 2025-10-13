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

            return response()->json([
                'success' => true,
                'message' => 'OTP challenge sent to driver',
                'otp_id' => $otp->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
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
}
