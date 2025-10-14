<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\RoleController;

use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\MatchingController;
use App\Http\Controllers\Admin\SuperAdminController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ===================================================================================================
// PUBLIC ROUTES
// ===================================================================================================

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
})->name('home');

// Terms and Privacy Routes
Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// ===================================================================================================
// AUTHENTICATION ROUTES (Admin)
// ===================================================================================================

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'login'])->name('login.submit');
        Route::get('/register', [App\Http\Controllers\Admin\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [App\Http\Controllers\Admin\Auth\RegisterController::class, 'register'])->name('register.submit');
        Route::get('/forgot-password', [App\Http\Controllers\Admin\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('forgot-password');
        Route::post('/forgot-password', [App\Http\Controllers\Admin\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forgot-password.submit');
        Route::get('/reset-password/{token}', [App\Http\Controllers\Admin\Auth\ResetPasswordController::class, 'showResetForm'])->name('reset-password');
        Route::post('/reset-password', [App\Http\Controllers\Admin\Auth\ResetPasswordController::class, 'reset'])->name('reset-password.submit');
    });

    // Protected admin routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\Admin\Auth\LoginController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });

        // Driver Management
        Route::prefix('drivers')->name('drivers.')->group(function () {
            Route::get('/', [DriverController::class, 'index'])->name('index');
            Route::get('/create', [DriverController::class, 'create'])->name('create');
            Route::post('/', [DriverController::class, 'store'])->name('store');
            Route::get('/{driver}', [DriverController::class, 'show'])->name('show');
            Route::get('/{driver}/edit', [DriverController::class, 'edit'])->name('edit');
            Route::put('/{driver}', [DriverController::class, 'update'])->name('update');
            Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('destroy');

            // Bulk operations
            Route::post('/bulk-operations', [DriverController::class, 'bulkOperations'])->name('bulk-operations');

            // KYC Management
            Route::prefix('kyc')->name('kyc.')->group(function () {
                Route::get('/', [DriverController::class, 'kycIndex'])->name('index');
                Route::get('/{driver}/review', [DriverController::class, 'kycReview'])->name('review');
                Route::post('/{driver}/approve', [DriverController::class, 'kycApprove'])->name('approve');
                Route::post('/{driver}/reject', [DriverController::class, 'kycReject'])->name('reject');
                Route::get('/dashboard', [DriverController::class, 'kycDashboard'])->name('dashboard');
            });

            // Verification
            Route::prefix('verification')->name('verification.')->group(function () {
                Route::get('/', [VerificationController::class, 'index'])->name('index');
                Route::get('/{driver}/verify', [VerificationController::class, 'verify'])->name('verify');
                Route::post('/{driver}/verify', [VerificationController::class, 'processVerification'])->name('process');
            });
        });

        // Company Management
        Route::prefix('companies')->name('companies.')->group(function () {
            Route::get('/', [CompanyController::class, 'index'])->name('index');
            Route::get('/create', [CompanyController::class, 'create'])->name('create');
            Route::post('/', [CompanyController::class, 'store'])->name('store');
            Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
            Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
            Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
            Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
            Route::get('/pending', [CompanyController::class, 'pending'])->name('pending');
            Route::post('/{company}/verify', [CompanyController::class, 'verify'])->name('verify');
        });

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        });

        // Role Management
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });



        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminReportController::class, 'index'])->name('index');
            Route::get('/driver-performance', [AdminReportController::class, 'driverPerformance'])->name('driver-performance');
            Route::get('/company-activity', [AdminReportController::class, 'companyActivity'])->name('company-activity');
            Route::get('/financial', [AdminReportController::class, 'financial'])->name('financial');
            Route::get('/dashboard', [AdminReportController::class, 'dashboard'])->name('dashboard');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [AdminNotificationController::class, 'index'])->name('index');
            Route::get('/compose', [AdminNotificationController::class, 'compose'])->name('compose');
            Route::post('/', [AdminNotificationController::class, 'send'])->name('send');
            Route::get('/{notification}', [AdminNotificationController::class, 'show'])->name('show');
        });

        // Matching System
        Route::prefix('matching')->name('matching.')->group(function () {
            Route::get('/', [MatchingController::class, 'index'])->name('index');
            Route::get('/matches', [MatchingController::class, 'matches'])->name('matches');
            Route::get('/dashboard', [MatchingController::class, 'dashboard'])->name('dashboard');
        });

        // Super Admin Routes
        Route::prefix('superadmin')->name('superadmin.')->middleware('can:manage-superadmin')->group(function () {
            Route::get('/', [SuperAdminController::class, 'index'])->name('index');
            Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
            Route::get('/audit-logs', [SuperAdminController::class, 'auditLogs'])->name('audit-logs');
            Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
        });

        // Feedback Analytics Routes
        Route::prefix('feedback-analytics')->name('feedback-analytics.')->group(function () {
            Route::get('/', [App\Http\Controllers\FeedbackAnalyticsController::class, 'index'])->name('index');
            Route::get('/trends', [App\Http\Controllers\FeedbackAnalyticsController::class, 'trends'])->name('trends');
            Route::get('/flagged-drivers', [App\Http\Controllers\FeedbackAnalyticsController::class, 'flaggedDrivers'])->name('flagged-drivers');
        });

        // Admin Profile Routes
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [AdminUserController::class, 'profile'])->name('edit');
            Route::put('/', [AdminUserController::class, 'updateProfile'])->name('update');
        });

        // Location Monitoring Routes
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'index'])->name('index');
            Route::get('/driver/{driverId}', [App\Http\Controllers\Admin\LocationMonitoringController::class, 'monitorDriver'])->name('driver');
        });

        // Log Dashboard Routes
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\LogDashboardController::class, 'index'])->name('index');
            Route::get('/stats', [App\Http\Controllers\Admin\LogDashboardController::class, 'getStats'])->name('stats');
            Route::get('/export', [App\Http\Controllers\Admin\LogDashboardController::class, 'export'])->name('export');
        });

    });
});



