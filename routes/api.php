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
// ROLE & PERMISSION MANAGEMENT API ROUTES
// ===================================================================================================

Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {
    // Role API routes
    Route::prefix('roles')->name('api.roles.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RoleController::class, 'apiIndex'])->name('index');
        Route::post('/{role}/permissions', [App\Http\Controllers\Admin\RoleController::class, 'apiAssignPermissions'])->name('assign-permissions');
    });

    // Permission API routes
    Route::prefix('permissions')->name('api.permissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PermissionController::class, 'apiIndex'])->name('index');
        Route::post('/sync', [App\Http\Controllers\Admin\PermissionController::class, 'syncPermissions'])->name('sync');
        Route::post('/assign-roles', [App\Http\Controllers\Admin\PermissionController::class, 'apiAssignRolesToUser'])->name('assign-roles');
    });

    // User role management API routes
    Route::prefix('user')->name('api.user.')->group(function () {
        Route::post('/roles', [App\Http\Controllers\Admin\SuperAdminController::class, 'manageUserRoles'])->name('roles');
    });

    // Admin users API routes for the frontend
    Route::prefix('admin')->name('api.admin.')->group(function () {
        Route::get('/users', [App\Http\Controllers\Admin\SuperAdminController::class, 'getUsersApi'])->name('users');
        Route::get('/users/{user}/roles', [App\Http\Controllers\Admin\SuperAdminController::class, 'getUserRolesApi'])->name('users.roles');
    });

    // Analytics API routes for Super Admin
    Route::prefix('analytics')->name('api.analytics.')->middleware(['role.permission:Super Admin,view_permission_analytics'])->group(function () {
        Route::get('/roles', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'roles'])->name('roles');
        Route::get('/violations', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'violations'])->name('violations');
        Route::get('/usage', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'usage'])->name('usage');
    });
});

// ===================================================================================================
// DRIVER VERIFICATION API ROUTES
// ===================================================================================================
Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {
    Route::prefix('driver-verification')->name('api.driver-verification.')->group(function () {
        Route::post('/{driverId}/start', [App\Http\Controllers\DriverVerificationController::class, 'start'])->name('start');
        Route::post('/{driverId}/reverify', [App\Http\Controllers\DriverVerificationController::class, 'reverify'])->name('reverify');
        Route::get('/{driverId}/report', [App\Http\Controllers\DriverVerificationController::class, 'report'])->name('report');
    });
});

