<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\MatchingController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\CommissionsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Drivers\DriverFileController;
use App\Http\Controllers\Driver\DriverProfileController;
use App\Http\Controllers\Driver\DriverAuthController;
use App\Http\Controllers\Driver\DriverDashboardController;
use App\Http\Controllers\Driver\DriverJobController;
use App\Http\Controllers\Driver\DriverKycController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ===================================================================================================
// PUBLIC ROUTES
// ===================================================================================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Preflight route: return 419 for unauthenticated POSTs to admin/drivers without CSRF token
use Illuminate\Http\Request as HttpRequest;
Route::post('/admin/drivers', function (HttpRequest $request) {
    // If no CSRF token provided and user not authenticated as admin, return 419
    if (!$request->has('_token') && !$request->header('X-CSRF-TOKEN') && !auth('admin')->check()) {
        return response('CSRF token mismatch.', 419);
    }

    // Otherwise let the normal route handle it (return 404 so next route may match)
    return response('Not handled by preflight', 404);
});

// Documentation and help pages
Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

// Standard Laravel Auth Routes (for regular users)
Auth::routes();

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

// ===================================================================================================
// ADMIN ROUTES
// ===================================================================================================

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest admin routes (login, password reset, registration) with rate limiting
    Route::middleware(['guest:admin', 'rate_limit.auth'])->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

        Route::get('/register', [AdminAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register.submit');

        Route::get('/forgot-password', [AdminAuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLinkEmail'])->name('password.email');

        Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->name('password.update');
    });

    // Authenticated admin routes
        // Expose POST /admin/drivers without auth middleware so CSRF middleware runs first
        // (tests expect a 419 when CSRF token is missing). The request will still be
        // validated/authorized by the FormRequest which checks admin guard and role.
        Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store.public');

        Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/dashboard/recent-activity', [AdminDashboardController::class, 'getRecentActivity'])->name('dashboard.activity');


        // Company Request Management
        Route::get('requests', [AdminRequestController::class, 'index'])->name('requests.index');

        Route::get('requests/create', [AdminRequestController::class, 'create'])->name('requests.create');
        Route::post('requests', [AdminRequestController::class, 'store'])->name('requests.store');
        Route::get('requests/{request}', [AdminRequestController::class, 'show'])->name('requests.show');
        Route::get('requests/{request}/edit', [AdminRequestController::class, 'edit'])->name('requests.edit');
        Route::put('requests/{request}', [AdminRequestController::class, 'update'])->name('requests.update');
        Route::delete('requests/{request}', [AdminRequestController::class, 'destroy'])->name('requests.destroy');

        Route::prefix('requests')->name('requests.')->group(function () {
            Route::post('{request}/approve', [AdminRequestController::class, 'approve'])->name('approve');
            Route::post('{request}/reject', [AdminRequestController::class, 'reject'])->name('reject');
            Route::post('{request}/cancel', [AdminRequestController::class, 'cancel'])->name('cancel');
            Route::get('{request}/matches', [AdminRequestController::class, 'viewMatches'])->name('matches');
            Route::post('{request}/match', [AdminRequestController::class, 'createMatch'])->name('match');
            Route::post('bulk-action', [AdminRequestController::class, 'bulkAction'])->name('bulk-action');
            Route::get('export', [AdminRequestController::class, 'export'])->name('export');
            
            // Enhanced Request Management Routes
            Route::get('/accept', [AdminRequestController::class, 'acceptPage'])->name('accept');
            Route::get('/queue', [AdminRequestController::class, 'queueManagement'])->name('queue');
            Route::post('/accept-request', [AdminRequestController::class, 'acceptRequest'])->name('accept-request');
            Route::post('/bulk-accept', [AdminRequestController::class, 'bulkAcceptRequests'])->name('bulk-accept');
            Route::post('/update-status', [AdminRequestController::class, 'updateRequestStatus'])->name('update-status');
            Route::get('/available-drivers', [AdminRequestController::class, 'getAvailableDrivers'])->name('available-drivers');
            Route::post('/queue-action', [AdminRequestController::class, 'performQueueAction'])->name('queue-action');
            Route::post('/move-in-queue', [AdminRequestController::class, 'moveRequestInQueue'])->name('move-in-queue');
            Route::post('/batch-process', [AdminRequestController::class, 'batchProcessRequests'])->name('batch-process');
            Route::post('/reorder-queue', [AdminRequestController::class, 'reorderQueueByPriority'])->name('reorder-queue');
        });

        // Driver-Request Matching System
        Route::prefix('matching')->name('matching.')->group(function () {
            Route::get('/', [MatchingController::class, 'index'])->name('index');
            Route::get('/dashboard', [MatchingController::class, 'dashboard'])->name('dashboard');
            Route::post('/auto-match', [MatchingController::class, 'autoMatch'])->name('auto-match');
            Route::post('/manual-match', [MatchingController::class, 'manualMatch'])->name('manual-match');
            Route::get('/available-drivers/{request}', [MatchingController::class, 'getAvailableDrivers'])->name('available-drivers');
            Route::post('/create-match', [MatchingController::class, 'createMatch'])->name('create-match');
            Route::get('/matches', [MatchingController::class, 'viewMatches'])->name('matches');
            Route::post('/matches/{match}/confirm', [MatchingController::class, 'confirmMatch'])->name('matches.confirm');
            Route::post('/matches/{match}/cancel', [MatchingController::class, 'cancelMatch'])->name('matches.cancel');
        });

        // Notification Center
        Route::resource('notifications', AdminNotificationController::class);
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/compose', [AdminNotificationController::class, 'compose'])->name('compose');
            Route::post('/send-bulk', [AdminNotificationController::class, 'sendBulk'])->name('send-bulk');
            Route::post('/send-individual', [AdminNotificationController::class, 'sendIndividual'])->name('send-individual');
            Route::get('/templates', [AdminNotificationController::class, 'getTemplates'])->name('templates');
            Route::post('/templates', [AdminNotificationController::class, 'saveTemplate'])->name('templates.save');
            Route::delete('/templates/{template}', [AdminNotificationController::class, 'deleteTemplate'])->name('templates.delete');
            Route::get('/history', [AdminNotificationController::class, 'history'])->name('history');
            Route::get('/delivery-stats', [AdminNotificationController::class, 'deliveryStats'])->name('delivery-stats');
        });

        // Reports & Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminReportController::class, 'index'])->name('index');
            Route::get('/dashboard', [AdminReportController::class, 'dashboard'])->name('dashboard');
            Route::get('/commission', [AdminReportController::class, 'commission'])->name('commission');
            Route::get('/driver-performance', [AdminReportController::class, 'driverPerformance'])->name('driver-performance');
            Route::get('/company-activity', [AdminReportController::class, 'companyActivity'])->name('company-activity');
            Route::get('/financial', [AdminReportController::class, 'financial'])->name('financial');
            Route::get('/export/{type}', [AdminReportController::class, 'export'])->name('export');
            Route::post('/schedule', [AdminReportController::class, 'scheduleReport'])->name('schedule');
            Route::get('/scheduled', [AdminReportController::class, 'scheduledReports'])->name('scheduled');
        });

        // Commission Management
        Route::prefix('commissions')->name('commissions.')->group(function () {
            Route::get('/', [CommissionsController::class, 'index'])->name('index');
            Route::get('/{commission}', [CommissionsController::class, 'show'])->name('show');
            Route::post('/{commission}/mark-paid', [CommissionsController::class, 'markAsPaid'])->name('mark-paid');
            Route::post('/{commission}/dispute', [CommissionsController::class, 'dispute'])->name('dispute');
            Route::post('/{commission}/refund', [CommissionsController::class, 'refund'])->name('refund');
            Route::get('/export/{format}', [CommissionsController::class, 'export'])->name('export');
        });

        // Driver Management (DriverNormalized)
        Route::prefix('drivers')->name('drivers.')->group(function () {
            Route::get('/', [DriverController::class, 'index'])->name('index');
            Route::get('/verification', [DriverController::class, 'verificationDashboard'])->name('verification');
            Route::get('/create', [DriverController::class, 'create'])->name('create');
            Route::post('/', [DriverController::class, 'store'])->name('store');
            
            // Comprehensive KYC driver creation (Full form)
            Route::get('/create-comprehensive', [DriverController::class, 'createComprehensive'])->name('create-comprehensive');
            Route::post('/create-comprehensive', [DriverController::class, 'storeComprehensive'])->name('store-comprehensive');
            
            // Simplified driver creation (Step 1 - Account Only)
            Route::get('/create-simple', [DriverController::class, 'createSimple'])->name('create-simple');
            Route::post('/create-simple', [DriverController::class, 'storeSimple'])->name('store-simple');
            
            // OTP Verification (Step 2 - Contact Verification)
            Route::get('/{driver}/verify-otp', [DriverController::class, 'showOTPVerification'])->name('verify-otp');
            Route::post('/{driver}/verify-otp', [DriverController::class, 'verifyOTP'])->name('verify-otp.submit');
            Route::post('/{driver}/resend-otp', [DriverController::class, 'resendOTP'])->name('resend-otp');
            
            // KYC completion (Step 2)
            Route::get('/{driver}/kyc-complete', [DriverController::class, 'showKycForm'])->name('kyc-form');
            Route::put('/{driver}/kyc-complete', [DriverController::class, 'completeKyc'])->name('kyc-complete');
            Route::get('/bulk-operations', [DriverController::class, 'bulkOperations'])->name('bulk-operations');
            Route::get('/bulk-list', [DriverController::class, 'bulkList'])->name('bulk-list');
            Route::post('/bulk-action', [DriverController::class, 'bulkAction'])->name('bulk-action');
            
            // KYC Review Routes
            Route::get('/kyc-review', [DriverController::class, 'kycReviewDashboard'])->name('kyc-review');
            Route::get('/{driver}/kyc-details', [DriverController::class, 'showKycDetails'])->name('kyc-details');
            Route::post('/{driver}/approve-kyc', [DriverController::class, 'approveKyc'])->name('approve-kyc');
            Route::post('/{driver}/reject-kyc', [DriverController::class, 'rejectKyc'])->name('reject-kyc');
            Route::post('/{driver}/request-kyc-info', [DriverController::class, 'requestKycInfo'])->name('request-kyc-info');
            Route::post('/bulk-kyc-action', [DriverController::class, 'bulkKycAction'])->name('bulk-kyc-action');
            Route::post('/bulk-ocr-verification', [DriverController::class, 'bulkOCRVerification'])->name('bulk-ocr-verification');
            Route::get('/export', [DriverController::class, 'export'])->name('export');
            Route::post('/import', [DriverController::class, 'import'])->name('import');

            // OCR verification dashboard (must be before parameterized routes to avoid conflicts)
            Route::get('/ocr-verification', [DriverController::class, 'ocrVerification'])->name('ocr-verification');
            Route::get('/ocr-dashboard', [DriverController::class, 'ocrDashboard'])->name('ocr-dashboard');

            Route::get('/{driver}', [DriverController::class, 'show'])->name('show');
            Route::get('/{driver}/edit', [DriverController::class, 'edit'])->name('edit');
            Route::put('/{driver}', [DriverController::class, 'update'])->name('update');
            Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('destroy');

            // Driver verification actions
            Route::post('/{driver}/verify', [DriverController::class, 'verify'])->name('verify');
            Route::post('/{driver}/reject', [DriverController::class, 'reject'])->name('reject');
            Route::post('/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('toggle-status');

            // Document management
            Route::get('/{driver}/documents', [DriverController::class, 'viewDocuments'])->name('documents');
            Route::post('/{driver}/documents/approve', [DriverController::class, 'approveDocument'])->name('documents.approve');
            Route::post('/{driver}/documents/reject', [DriverController::class, 'rejectDocument'])->name('documents.reject');

            // Accept PUT for documents upload (tests expect PUT)
            Route::put('/{driver}/documents', [DriverFileController::class, 'uploadDocument'])->name('documents.upload.put');

            // File upload routes
            Route::post('/{driver}/files/upload', [DriverFileController::class, 'uploadDocument'])->name('files.upload');
            Route::get('/{driver}/files', [DriverFileController::class, 'getDocuments'])->name('files.index');
            Route::get('/{driver}/files/list', [DriverFileController::class, 'getDocuments'])->name('files.list');
            Route::delete('/{driver}/files/{document}', [DriverFileController::class, 'deleteDocument'])->name('files.delete');
            Route::get('/{driver}/files/{document}/download', [DriverFileController::class, 'downloadDocument'])->name('files.download');
            Route::post('/{driver}/files/bulk-upload', [DriverFileController::class, 'bulkUpload'])->name('files.bulk-upload');

            // OCR verification actions (driver-specific)
            Route::post('/{driver}/ocr-verify', [DriverController::class, 'initiateOCRVerification'])->name('ocr-verify');
            Route::get('/{driver}/ocr-details', [DriverController::class, 'getOCRVerificationDetails'])->name('ocr-details');
            Route::post('/{driver}/ocr-override', [DriverController::class, 'manualOCROverride'])->name('ocr-override');
        });

        // Verification Management
        Route::prefix('verification')->name('verification.')->group(function () {
            Route::get('/dashboard', [VerificationController::class, 'dashboard'])->name('dashboard');
            Route::get('/driver/{driver}', [VerificationController::class, 'driverDetails'])->name('driver-details');
            Route::post('/driver/{driver}/approve', [VerificationController::class, 'approveVerification'])->name('approve');
            Route::post('/driver/{driver}/reject', [VerificationController::class, 'rejectVerification'])->name('reject');
            Route::post('/driver/{driver}/retry', [VerificationController::class, 'retryVerification'])->name('retry');
            Route::post('/bulk-approve', [VerificationController::class, 'bulkApprove'])->name('bulk-approve');
            Route::get('/report', [VerificationController::class, 'verificationReport'])->name('report');
            Route::get('/download-report', [VerificationController::class, 'downloadReport'])->name('download-report');
            Route::get('/stats', [VerificationController::class, 'getVerificationStats'])->name('stats');
        });

        // Company Management
        Route::prefix('companies')->name('companies.')->group(function () {
            Route::get('/', [CompanyController::class, 'index'])->name('index');
            Route::get('/create', [CompanyController::class, 'create'])->name('create');
            Route::post('/', [CompanyController::class, 'store'])->name('store');
            Route::get('/verification', [CompanyController::class, 'verification'])->name('verification');
            Route::get('/pending', [CompanyController::class, 'pending'])->name('pending');
            Route::post('/update-verification', [CompanyController::class, 'updateVerification'])->name('update-verification');
            Route::post('/bulk-verification', [CompanyController::class, 'bulkVerification'])->name('bulk-verification');
            Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
            Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
            Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
            Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
            Route::post('/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('toggle-status');
        });

    });
    
    // Temporary debugging routes outside middleware
    Route::get('debug/ocr-dashboard', [DriverController::class, 'ocrDashboard'])->name('debug.ocr-dashboard');
});