// ===================================================================================================
// DEACTIVATION AND MONITORING ROUTES
// ===================================================================================================

require __DIR__.'/deactivation.php';

// ===================================================================================================
// DRIVER SELF-SERVICE ROUTES
// ===================================================================================================

Route::middleware(['auth:driver'])->group(function () {
    Route::post('/driver/deactivation-request', [App\Http\Controllers\Admin\DeactivationController::class, 'driverRequestDeactivation'])->name('driver.deactivation-request');
});

// ===================================================================================================
// DRIVER REGISTRATION STEPS
// ===================================================================================================

Route::prefix('driver')->name('driver.')->group(function () {
    // Registration Steps
    Route::middleware('guest')->group(function () {
        Route::post('/register/step1', [App\Http\Controllers\Driver\DriverAuthController::class, 'registerStep1'])->name('register.step1');
        Route::get('/register/step2', [App\Http\Controllers\Driver\DriverAuthController::class, 'showRegisterStep2'])->name('register.step2');
        Route::post('/register/step2', [App\Http\Controllers\Driver\DriverAuthController::class, 'registerStep2'])->name('register.step2.submit');
        Route::get('/register/step3', [App\Http\Controllers\Driver\DriverAuthController::class, 'showRegisterStep3'])->name('register.step3');
        Route::post('/register/step3', [App\Http\Controllers\Driver\DriverAuthController::class, 'registerStep3'])->name('register.step3.submit');
        Route::get('/register/step4', [App\Http\Controllers\Driver\DriverAuthController::class, 'showRegisterStep4'])->name('register.step4');
        Route::post('/register/step4', [App\Http\Controllers\Driver\DriverAuthController::class, 'registerStep4'])->name('register.step4.submit');
        Route::get('/password/request', [App\Http\Controllers\Driver\DriverAuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/password/email', [App\Http\Controllers\Driver\DriverAuthController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/password/reset/{token}', [App\Http\Controllers\Driver\DriverAuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/password/reset', [App\Http\Controllers\Driver\DriverAuthController::class, 'resetPassword'])->name('password.update');
    });

    // Document Routes
    Route::middleware(['auth:driver'])->group(function () {
        Route::get('/documents', [App\Http\Controllers\Driver\DriverDocumentController::class, 'index'])->name('documents');
        Route::get('/documents/upload', [App\Http\Controllers\Driver\DriverDocumentController::class, 'uploadForm'])->name('documents.upload');
        Route::post('/documents/upload', [App\Http\Controllers\Driver\DriverDocumentController::class, 'uploadDocument'])->name('documents.upload.submit');
    });

    // KYC Routes
    Route::middleware(['auth:driver'])->group(function () {
        Route::prefix('kyc')->name('kyc.')->group(function () {
            Route::get('/', [App\Http\Controllers\Driver\DriverKycController::class, 'index'])->name('index');
            Route::get('/step1', [App\Http\Controllers\Driver\DriverKycController::class, 'showStep1'])->name('step1');
            Route::post('/step1', [App\Http\Controllers\Driver\DriverKycController::class, 'postStep1'])->name('step1.submit');
            Route::get('/step2', [App\Http\Controllers\Driver\DriverKycController::class, 'showStep2'])->name('step2');
            Route::post('/step2', [App\Http\Controllers\Driver\DriverKycController::class, 'postStep2'])->name('step2.submit');
            Route::get('/step3', [App\Http\Controllers\Driver\DriverKycController::class, 'showStep3'])->name('step3');
            Route::post('/step3', [App\Http\Controllers\Driver\DriverKycController::class, 'postStep3'])->name('step3.submit');
            Route::get('/lgas/{stateId}', [App\Http\Controllers\Driver\DriverKycController::class, 'getLocalGovernments'])->name('lgas');
        });
    });
});

// ===================================================================================================
// REQUEST MANAGEMENT ROUTES
// ===================================================================================================

Route::prefix('admin/requests')->name('admin.requests.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminRequestController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admin\AdminRequestController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\AdminRequestController::class, 'store'])->name('store');
    Route::get('/{request}', [App\Http\Controllers\Admin\AdminRequestController::class, 'show'])->name('show');
    Route::get('/{request}/edit', [App\Http\Controllers\Admin\AdminRequestController::class, 'edit'])->name('edit');
    Route::put('/{request}', [App\Http\Controllers\Admin\AdminRequestController::class, 'update'])->name('update');
    Route::delete('/{request}', [App\Http\Controllers\Admin\AdminRequestController::class, 'destroy'])->name('destroy');
    Route::get('/matches/{request}', [App\Http\Controllers\Admin\AdminRequestController::class, 'matches'])->name('matches');
    Route::post('/{request}/accept/{driver}', [App\Http\Controllers\Admin\AdminRequestController::class, 'accept'])->name('accept');
    Route::get('/queue', [App\Http\Controllers\Admin\AdminRequestController::class, 'queue'])->name('queue');
});

// ===================================================================================================
// EMPLOYMENT FEEDBACK ROUTES
// ===================================================================================================

Route::prefix('admin/employment-feedback')->name('admin.employment-feedback.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\EmploymentFeedbackController::class, 'index'])->name('index');
    Route::post('/request', [App\Http\Controllers\EmploymentFeedbackController::class, 'requestFeedback'])->name('request');
    Route::post('/bulk-request', [App\Http\Controllers\EmploymentFeedbackController::class, 'bulkRequestFeedback'])->name('bulk-request');
    Route::get('/stats', [App\Http\Controllers\EmploymentFeedbackController::class, 'getStats'])->name('stats');
    Route::get('/flagged-drivers', [App\Http\Controllers\EmploymentFeedbackController::class, 'getFlaggedDrivers'])->name('flagged-drivers');
});

// ===================================================================================================
// VERIFICATION ROUTES
// ===================================================================================================

Route::prefix('admin/verification')->name('admin.verification.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\VerificationController::class, 'dashboard'])->name('dashboard');
    Route::get('/driver/{driver}', [App\Http\Controllers\Admin\VerificationController::class, 'driverDetails'])->name('driver-details');
    Route::get('/report', [App\Http\Controllers\Admin\VerificationController::class, 'report'])->name('report');
});

