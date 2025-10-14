<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Jobs\ProcessLocationUpdate;
use App\Models\DriverLocationTracking;
use App\Models\Drivers;
use App\Services\LocationMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Driver Location API Controller
 *
 * Handles location tracking and monitoring for drivers
 * Provides endpoints for location updates and monitoring
 *
 * @package App\Http\Controllers\Api
 */
class DriverLocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationMonitoringService $locationService)
    {
        $this->locationService = $locationService;
        $this->middleware('auth:api');
    }

    /**
     * Store a new location update for the authenticated driver
     *
     * @param StoreLocationRequest $request
     * @return JsonResponse
     */
    public function store(StoreLocationRequest $request): JsonResponse
    {
        try {
            $driver = Auth::user();

            // Verify driver is active
            if (!$driver->is_current) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver account is not active',
                ], 403);
            }

            $locationData = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'device_info' => $request->device_info,
                'metadata' => $request->metadata,
                'recorded_at' => $request->recorded_at,
            ];

            // Dispatch job to process location update asynchronously
            ProcessLocationUpdate::dispatch($locationData, $driver->id);

            Log::info("Location update queued for driver {$driver->id}", [
                'coordinates' => [
                    'lat' => $request->latitude,
                    'lng' => $request->longitude,
                ],
                'accuracy' => $request->accuracy,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Location update queued successfully',
                    'data' => [
                        'driver_id' => $driver->id,
                        'queued_at' => now()->toISOString(),
                        'coordinates' => [
                            'latitude' => $request->latitude,
                            'longitude' => $request->longitude,
                        ],
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Location update queued successfully');

        } catch (\Exception $e) {
            Log::error("Failed to queue location update: {$e->getMessage()}", [
                'driver_id' => Auth::id(),
                'request_data' => $request->all(),
                'exception' => $e,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process location update',
                    'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to process location update: ' . (config('app.debug') ? $e->getMessage() : 'Internal server error'));
        }
    }

    /**
     * Get driver's recent location history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $driver = Auth::user();
            $limit = $request->input('limit', 50);
            $hours = $request->input('hours', 24); // Default to last 24 hours

            $locations = DriverLocationTracking::where('driver_id', $driver->id)
                ->where('recorded_at', '>=', now()->subHours($hours))
                ->orderBy('recorded_at', 'desc')
                ->limit($limit)
                ->get(['id', 'latitude', 'longitude', 'accuracy', 'recorded_at', 'device_info']);

            return response()->json([
                'success' => true,
                'message' => 'Location history retrieved successfully',
                'data' => [
                    'driver_id' => $driver->id,
                    'locations' => $locations,
                    'count' => $locations->count(),
                    'time_range' => [
                        'hours' => $hours,
                        'from' => now()->subHours($hours)->toISOString(),
                        'to' => now()->toISOString(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to retrieve location history: {$e->getMessage()}", [
                'driver_id' => Auth::id(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve location history',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get driver's current status and monitoring information
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        try {
            $driver = Auth::user();

            // Get latest location
            $latestLocation = DriverLocationTracking::where('driver_id', $driver->id)
                ->orderBy('recorded_at', 'desc')
                ->first(['latitude', 'longitude', 'accuracy', 'recorded_at', 'device_info']);

            // Check for active OTP challenges
            $activeOtp = $driver->otpNotifications()
                ->where('type', 'location_challenge')
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->first(['id', 'otp_code', 'expires_at', 'attempts']);

            // Get monitoring status
            $isBeingMonitored = $this->locationService->isDriverBeingMonitored($driver->id);

            return response()->json([
                'success' => true,
                'message' => 'Driver status retrieved successfully',
                'data' => [
                    'driver_id' => $driver->id,
                    'is_active' => $driver->is_current,
                    'is_being_monitored' => $isBeingMonitored,
                    'latest_location' => $latestLocation ? [
                        'latitude' => $latestLocation->latitude,
                        'longitude' => $latestLocation->longitude,
                        'accuracy' => $latestLocation->accuracy,
                        'recorded_at' => $latestLocation->recorded_at,
                        'device_info' => $latestLocation->device_info,
                    ] : null,
                    'active_challenge' => $activeOtp ? [
                        'id' => $activeOtp->id,
                        'expires_at' => $activeOtp->expires_at,
                        'attempts_remaining' => 3 - $activeOtp->attempts, // Assuming max 3 attempts
                    ] : null,
                    'last_updated' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to retrieve driver status: {$e->getMessage()}", [
                'driver_id' => Auth::id(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve driver status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Verify OTP challenge for suspicious activity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyChallenge(Request $request): JsonResponse
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        try {
            $driver = Auth::user();

            $otpNotification = $driver->otpNotifications()
                ->where('type', 'location_challenge')
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->first();

            if (!$otpNotification) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active OTP challenge found',
                ], 404);
            }

            if ($otpNotification->otp_code !== $request->otp_code) {
                $otpNotification->increment('attempts');

                // Check if max attempts reached
                if ($otpNotification->attempts >= 3) {
                    $otpNotification->update(['status' => 'failed']);

                    // Log failed verification
                    \App\Models\ActivityLog::create([
                        'user_type' => 'driver',
                        'user_id' => $driver->id,
                        'action' => 'otp_verification_failed',
                        'description' => 'OTP verification failed after maximum attempts',
                        'metadata' => [
                            'otp_id' => $otpNotification->id,
                            'attempts' => $otpNotification->attempts,
                        ],
                        'ip_address' => $request->ip(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'OTP verification failed. Maximum attempts exceeded.',
                    ], 429);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP code',
                    'data' => [
                        'attempts_remaining' => 3 - $otpNotification->attempts,
                    ],
                ], 400);
            }

            // OTP verified successfully
            $otpNotification->update([
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            // Log successful verification
            \App\Models\ActivityLog::create([
                'user_type' => 'driver',
                'user_id' => $driver->id,
                'action' => 'otp_verification_success',
                'description' => 'OTP challenge verified successfully',
                'metadata' => [
                    'otp_id' => $otpNotification->id,
                    'verification_time' => now(),
                ],
                'ip_address' => $request->ip(),
            ]);

            Log::info("OTP challenge verified for driver {$driver->id}", [
                'otp_id' => $otpNotification->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP challenge verified successfully',
                'data' => [
                    'verified_at' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to verify OTP challenge: {$e->getMessage()}", [
                'driver_id' => Auth::id(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify OTP challenge',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