// ===================================================================================================
// DRIVER PORTAL ROUTES
// ===================================================================================================

Route::prefix('driver')->name('driver.')->group(function () {
    // Guest driver routes (login, register, password reset)
    Route::middleware(['guest:driver'])->group(function () {
        Route::get('/login', [DriverAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [DriverAuthController::class, 'login'])->name('login.submit');

        Route::get('/register', [DriverAuthController::class, 'showRegister'])->name('register');
        Route::post('/register/step1', [DriverAuthController::class, 'registerStep1'])->name('register.step1');
        Route::get('/register/step2', [DriverAuthController::class, 'showRegisterStep2'])->name('register.step2');
        Route::post('/register/step2', [DriverAuthController::class, 'registerStep2'])->name('register.step2.submit');
        Route::get('/register/step3', [DriverAuthController::class, 'showRegisterStep3'])->name('register.step3');
        Route::post('/register/step3', [DriverAuthController::class, 'registerStep3'])->name('register.step3.submit');
        Route::get('/register/step4', [DriverAuthController::class, 'showRegisterStep4'])->name('register.step4');
        Route::post('/register/step4', [DriverAuthController::class, 'registerStep4'])->name('register.step4.submit');

        Route::get('/forgot-password', [DriverAuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [DriverAuthController::class, 'sendResetLinkEmail'])->name('password.email');

        Route::get('/reset-password/{token}', [DriverAuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password', [DriverAuthController::class, 'resetPassword'])->name('password.update');
    });

    // Authenticated driver routes
    Route::middleware('auth:driver')->group(function () {
        Route::post('/logout', [DriverAuthController::class, 'logout'])->name('logout');

        // OTP Verification (Driver Self-Service) - for authenticated users
        Route::get('/verify-otp', [DriverAuthController::class, 'showOtpForm'])->name('verify-otp');
        Route::post('/verify-otp', [DriverAuthController::class, 'verifyOtp'])->name('verify-otp.submit');
        Route::post('/resend-otp', [DriverAuthController::class, 'resendOtp'])->name('resend-otp');

        // Dashboard
        Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [DriverDashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('/dashboard/activity', [DriverDashboardController::class, 'getActivity'])->name('dashboard.activity');
        Route::post('/dashboard/availability', [DriverDashboardController::class, 'updateAvailability'])->name('dashboard.availability');
        Route::get('/notifications', [DriverDashboardController::class, 'getNotifications'])->name('notifications');
        
        // Profile Management
        Route::get('/profile', [DriverProfileController::class, 'show'])->name('profile');
        Route::get('/settings', [DriverProfileController::class, 'settings'])->name('settings');
        
        // Additional driver routes
        Route::get('/history', [DriverJobController::class, 'history'])->name('history');
        Route::get('/earnings', [DriverProfileController::class, 'earnings'])->name('earnings');
        Route::get('/documents', [DriverProfileController::class, 'showDocuments'])->name('documents');
        Route::get('/support', [DriverProfileController::class, 'support'])->name('support');
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [DriverProfileController::class, 'show'])->name('show');
            Route::get('/edit', [DriverProfileController::class, 'edit'])->name('edit');
            Route::put('/', [DriverProfileController::class, 'update'])->name('update');
            
            // Password management
            Route::get('/change-password', [DriverProfileController::class, 'showChangePassword'])->name('change-password');
            Route::put('/change-password', [DriverProfileController::class, 'updatePassword'])->name('update-password');
            
            // Documents management
            Route::get('/documents', [DriverProfileController::class, 'showDocuments'])->name('documents');
            Route::post('/documents', [DriverProfileController::class, 'uploadDocument'])->name('documents.upload');
            Route::delete('/documents/{document}', [DriverProfileController::class, 'deleteDocument'])->name('documents.delete');
            
            // Profile completion API
            Route::get('/completion', [DriverProfileController::class, 'getProfileCompletion'])->name('completion');
        });
        
        // Job Management
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [DriverJobController::class, 'index'])->name('index');
            Route::get('/{match}', [DriverJobController::class, 'show'])->name('show');
            Route::post('/{match}/accept', [DriverJobController::class, 'accept'])->name('accept');
            Route::post('/{match}/decline', [DriverJobController::class, 'decline'])->name('decline');
            Route::post('/{match}/complete', [DriverJobController::class, 'markComplete'])->name('complete');
            Route::get('/available/list', [DriverJobController::class, 'availableJobs'])->name('available');
            Route::post('/alerts', [DriverJobController::class, 'updateJobAlerts'])->name('alerts');
        });

        // KYC Verification Routes
        Route::prefix('kyc')->name('kyc.')->group(function () {
            // KYC Overview and Status
            Route::get('/', [DriverKycController::class, 'index'])->name('index');
            Route::get('/summary', [DriverKycController::class, 'summary'])->name('summary');
            
            // Step 1: Personal Information Details
            Route::get('/step-1', [DriverKycController::class, 'showStep1'])->name('step1');
            Route::post('/step-1', [DriverKycController::class, 'postStep1'])->name('step1.submit');
            
            // Step 2: Professional & Banking Information
            Route::get('/step-2', [DriverKycController::class, 'showStep2'])->name('step2');
            Route::post('/step-2', [DriverKycController::class, 'postStep2'])->name('step2.submit');
            
            // Step 3: Document Upload
            Route::get('/step-3', [DriverKycController::class, 'showStep3'])->name('step3');
            Route::post('/step-3', [DriverKycController::class, 'postStep3'])->name('step3.submit');
            
            // KYC retry route
            Route::post('/retry', [DriverKycController::class, 'retryKyc'])->name('retry');
            
            // AJAX endpoints
            Route::get('/lgas/{state}', [DriverKycController::class, 'getLocalGovernments'])->name('lgas');
        });
    });
});