// ===================================================================================================
// AUTHENTICATION ROUTES (Driver)
// ===================================================================================================

Route::prefix('driver')->name('driver.')->group(function () {
    Route::middleware('guest:driver')->group(function () {
        Route::get('/login', [App\Http\Controllers\Driver\DriverAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Driver\DriverAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [App\Http\Controllers\Driver\DriverAuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [App\Http\Controllers\Driver\DriverAuthController::class, 'register'])->name('register.submit');
        Route::get('/forgot-password', [App\Http\Controllers\Driver\DriverAuthController::class, 'showLinkRequestForm'])->name('forgot-password');
        Route::post('/forgot-password', [App\Http\Controllers\Driver\DriverAuthController::class, 'sendResetLinkEmail'])->name('forgot-password.submit');
        Route::get('/reset-password/{token}', [App\Http\Controllers\Driver\DriverAuthController::class, 'showResetForm'])->name('reset-password');
        Route::post('/reset-password', [App\Http\Controllers\Driver\DriverAuthController::class, 'reset'])->name('reset-password.submit');
    });

    Route::middleware(['auth:driver'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\Driver\DriverAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [App\Http\Controllers\Driver\DriverDashboardController::class, 'index'])->name('dashboard');

        // Driver Profile Routes
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [App\Http\Controllers\Driver\DriverProfileController::class, 'show'])->name('show');
            Route::get('/edit', [App\Http\Controllers\Driver\DriverProfileController::class, 'edit'])->name('edit');
            Route::put('/', [App\Http\Controllers\Driver\DriverProfileController::class, 'update'])->name('update');
            Route::post('/avatar', [App\Http\Controllers\Driver\DriverProfileController::class, 'updateAvatar'])->name('avatar');
            Route::get('/documents', [App\Http\Controllers\Driver\DriverProfileController::class, 'showDocuments'])->name('documents');
            Route::post('/documents', [App\Http\Controllers\Driver\DriverProfileController::class, 'uploadDocument'])->name('documents.upload');
            Route::get('/change-password', [App\Http\Controllers\Driver\DriverProfileController::class, 'showChangePassword'])->name('change-password');
            Route::post('/change-password', [App\Http\Controllers\Driver\DriverProfileController::class, 'changePassword'])->name('change-password.submit');
            Route::post('/update-password', [App\Http\Controllers\Driver\DriverProfileController::class, 'changePassword'])->name('update-password');
            Route::delete('/', [App\Http\Controllers\Driver\DriverProfileController::class, 'destroy'])->name('destroy');
        });

        Route::get('/show', [App\Http\Controllers\Driver\DriverAuthController::class, 'show'])->name('show');
        Route::post('/resend-otp', [App\Http\Controllers\Driver\DriverAuthController::class, 'resendOtp'])->name('resend-otp');

        // Dashboard Routes
        Route::get('/dashboard', [App\Http\Controllers\Driver\DriverDashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/availability', [App\Http\Controllers\Driver\DriverDashboardController::class, 'updateAvailability'])->name('dashboard.availability');
        Route::get('/dashboard/notifications', [App\Http\Controllers\Driver\DriverDashboardController::class, 'getNotifications'])->name('dashboard.notifications');
        Route::get('/dashboard/stats', [App\Http\Controllers\Driver\DriverDashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/dashboard/activity', [App\Http\Controllers\Driver\DriverDashboardController::class, 'getActivity'])->name('dashboard.activity');

        // Job Routes
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [App\Http\Controllers\Driver\DriverJobController::class, 'index'])->name('index');
            Route::get('/available', [App\Http\Controllers\Driver\DriverJobController::class, 'availableJobs'])->name('available');
            Route::get('/history', [App\Http\Controllers\Driver\DriverJobController::class, 'history'])->name('history');
            Route::get('/{match}', [App\Http\Controllers\Driver\DriverJobController::class, 'show'])->name('show');
            Route::post('/{match}/accept', [App\Http\Controllers\Driver\DriverJobController::class, 'accept'])->name('accept');
            Route::post('/{match}/decline', [App\Http\Controllers\Driver\DriverJobController::class, 'decline'])->name('decline');
            Route::post('/{match}/complete', [App\Http\Controllers\Driver\DriverJobController::class, 'markComplete'])->name('complete');
            Route::post('/alerts', [App\Http\Controllers\Driver\DriverJobController::class, 'updateJobAlerts'])->name('alerts');
        });

        // Document Routes
        Route::get('/documents', [App\Http\Controllers\Driver\DriverDocumentController::class, 'index'])->name('documents');
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [App\Http\Controllers\Driver\DriverDocumentController::class, 'index'])->name('index');
            Route::post('/upload', [App\Http\Controllers\Driver\DriverDocumentController::class, 'uploadDocument'])->name('upload');
            Route::get('/{document}/download', [App\Http\Controllers\Driver\DriverDocumentController::class, 'downloadDocument'])->name('download');
            Route::delete('/{document}', [App\Http\Controllers\Driver\DriverDocumentController::class, 'deleteDocument'])->name('delete');
        });

    });
});