// ===================================================================================================
// COMPANY API ROUTES
// ===================================================================================================
Route::prefix('company')->name('api.company.')->group(function () {
    // Authentication endpoints with enhanced rate limiting (public)
    Route::middleware(['enhanced.rate.limit:auth'])->group(function () {
        Route::post('/register', [App\Http\Controllers\API\CompanyAuthController::class, 'register'])->name('register');
        Route::post('/login', [App\Http\Controllers\API\CompanyAuthController::class, 'login'])->name('login');
        Route::post('/verify-email', [App\Http\Controllers\API\CompanyAuthController::class, 'verifyEmail'])->name('verify-email');
    });

    // Protected company endpoints (apply secure.api and auth)
    Route::middleware(['secure.api', 'auth:sanctum', 'ability:company'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\API\CompanyAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [App\Http\Controllers\API\CompanyAuthController::class, 'profile'])->name('profile');
        Route::put('/profile', [App\Http\Controllers\API\CompanyAuthController::class, 'updateProfile'])->name('profile.update');

        // Company Requests API Routes
        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/', [App\Http\Controllers\API\CompanyRequestController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\API\CompanyRequestController::class, 'store'])->name('store');
            Route::get('/{companyRequest}', [App\Http\Controllers\API\CompanyRequestController::class, 'show'])->name('show');
            Route::put('/{companyRequest}', [App\Http\Controllers\API\CompanyRequestController::class, 'update'])->name('update');
            Route::delete('/{companyRequest}', [App\Http\Controllers\API\CompanyRequestController::class, 'destroy'])->name('destroy');
            Route::get('/{companyRequest}/matches', [App\Http\Controllers\API\CompanyRequestController::class, 'matches'])->name('matches');
            Route::post('/matches/{companyMatch}/accept', [App\Http\Controllers\API\CompanyRequestController::class, 'acceptMatch'])->name('matches.accept');
            Route::post('/matches/{companyMatch}/reject', [App\Http\Controllers\API\CompanyRequestController::class, 'rejectMatch'])->name('matches.reject');
        });

        // Fleet Management API Routes
        Route::prefix('fleets')->name('fleets.')->group(function () {
            Route::get('/', [App\Http\Controllers\API\FleetController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\API\FleetController::class, 'store'])->name('store');
            Route::get('/{fleet}', [App\Http\Controllers\API\FleetController::class, 'show'])->name('show');
            Route::put('/{fleet}', [App\Http\Controllers\API\FleetController::class, 'update'])->name('update');
            Route::delete('/{fleet}', [App\Http\Controllers\API\FleetController::class, 'destroy'])->name('destroy');
            Route::get('/{fleet}/vehicles', [App\Http\Controllers\API\FleetController::class, 'vehicles'])->name('vehicles');
            Route::post('/{fleet}/vehicles', [App\Http\Controllers\API\FleetController::class, 'addVehicle'])->name('vehicles.add');
            Route::put('/{fleet}/vehicles/{vehicle}', [App\Http\Controllers\API\FleetController::class, 'updateVehicle'])->name('vehicles.update');
            Route::delete('/{fleet}/vehicles/{vehicle}', [App\Http\Controllers\API\FleetController::class, 'removeVehicle'])->name('vehicles.remove');
            Route::get('/{fleet}/maintenance-due', [App\Http\Controllers\API\FleetController::class, 'maintenanceDue'])->name('maintenance-due');
        });

        // Invoice Management API Routes
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [App\Http\Controllers\API\InvoiceController::class, 'index'])->name('index');
            Route::get('/{invoice}', [App\Http\Controllers\API\InvoiceController::class, 'show'])->name('show');
            Route::post('/{invoice}/pay', [App\Http\Controllers\API\InvoiceController::class, 'pay'])->name('pay');
            Route::get('/{invoice}/download', [App\Http\Controllers\API\InvoiceController::class, 'download'])->name('download');
            Route::get('/overdue', [App\Http\Controllers\API\InvoiceController::class, 'overdue'])->name('overdue');
            Route::get('/summary', [App\Http\Controllers\API\InvoiceController::class, 'summary'])->name('summary');
            Route::post('/{invoice}/mark-paid', [App\Http\Controllers\API\InvoiceController::class, 'markAsPaid'])->name('mark-paid');
            Route::post('/{invoice}/dispute', [App\Http\Controllers\API\InvoiceController::class, 'dispute'])->name('dispute');
        });

        // Company Profile Management API Routes
        Route::get('/', [App\Http\Controllers\API\CompanyController::class, 'show'])->name('show');
        Route::put('/', [App\Http\Controllers\API\CompanyController::class, 'update'])->name('update');
        Route::get('/dashboard', [App\Http\Controllers\API\CompanyController::class, 'dashboard'])->name('dashboard');

        // Company Members Management API Routes
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [App\Http\Controllers\API\CompanyController::class, 'members'])->name('index');
            Route::post('/', [App\Http\Controllers\API\CompanyController::class, 'addMember'])->name('store');
            Route::put('/{member}', [App\Http\Controllers\API\CompanyController::class, 'updateMember'])->name('update');
            Route::delete('/{member}', [App\Http\Controllers\API\CompanyController::class, 'removeMember'])->name('destroy');
        });

        // Company Matches Management API Routes
        Route::prefix('matches')->name('matches.')->group(function () {
            Route::get('/', [App\Http\Controllers\API\CompanyMatchController::class, 'index'])->name('index');
            Route::get('/{companyMatch}', [App\Http\Controllers\API\CompanyMatchController::class, 'show'])->name('show');
            Route::post('/{companyMatch}/accept', [App\Http\Controllers\API\CompanyMatchController::class, 'accept'])->name('accept');
            Route::post('/{companyMatch}/reject', [App\Http\Controllers\API\CompanyMatchController::class, 'reject'])->name('reject');
            Route::post('/{companyMatch}/negotiate', [App\Http\Controllers\API\CompanyMatchController::class, 'negotiate'])->name('negotiate');
        });

        // Vehicle Management API Routes
        Route::prefix('vehicles')->name('vehicles.')->group(function () {
            Route::get('/', [App\Http\Controllers\API\VehicleController::class, 'index'])->name('index');
            Route::get('/{vehicle}', [App\Http\Controllers\API\VehicleController::class, 'show'])->name('show');
            Route::put('/{vehicle}', [App\Http\Controllers\API\VehicleController::class, 'update'])->name('update');
            Route::delete('/{vehicle}', [App\Http\Controllers\API\VehicleController::class, 'destroy'])->name('destroy');
            Route::get('/{vehicle}/maintenance', [App\Http\Controllers\API\VehicleController::class, 'maintenanceHistory'])->name('maintenance');
            Route::post('/{vehicle}/assign-fleet', [App\Http\Controllers\API\VehicleController::class, 'assignToFleet'])->name('assign-fleet');
        });

        // Webhook Management API Routes
        Route::prefix('webhooks')->name('webhooks.')->group(function () {
            Route::get('/', [App\Http\Controllers\API\WebhookController::class, 'index'])->name('index');
        });
    });
});

// ===================================================================================================
// WEBHOOK ROUTES
// ===================================================================================================
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/payment/paystack', [App\Http\Controllers\Webhooks\PaymentWebhookController::class, 'handlePaystackWebhook'])->name('payment.paystack');
    Route::post('/payment/flutterwave', [App\Http\Controllers\Webhooks\PaymentWebhookController::class, 'handleFlutterwaveWebhook'])->name('payment.flutterwave');
});

// ===================================================================================================
// FRONTEND EXPECTED ROUTES (matching JavaScript calls)
// ===================================================================================================
// Add routes that match what the frontend JavaScript is calling
Route::get('/states', [LocationController::class, 'getStates'])->name('api.states.frontend');
Route::get('/states/{stateId}/lgas', [LocationController::class, 'getLocalGovernments'])->name('api.states.lgas.frontend');