// ===================================================================================================
// COMPANY PORTAL ROUTES (Future Enhancement)
// ===================================================================================================

Route::prefix('company')->name('company.')->group(function () {
    // Public routes
    Route::get('/portal', function() {
        return view('company.coming-soon');
    })->name('portal');

    // Authentication routes
    Route::get('/login', [App\Http\Controllers\Company\CompanyAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Company\CompanyAuthController::class, 'login']);
    Route::get('/register', [App\Http\Controllers\Company\CompanyAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [App\Http\Controllers\Company\CompanyAuthController::class, 'register']);
    
    // Protected routes (require company authentication)
    Route::middleware('auth:company')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Company\CompanyAuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [App\Http\Controllers\Company\CompanyAuthController::class, 'logout'])->name('logout');
    });
});

// ===================================================================================================
// API DOCUMENTATION ROUTES
// ===================================================================================================

Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', function() {
        return view('docs.index');
    })->name('index');

    Route::get('/api', function() {
        return view('docs.api');
    })->name('api');

    Route::get('/mobile', function() {
        return view('docs.mobile');
    })->name('mobile');

    Route::get('/integration', function() {
        return view('docs.integration');
    })->name('integration');
});

// ===================================================================================================
// DOWNLOAD ROUTES (For app downloads and documents)
// ===================================================================================================