// ===================================================================================================
// COMPANY ROUTES
// ===================================================================================================

Route::prefix('company')->name('company.')->group(function () {
    Route::middleware('guest:company')->group(function () {
        Route::get('/login', [App\Http\Controllers\Company\CompanyAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Company\CompanyAuthController::class, 'login']);
        Route::get('/register', [App\Http\Controllers\Company\CompanyAuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [App\Http\Controllers\Company\CompanyAuthController::class, 'register']);
        Route::get('/forgot-password', [App\Http\Controllers\Company\CompanyAuthController::class, 'showLinkRequestForm'])->name('forgot-password');
        Route::post('/forgot-password', [App\Http\Controllers\Company\CompanyAuthController::class, 'sendResetLinkEmail']);
        Route::get('/reset-password/{token}', [App\Http\Controllers\Company\CompanyAuthController::class, 'showResetForm'])->name('reset-password');
        Route::post('/reset-password', [App\Http\Controllers\Company\CompanyAuthController::class, 'reset']);
    });

    Route::middleware(['auth:company'])->group(function () {
        Route::post('/logout', [App\Http\Controllers\Company\CompanyAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [App\Http\Controllers\Company\CompanyDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [App\Http\Controllers\Company\CompanyAuthController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Company\CompanyAuthController::class, 'update'])->name('profile.update');
    });
});

// ===================================================================================================
// ADDITIONAL CONTROLLER ROUTES (Unregistered Controllers)
// ===================================================================================================

// Commissions Routes
Route::prefix('commissions')->name('commissions.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\CommissionsController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\CommissionsController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\CommissionsController::class, 'store'])->name('store');
    Route::get('/{commission}', [App\Http\Controllers\CommissionsController::class, 'show'])->name('show');
    Route::get('/{commission}/edit', [App\Http\Controllers\CommissionsController::class, 'edit'])->name('edit');
    Route::put('/{commission}', [App\Http\Controllers\CommissionsController::class, 'update'])->name('update');
    Route::delete('/{commission}', [App\Http\Controllers\CommissionsController::class, 'destroy'])->name('destroy');
});

// Company Routes (Root Level)
Route::prefix('companies')->name('companies.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\CompanyController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\CompanyController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\CompanyController::class, 'store'])->name('store');
    Route::get('/{company}', [App\Http\Controllers\CompanyController::class, 'show'])->name('show');
    Route::get('/{company}/edit', [App\Http\Controllers\CompanyController::class, 'edit'])->name('edit');
    Route::put('/{company}', [App\Http\Controllers\CompanyController::class, 'update'])->name('update');
    Route::delete('/{company}', [App\Http\Controllers\CompanyController::class, 'destroy'])->name('destroy');
});

// Global Driver Routes
Route::prefix('global-drivers')->name('global-drivers.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\GlobalDriverController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\GlobalDriverController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\GlobalDriverController::class, 'store'])->name('store');
    Route::get('/{driver}', [App\Http\Controllers\GlobalDriverController::class, 'show'])->name('show');
    Route::get('/{driver}/edit', [App\Http\Controllers\GlobalDriverController::class, 'edit'])->name('edit');
    Route::put('/{driver}', [App\Http\Controllers\GlobalDriverController::class, 'update'])->name('update');
    Route::delete('/{driver}', [App\Http\Controllers\GlobalDriverController::class, 'destroy'])->name('destroy');
});

