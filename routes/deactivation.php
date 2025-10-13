<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DeactivationController;
use App\Http\Controllers\Admin\LocationMonitoringController;
use App\Http\Controllers\Api\DriverLocationController;

/*
|--------------------------------------------------------------------------
| Deactivation Management Routes
|--------------------------------------------------------------------------
|
| Routes for managing driver and company deactivation requests.
| These routes handle the complete deactivation & approval flow.
|
*/

// ===================================================================================================
// DEACTIVATION MANAGEMENT ROUTES (Admin Only)
// ===================================================================================================

Route::prefix('admin/deactivation')->name('admin.deactivation.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [DeactivationController::class, 'index'])->name('index');
    Route::get('/create', [DeactivationController::class, 'create'])->name('create');
    Route::post('/', [DeactivationController::class, 'store'])->name('store');
    Route::get('/{deactivationRequest}', [DeactivationController::class, 'show'])->name('show');

    // Admin actions
    Route::post('/{deactivationRequest}/review', [DeactivationController::class, 'review'])->name('review');
    Route::post('/{deactivationRequest}/approve', [DeactivationController::class, 'approve'])->name('approve');
    Route::post('/{deactivationRequest}/reject', [DeactivationController::class, 'reject'])->name('reject');

    // OTP verification
    Route::get('/otp/{otp}', [DeactivationController::class, 'showOTP'])->name('otp');
    Route::post('/otp/{otp}/verify', [DeactivationController::class, 'verifyOTP'])->name('verify-otp');
    Route::post('/otp/{otp}/resend', [DeactivationController::class, 'resendOTP'])->name('resend-otp');

    // API endpoints for AJAX calls
    Route::post('/send-challenge', [DeactivationController::class, 'sendChallenge'])->name('send-challenge');
    Route::get('/stats', [DeactivationController::class, 'getStats'])->name('stats');
});

// ===================================================================================================
// LOCATION MONITORING ROUTES (Admin-II Only)
// ===================================================================================================

Route::prefix('admin/monitoring')->name('admin.monitoring.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [LocationMonitoringController::class, 'index'])->name('index');
    Route::get('/driver/{driverId}', [LocationMonitoringController::class, 'monitorDriver'])->name('driver');

    // API endpoints for real-time monitoring
    Route::get('/driver/{driverId}/locations', [LocationMonitoringController::class, 'getDriverLocations'])->name('driver-locations');
    Route::post('/driver/{driverId}/challenge', [LocationMonitoringController::class, 'sendChallenge'])->name('send-challenge');
    Route::get('/locations/bounds', [LocationMonitoringController::class, 'getLocationsInBounds'])->name('locations-bounds');
    Route::get('/driver/{driverId}/suspicious', [LocationMonitoringController::class, 'checkSuspiciousActivity'])->name('check-suspicious');
});

// ===================================================================================================
// DRIVER API ROUTES (For location tracking and monitoring)
// ===================================================================================================

Route::prefix('api/driver')->name('api.driver.')->middleware(['auth:api'])->group(function () {
    // Location tracking endpoints
    Route::post('/location', [DriverLocationController::class, 'store'])->name('location.store');
    Route::get('/location/history', [DriverLocationController::class, 'history'])->name('location.history');
    Route::get('/location/status', [DriverLocationController::class, 'status'])->name('location.status');
    Route::post('/location/verify-challenge', [DriverLocationController::class, 'verifyChallenge'])->name('location.verify-challenge');
});

// ===================================================================================================
// DRIVER SELF-SERVICE ROUTES
// ===================================================================================================

Route::middleware(['auth'])->group(function () {
    Route::post('/driver/deactivation-request', [DeactivationController::class, 'driverRequestDeactivation'])->name('driver.deactivation-request');
});