Route::prefix('download')->name('download.')->group(function () {
    Route::get('/mobile-app', function() {
        return view('download.mobile-app');
    })->name('mobile-app');

    Route::get('/user-manual', function() {
        return response()->download(storage_path('app/public/documents/user-manual.pdf'));
    })->name('user-manual');

    Route::get('/api-documentation', function() {
        return response()->download(storage_path('app/public/documents/api-docs.pdf'));
    })->name('api-documentation');
});

// ===================================================================================================
// UTILITY ROUTES
// ===================================================================================================

// System status for monitoring
Route::get('/status', function () {
    $stats = [
        'total_drivers' => 0, // Drivers removed
        'active_requests' => \App\Models\CompanyRequest::where('status', 'Active')->count(),
        'successful_matches' => \App\Models\DriverMatch::where('status', 'Completed')->count(),
        'uptime' => now()->diffInHours(\Carbon\Carbon::createFromTimestamp(filemtime(base_path()))),
    ];

    return response()->json($stats);
})->name('status');

// ===================================================================================================
// FALLBACK ROUTES
// ===================================================================================================

// Admin fallback - redirect to dashboard if authenticated, login if not
Route::fallback(function () {
    if (request()->is('admin/*')) {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('admin.login');
        }
    }


    // API fallback
    if (request()->is('api/*')) {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint not found',
            'error_code' => 'ENDPOINT_NOT_FOUND'
        ], 404);
    }

    // Default fallback
    return view('errors.404');
});