// Home Routes
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home.index');

// Admin Analytics Routes
Route::prefix('admin/analytics')->name('admin.analytics.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('index');
    Route::get('/dashboard', [App\Http\Controllers\Admin\AnalyticsController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [App\Http\Controllers\Admin\AnalyticsController::class, 'reports'])->name('reports');
});

// Admin Global Driver Routes
Route::prefix('admin/global-drivers')->name('admin.global-drivers.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\GlobalDriverController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admin\GlobalDriverController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\GlobalDriverController::class, 'store'])->name('store');
    Route::get('/{driver}', [App\Http\Controllers\Admin\GlobalDriverController::class, 'show'])->name('show');
    Route::get('/{driver}/edit', [App\Http\Controllers\Admin\GlobalDriverController::class, 'edit'])->name('edit');
    Route::put('/{driver}', [App\Http\Controllers\Admin\GlobalDriverController::class, 'update'])->name('update');
    Route::delete('/{driver}', [App\Http\Controllers\Admin\GlobalDriverController::class, 'destroy'])->name('destroy');
});

// Admin Optimized Driver Routes
Route::prefix('admin/optimized-drivers')->name('admin.optimized-drivers.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\OptimizedDriverController::class, 'index'])->name('index');
    Route::get('/optimize', [App\Http\Controllers\Admin\OptimizedDriverController::class, 'optimize'])->name('optimize');
    Route::get('/results', [App\Http\Controllers\Admin\OptimizedDriverController::class, 'results'])->name('results');
});

