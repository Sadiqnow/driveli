<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverNormalized as Driver;
use App\Http\Requests\DriverRegistrationRequest;
use App\Http\Requests\DriverProfileUpdateRequest;
use App\Services\DriverService;
use App\Services\SecureFileUploadService;
use App\Services\OCRVerificationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Optimized Driver Controller
 * 
 * Refactored version of DriverController with improved:
 * - Method organization and single responsibility
 * - Proper dependency injection
 * - Enhanced error handling
 * - Query optimization
 * - Security improvements
 * 
 * @package App\Http\Controllers\Admin
 */
class OptimizedDriverController extends Controller
{
    /** @var DriverService */
    protected $driverService;
    
    /** @var SecureFileUploadService */
    protected $fileUploadService;
    
    /** @var OCRVerificationService */
    protected $ocrService;
    
    /** @var NotificationService */
    protected $notificationService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        DriverService $driverService,
        SecureFileUploadService $fileUploadService,
        OCRVerificationService $ocrService,
        NotificationService $notificationService
    ) {
        $this->driverService = $driverService;
        $this->fileUploadService = $fileUploadService;
        $this->ocrService = $ocrService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display paginated list of drivers with optimized queries
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $result = $this->driverService->getDriverList($request);
            
            if ($request->get('format') === 'json') {
                return $this->handleJsonResponse($request, $result['drivers']);
            }
            
            return view('admin.drivers.index', [
                'drivers' => $result['drivers'],
                'verifiedCount' => $result['metrics']['verification_counts']['verified'],
                'pendingCount' => $result['metrics']['verification_counts']['pending'],
                'rejectedCount' => $result['metrics']['verification_counts']['rejected'],
                'activeCount' => $result['metrics']['status_counts']['active'],
                'totalEarnings' => $result['metrics']['performance']['total_earnings'],
                'totalJobsCompleted' => $result['metrics']['performance']['total_jobs'],
                'averageRating' => $result['metrics']['performance']['average_rating'],
                'newDriversThisMonth' => $result['metrics']['time_based']['new_this_month'],
                'activeDriversToday' => $result['metrics']['time_based']['active_today'],
                'onlineDrivers' => $result['metrics']['time_based']['online_now']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Driver index page failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load drivers: ' . $e->getMessage());
        }
    }

    /**
     * Show verification page with optimized filtering
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function verification(Request $request)
    {
        try {
            $drivers = $this->driverService->getDriversForVerification($request);
            $metrics = $this->driverService->calculatePerformanceMetrics();
            
            return view('admin.drivers.verification', [
                'drivers' => $drivers,
                'verificationType' => $request->get('type', 'pending'),
                'pendingCount' => $metrics['verification_counts']['pending'],
                'verifiedCount' => $metrics['verification_counts']['verified'],
                'rejectedCount' => $metrics['verification_counts']['rejected']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Driver verification page failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load verification page: ' . $e->getMessage());
        }
    }

    /**
     * Show create driver form
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.drivers.create');
    }

    /**
     * Store new driver using service class
     * 
     * @param DriverRegistrationRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(DriverRegistrationRequest $request)
    {
        try {
            Log::info('Driver creation attempt', [
                'admin_id' => auth('admin')->id(),
                'email' => $request->email
            ]);

            $result = $this->driverService->createDriver($request->validated(), $request);
            
            if ($result['success']) {
                return redirect()->route('admin.drivers.index')
                    ->with('success', $result['message']);
            } else {
                return back()->withInput()
                    ->withErrors(['error' => $result['error']]);
            }
            
        } catch (\Exception $e) {
            Log::error('Driver creation failed', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Show driver details with optimized loading
     * 
     * @param Driver $driver
     * @return \Illuminate\View\View
     */
    public function show(Driver $driver)
    {
        $driver->load([
            'verifiedBy:id,name,email',
            'nationality:id,name,code',
            'performance:id,driver_id,total_jobs_completed,average_rating,total_earnings',
            'locations:id,driver_id,location_type,state_id,lga_id,address,is_primary',
            'locations.state:id,name',
            'locations.lga:id,name',
            'bankingDetails:id,driver_id,bank_id,account_name,is_verified',
            'bankingDetails.bank:id,name,code',
            'guarantors:id,driver_id,first_name,last_name,relationship,phone'
        ]);
        
        return view('admin.drivers.show', compact('driver'));
    }

    /**
     * Show edit driver form
     * 
     * @param Driver $driver
     * @return \Illuminate\View\View
     */
    public function edit(Driver $driver)
    {
        $driver->load(['verifiedBy:id,name,email']);
        return view('admin.drivers.edit', compact('driver'));
    }

    /**
     * Update driver using service class
     * 
     * @param DriverProfileUpdateRequest $request
     * @param Driver $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(DriverProfileUpdateRequest $request, Driver $driver)
    {
        try {
            $result = $this->driverService->updateDriver($driver, $request->validated());
            
            if ($result['success']) {
                return redirect()->route('admin.drivers.index')
                    ->with('success', $result['message']);
            } else {
                return back()->withInput()
                    ->withErrors(['error' => $result['error']]);
            }
            
        } catch (\Exception $e) {
            Log::error('Driver update failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Soft delete driver
     * 
     * @param Driver $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Driver $driver)
    {
        try {
            $driver->delete();
            
            Log::info('Driver soft deleted', [
                'driver_id' => $driver->id,
                'admin_id' => auth('admin')->id()
            ]);
            
            return redirect()->route('admin.drivers.index')
                ->with('success', 'Driver deleted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Driver deletion failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to delete driver: ' . $e->getMessage());
        }
    }

    /**
     * Verify driver with enhanced security
     * 
     * @param Driver $driver
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Driver $driver, Request $request)
    {
        $request->validate([
            'admin_password' => 'required|string',
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        if (!Hash::check($request->admin_password, auth('admin')->user()->password)) {
            return back()->withErrors(['admin_password' => 'Invalid admin password.']);
        }

        try {
            DB::beginTransaction();

            $driver->adminUpdateVerification('verified', auth('admin')->user(), $request->verification_notes);
            
            $notificationResult = $this->notificationService->sendVerificationNotification(
                $driver, 
                'verified', 
                $request->verification_notes
            );

            DB::commit();

            $message = 'Driver verified successfully!';
            if ($notificationResult['success']) {
                $message .= ' Notification sent to driver.';
            } else {
                $message .= ' (Notification failed to send)';
            }

            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Driver verification failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject driver verification with enhanced security
     * 
     * @param Driver $driver
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Driver $driver, Request $request)
    {
        $request->validate([
            'admin_password' => 'required|string',
            'rejection_reason' => 'required|string',
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        if (!Hash::check($request->admin_password, auth('admin')->user()->password)) {
            return back()->withErrors(['admin_password' => 'Invalid admin password.']);
        }

        try {
            DB::beginTransaction();

            $rejectionNote = "Reason: " . ucwords(str_replace('_', ' ', $request->rejection_reason));
            if ($request->verification_notes) {
                $rejectionNote .= "\nNotes: " . $request->verification_notes;
            }

            $driver->adminUpdateVerification('rejected', auth('admin')->user(), $rejectionNote);
            
            $notificationResult = $this->notificationService->sendVerificationNotification(
                $driver, 
                'rejected', 
                $rejectionNote
            );

            DB::commit();

            $message = 'Driver verification rejected successfully!';
            if ($notificationResult['success']) {
                $message .= ' Notification sent to driver.';
            } else {
                $message .= ' (Notification failed to send)';
            }

            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Driver rejection failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Toggle driver status
     * 
     * @param Driver $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(Driver $driver)
    {
        try {
            $newStatus = $driver->status === 'active' ? 'inactive' : 'active';
            $driver->adminUpdateStatus($newStatus, auth('admin')->user());

            return back()->with('success', "Driver status changed to {$newStatus}!");
            
        } catch (\Exception $e) {
            Log::error('Driver status toggle failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to change status: ' . $e->getMessage());
        }
    }

    /**
     * View driver documents
     * 
     * @param Driver $driver
     * @return \Illuminate\View\View
     */
    public function viewDocuments(Driver $driver)
    {
        $driver->load([
            'documents',
            'verifiedBy:id,name,email'
        ]);
        
        $documents = [
            'profile_photo' => $driver->profile_photo,
            'license_front_image' => $driver->license_front_image,
            'license_back_image' => $driver->license_back_image,
            'nin_document' => $driver->nin_document,
            'passport_photograph' => $driver->passport_photograph,
            'additional_documents' => $driver->additional_documents,
        ];
        
        return view('admin.drivers.documents', compact('driver', 'documents'));
    }

    /**
     * Handle bulk operations using service class
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'exists:drivers,id',
            'notes' => 'nullable|string',
            'admin_password' => 'required_if:action,verify,reject,ocr_verify|string'
        ]);

        // Verify admin password for sensitive operations
        if (in_array($request->action, ['verify', 'reject', 'ocr_verify'])) {
            if (!Hash::check($request->admin_password, auth('admin')->user()->password)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Invalid admin password.'], 422);
                }
                return back()->withErrors(['admin_password' => 'Invalid admin password.']);
            }
        }

        try {
            $result = $this->driverService->bulkUpdateDrivers(
                $request->driver_ids, 
                $request->action,
                ['notes' => $request->notes]
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message']
                ]);
            }
            
            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }
            
        } catch (\Exception $e) {
            Log::error('Bulk action failed', [
                'action' => $request->action,
                'driver_count' => count($request->driver_ids),
                'error' => $e->getMessage()
            ]);
            
            $errorMessage = 'Bulk operation failed: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMessage], 500);
            }
            
            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Handle JSON response for AJAX requests
     * 
     * @param Request $request
     * @param $drivers
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleJsonResponse(Request $request, $drivers)
    {
        if ($request->get('include_ocr') === 'true') {
            return response()->json([
                'success' => true,
                'drivers' => $drivers->map(function ($driver) {
                    return [
                        'id' => $driver->id,
                        'driver_id' => $driver->driver_id,
                        'full_name' => $driver->full_name,
                        'email' => $driver->email,
                        'phone' => $driver->phone,
                        'status' => $driver->status,
                        'verification_status' => $driver->verification_status,
                        'nin_ocr_match_score' => $driver->nin_ocr_match_score ?? 0,
                        'frsc_ocr_match_score' => $driver->frsc_ocr_match_score ?? 0,
                        'ocr_verification_status' => $driver->ocr_verification_status ?? 'pending',
                        'nin_verified_at' => $driver->nin_verified_at?->format('Y-m-d H:i:s'),
                        'frsc_verified_at' => $driver->frsc_verified_at?->format('Y-m-d H:i:s'),
                        'created_at' => $driver->created_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]);
        }
        
        return response()->json([
            'success' => true,
            'drivers' => $drivers->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'driver_id' => $driver->driver_id,
                    'full_name' => $driver->full_name,
                    'email' => $driver->email,
                    'phone' => $driver->phone,
                    'status' => $driver->status,
                    'verification_status' => $driver->verification_status,
                    'created_at' => $driver->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }
}