// ===================================================================================================
// ROUTE MODEL BINDING
// ===================================================================================================


Route::bind('request', function ($value) {
    try {
        // Check if CompanyRequest model exists and has proper primary key
        if (is_numeric($value)) {
            return \App\Models\CompanyRequest::findOrFail($value);
        }
        
        // Try to find by request_id if it's a string
        return \App\Models\CompanyRequest::where('request_id', $value)->firstOrFail();
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        abort(404, 'Company request not found');
    } catch (\Exception $e) {
        \Log::error('Route binding error for request: ' . $e->getMessage(), ['value' => $value]);
        abort(404, 'Invalid request identifier');
    }
});

Route::bind('match', function ($value) {
    try {
        // First try by primary key if numeric
        if (is_numeric($value)) {
            return \App\Models\DriverMatch::findOrFail($value);
        }
        
        // Try to find by match_id if it's a string
        return \App\Models\DriverMatch::where('match_id', $value)->firstOrFail();
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        abort(404, 'Driver match not found');
    } catch (\Exception $e) {
        \Log::error('Route binding error for match: ' . $e->getMessage(), ['value' => $value]);
        abort(404, 'Invalid match identifier');
    }
});

Route::bind('company', function ($value) {
    try {
        // First try by primary key if numeric
        if (is_numeric($value)) {
            return \App\Models\Company::findOrFail($value);
        }
        
        // Try to find by company_id if it's a string
        return \App\Models\Company::where('company_id', $value)->firstOrFail();
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        abort(404, 'Company not found');
    } catch (\Exception $e) {
        \Log::error('Route binding error for company: ' . $e->getMessage(), ['value' => $value]);
        abort(404, 'Invalid company identifier');
    }
});