// Admin Permission Routes
Route::prefix('admin/permissions')->name('admin.permissions.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admin\PermissionController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\PermissionController::class, 'store'])->name('store');
    Route::get('/{permission}', [App\Http\Controllers\Admin\PermissionController::class, 'show'])->name('show');
    Route::get('/{permission}/edit', [App\Http\Controllers\Admin\PermissionController::class, 'edit'])->name('edit');
    Route::put('/{permission}', [App\Http\Controllers\Admin\PermissionController::class, 'update'])->name('update');
    Route::delete('/{permission}', [App\Http\Controllers\Admin\PermissionController::class, 'destroy'])->name('destroy');
});



// Driver Job Routes
Route::prefix('driver/jobs')->name('driver.jobs.')->middleware(['auth:driver'])->group(function () {
    Route::get('/', [App\Http\Controllers\Driver\DriverJobController::class, 'index'])->name('index');
    Route::get('/available', [App\Http\Controllers\Driver\DriverJobController::class, 'available'])->name('available');
    Route::get('/{match}', [App\Http\Controllers\Driver\DriverJobController::class, 'show'])->name('show');
    Route::post('/{match}/accept', [App\Http\Controllers\Driver\DriverJobController::class, 'accept'])->name('accept');
    Route::post('/{match}/decline', [App\Http\Controllers\Driver\DriverJobController::class, 'decline'])->name('decline');
    Route::post('/{match}/complete', [App\Http\Controllers\Driver\DriverJobController::class, 'complete'])->name('complete');
    Route::post('/alerts', [App\Http\Controllers\Driver\DriverJobController::class, 'alerts'])->name('alerts');
});

// Drivers File Routes
Route::prefix('drivers/files')->name('drivers.files.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Drivers\DriverFileController::class, 'index'])->name('index');
    Route::get('/upload', [App\Http\Controllers\Drivers\DriverFileController::class, 'upload'])->name('upload');
    Route::post('/store', [App\Http\Controllers\Drivers\DriverFileController::class, 'store'])->name('store');
    Route::get('/{file}', [App\Http\Controllers\Drivers\DriverFileController::class, 'show'])->name('show');
    Route::delete('/{file}', [App\Http\Controllers\Drivers\DriverFileController::class, 'destroy'])->name('destroy');
});

// Drivers KYC Routes
Route::prefix('drivers/kyc')->name('drivers.kyc.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Drivers\DriverKycController::class, 'index'])->name('index');
    Route::get('/{driver}/review', [App\Http\Controllers\Drivers\DriverKycController::class, 'review'])->name('review');
    Route::post('/{driver}/approve', [App\Http\Controllers\Drivers\DriverKycController::class, 'approve'])->name('approve');
    Route::post('/{driver}/reject', [App\Http\Controllers\Drivers\DriverKycController::class, 'reject'])->name('reject');
});

// ===================================================================================================
// LEGACY ROUTES (for backward compatibility)
// ===================================================================================================

// Catch-all for undefined routes
Route::fallback(function () {
    return view('errors.404');
});