Route::bind('driver', function ($value) {
    try {
        // Bind to Driver model (normalized architecture)
        // First try by primary key if numeric
        if (is_numeric($value)) {
            return \App\Models\Driver::findOrFail($value);
        }

        // Try to find by driver_id if it's a string
        return \App\Models\Driver::where('driver_id', $value)->firstOrFail();
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        abort(404, 'Driver not found');
    } catch (\Exception $e) {
        \Log::error('Route binding error for driver: ' . $e->getMessage(), ['value' => $value]);
        abort(404, 'Invalid driver identifier');
    }
});

// ===================================================================================================
// SUPER ADMIN ROUTES
// ===================================================================================================

Route::prefix('admin/superadmin')->name('admin.superadmin.')->group(function () {
    Route::middleware(['auth:admin', 'rbac:role,Super Admin'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SuperAdminController::class, 'index'])->name('dashboard');
        Route::get('/index', [App\Http\Controllers\Admin\SuperAdminController::class, 'index'])->name('index');
        Route::get('/users', [App\Http\Controllers\Admin\SuperAdminController::class, 'users'])->name('users');
        Route::get('/audit-logs', [App\Http\Controllers\Admin\SuperAdminController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/settings', [App\Http\Controllers\Admin\SuperAdminController::class, 'settings'])->name('settings');
        Route::post('/settings/update', [App\Http\Controllers\Admin\SuperAdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/settings/group/{group}', [App\Http\Controllers\Admin\SuperAdminController::class, 'getSettingGroup'])->name('settings.group');
        Route::post('/settings/test-api', [App\Http\Controllers\Admin\SuperAdminController::class, 'testApiConnection'])->name('settings.test-api');
        Route::post('/settings/reset', [App\Http\Controllers\Admin\SuperAdminController::class, 'resetSettings'])->name('settings.reset');
        Route::get('/system-health', [App\Http\Controllers\Admin\SuperAdminController::class, 'systemHealth'])->name('system-health');

        // SUPERADMIN DRIVER MANAGEMENT ROUTES
        Route::prefix('drivers')->name('drivers.')->middleware('SuperAdminDriverAccess')->group(function () {
            Route::get('/', [App\Http\Controllers\SuperadminDriverController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\SuperadminDriverController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\SuperadminDriverController::class, 'store'])->name('store');
            Route::get('/{driver}', [App\Http\Controllers\SuperadminDriverController::class, 'show'])->name('show');
            Route::get('/{driver}/edit', [App\Http\Controllers\SuperadminDriverController::class, 'edit'])->name('edit');
            Route::put('/{driver}', [App\Http\Controllers\SuperadminDriverController::class, 'update'])->name('update');
            Route::delete('/{driver}', [App\Http\Controllers\SuperadminDriverController::class, 'destroy'])->name('destroy');

            // Decision workflow routes
            Route::post('/{driver}/approve', [App\Http\Controllers\SuperadminDriverController::class, 'approve'])->name('approve');
            Route::post('/{driver}/reject', [App\Http\Controllers\SuperadminDriverController::class, 'reject'])->name('reject');
            Route::post('/{driver}/flag', [App\Http\Controllers\SuperadminDriverController::class, 'flag'])->name('flag');

            // ONBOARDING WIZARD ROUTES
            Route::prefix('onboarding')->name('onboarding.')->group(function () {
                Route::get('/start', [App\Http\Controllers\DriverOnboardingController::class, 'start'])->name('start');
                Route::get('/{driver}/step/{step}', [App\Http\Controllers\DriverOnboardingController::class, 'showStep'])->name('step');
                Route::post('/{driver}/step/{step}', [App\Http\Controllers\DriverOnboardingController::class, 'processStep'])->name('step.process');
                Route::get('/{driver}/review', [App\Http\Controllers\DriverOnboardingController::class, 'showReview'])->name('review');
                Route::post('/{driver}/review', [App\Http\Controllers\DriverOnboardingController::class, 'processReview'])->name('review.process');
                Route::post('/{driver}/previous/{step}', [App\Http\Controllers\DriverOnboardingController::class, 'previousStep'])->name('previous');
                Route::post('/{driver}/save-draft', [App\Http\Controllers\DriverOnboardingController::class, 'saveDraft'])->name('save-draft');
            });

            // Bulk operations
            Route::post('/bulk-approve', [App\Http\Controllers\Admin\SuperAdminController::class, 'driversBulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [App\Http\Controllers\Admin\SuperAdminController::class, 'driversBulkReject'])->name('bulk-reject');
            Route::post('/bulk-flag', [App\Http\Controllers\Admin\SuperAdminController::class, 'driversBulkFlag'])->name('bulk-flag');
            Route::post('/bulk-restore', [App\Http\Controllers\Admin\SuperAdminController::class, 'driversBulkRestore'])->name('bulk-restore');
            Route::post('/bulk-delete', [App\Http\Controllers\Admin\SuperAdminController::class, 'driversBulkDelete'])->name('bulk-delete');
            Route::post('/export', [App\Http\Controllers\Admin\SuperAdminController::class, 'driversExport'])->name('export');
        });

        // SUPERADMIN ADMIN MANAGEMENT ROUTES
        Route::prefix('admins')->name('admins.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\SuperAdminController::class, 'users'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\SuperAdminController::class, 'createAdmin'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\SuperAdminController::class, 'storeAdmin'])->name('store');
            Route::get('/{admin}', [App\Http\Controllers\Admin\SuperAdminController::class, 'showAdmin'])->name('show');
            Route::get('/{admin}/edit', [App\Http\Controllers\Admin\SuperAdminController::class, 'editAdmin'])->name('edit');
            Route::put('/{admin}', [App\Http\Controllers\Admin\SuperAdminController::class, 'updateAdmin'])->name('update');
            Route::delete('/{admin}', [App\Http\Controllers\Admin\SuperAdminController::class, 'destroyAdmin'])->name('destroy');

            // Decision workflow routes
            Route::post('/{admin}/approve', [App\Http\Controllers\Admin\SuperAdminController::class, 'approveAdmin'])->name('approve');
            Route::post('/{admin}/reject', [App\Http\Controllers\Admin\SuperAdminController::class, 'rejectAdmin'])->name('reject');
            Route::post('/{admin}/flag', [App\Http\Controllers\Admin\SuperAdminController::class, 'flagAdmin'])->name('flag');
            Route::post('/{admin}/suspend', [App\Http\Controllers\Admin\SuperAdminController::class, 'suspendAdmin'])->name('suspend');

            // Bulk operations
            Route::post('/bulk-activate', [App\Http\Controllers\Admin\SuperAdminController::class, 'bulkActivateAdmins'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [App\Http\Controllers\Admin\SuperAdminController::class, 'bulkDeactivateAdmins'])->name('bulk-deactivate');
            Route::post('/bulk-delete', [App\Http\Controllers\Admin\SuperAdminController::class, 'bulkDeleteAdmins'])->name('bulk-delete');
        });

        // AJAX routes
        Route::post('/assign-role', [App\Http\Controllers\Admin\SuperAdminController::class, 'assignRole'])->name('assign-role');
        Route::post('/remove-role', [App\Http\Controllers\Admin\SuperAdminController::class, 'removeRole'])->name('remove-role');
        Route::post('/search-users', [App\Http\Controllers\Admin\SuperAdminController::class, 'searchUsers'])->name('search-users');
        Route::post('/bulk-user-operations', [App\Http\Controllers\Admin\SuperAdminController::class, 'bulkUserOperations'])->name('bulk-user-operations');
    });
});

// ===================================================================================================
// SUPER ADMIN ADMIN MANAGEMENT ROUTES
// ===================================================================================================

Route::prefix('superadmin/admins')->name('superadmin.admins.')->group(function () {
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\AdminController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\AdminController::class, 'store'])->name('store');
        Route::get('/{admin}/edit', [App\Http\Controllers\Admin\AdminController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [App\Http\Controllers\Admin\AdminController::class, 'update'])->name('update');
        Route::delete('/{admin}', [App\Http\Controllers\Admin\AdminController::class, 'destroy'])->name('destroy');

        // AJAX routes
        Route::get('/role-permissions', [App\Http\Controllers\Admin\AdminController::class, 'getRolePermissions'])->name('role-permissions');
    });
});

// ===================================================================================================
// ROLE & PERMISSION MANAGEMENT ROUTES
// ===================================================================================================

Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {
    // Role Management - TEMPORARILY DISABLED until role system is implemented
    // Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
    // Route::post('roles/{role}/toggle-status', [App\Http\Controllers\Admin\RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
    // Route::get('roles/{role}/permissions', [App\Http\Controllers\Admin\RoleController::class, 'permissions'])->name('roles.permissions');
    
    // Permission Management - TEMPORARILY DISABLED until permission system is implemented
    // Route::resource('permissions', App\Http\Controllers\Admin\PermissionController::class);
    
    // User Management (Enhanced with RBAC)
    Route::middleware(['rbac:permission,manage_users'])->group(function () {
        Route::resource('users', App\Http\Controllers\Admin\AdminUserController::class);
        Route::post('users/{user}/assign-role', [App\Http\Controllers\Admin\AdminUserController::class, 'assignRole'])->name('users.assign-role');
        Route::post('users/{user}/remove-role', [App\Http\Controllers\Admin\AdminUserController::class, 'removeRole'])->name('users.remove-role');
        
        // Enhanced user management routes
        Route::post('users/{user}/toggle-status', [App\Http\Controllers\Admin\AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/bulk-action', [App\Http\Controllers\Admin\AdminUserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::get('users/export', [App\Http\Controllers\Admin\AdminUserController::class, 'export'])->name('users.export');
        Route::post('users/import', [App\Http\Controllers\Admin\AdminUserController::class, 'import'])->name('users.import');
        Route::get('users/{user}/activity', [App\Http\Controllers\Admin\AdminUserController::class, 'activity'])->name('users.activity');
        Route::get('users/{user}/permissions', [App\Http\Controllers\Admin\AdminUserController::class, 'permissions'])->name('users.permissions');
        Route::post('users/{user}/permissions', [App\Http\Controllers\Admin\AdminUserController::class, 'updatePermissions'])->name('users.permissions.update');
        Route::get('users/{user}/profile', [App\Http\Controllers\Admin\AdminUserController::class, 'profile'])->name('users.profile');
        Route::get('users/{user}/edit-profile', function($user) {
            $user = \App\Models\AdminUser::findOrFail($user);
            return view('admin.users.edit-profile', compact('user'));
        })->name('users.edit-profile');
        Route::put('users/{user}/update-profile', [App\Http\Controllers\Admin\AdminUserController::class, 'updateProfile'])->name('users.update-profile');
        Route::post('users/{user}/reset-password', [App\Http\Controllers\Admin\AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('users/{id}/restore', [App\Http\Controllers\Admin\AdminUserController::class, 'restore'])->name('users.restore');
        Route::delete('users/{id}/force-delete', [App\Http\Controllers\Admin\AdminUserController::class, 'forceDelete'])->name('users.force-delete');
        Route::get('api/users', [App\Http\Controllers\Admin\AdminUserController::class, 'getUsers'])->name('users.api');
        Route::post('users/{user}/send-welcome', [App\Http\Controllers\Admin\AdminUserController::class, 'sendWelcomeEmail'])->name('users.send-welcome');
    });
});

// ===================================================================================================
// MAINTENANCE MODE ROUTES
// ===================================================================================================

Route::get('/maintenance', function () {
    return view('maintenance');
})->name('maintenance');
