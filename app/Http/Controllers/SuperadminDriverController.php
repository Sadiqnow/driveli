<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverDocument;
use App\Models\DriverPerformance;
use App\Models\DriverNextOfKin;
use App\Models\DriverBankingDetail;
use App\Models\DriverCategoryRequirement;
use App\Models\DriverMatch;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Services\SuperadminActivityLogger;
use App\Services\DriverQueryOptimizationService;
use App\Services\NotificationService;
use App\Services\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SuperadminDriverController extends Controller
{
    protected $optimizationService;
    protected $notificationService;
    protected $driverService;

    public function __construct(
        DriverQueryOptimizationService $optimizationService,
        NotificationService $notificationService,
        DriverService $driverService
    ) {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');
        $this->optimizationService = $optimizationService;
        $this->notificationService = $notificationService;
        $this->driverService = $driverService;
    }

    /**
     * Display a listing of drivers with advanced search, filter, and pagination
     * Includes progressive disclosure, real-time verification, and analytics
     */
    public function index(Request $request)
    {
        // Get comprehensive dashboard statistics
        $stats = $this->getDashboardStats();

        // Prepare advanced filters array
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'verification_status' => $request->get('verification_status'),
            'kyc_status' => $request->get('kyc_status'),
            'experience_level' => $request->get('experience_level'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        // Remove null values
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        // Get optimized paginated results with relationships
        $drivers = $this->getDriverList($filters, $request->get('per_page', 20));

        // Get additional analytics for Superadmin
        $analytics = $this->getDriverAnalytics();

        return view('admin.superadmin.drivers.index', compact('drivers', 'stats', 'filters', 'analytics'));
    }

    /**
     * Get comprehensive dashboard statistics
     */
    private function getDashboardStats()
    {
        try {
            return [
                'total' => Driver::count(),
                'active' => Driver::where('status', 'active')->count(),
                'inactive' => Driver::where('status', 'inactive')->count(),
                'flagged' => Driver::where('status', 'flagged')->count(),
                'verified' => Driver::where('verification_status', 'verified')->count(),
                'kyc_completed' => Driver::where('kyc_status', 'completed')->count(),
                'pending_verification' => Driver::where('verification_status', 'pending')->count(),
                'rejected' => Driver::where('verification_status', 'rejected')->count(),
                'new_this_month' => Driver::where('created_at', '>=', now()->startOfMonth())->count(),
                'verified_today' => Driver::whereDate('verified_at', today())->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get dashboard stats: ' . $e->getMessage());
            return array_fill_keys(['total', 'active', 'inactive', 'flagged', 'verified', 'kyc_completed', 'pending_verification', 'rejected', 'new_this_month', 'verified_today'], 0);
        }
    }

    /**
     * Get optimized driver list with relationships
     */
    private function getDriverList($filters, $perPage = 20)
    {
        $query = Driver::with([
            'verifiedBy:id,name,email',
            'performance:id,driver_id,average_rating,total_jobs_completed,total_earnings',
            'documents' => function($q) {
                $q->select(['id', 'driver_id', 'document_type', 'verification_status']);
            }
        ]);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('surname', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('driver_id', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply verification status filter
        if (!empty($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        // Apply KYC status filter
        if (!empty($filters['kyc_status'])) {
            $query->where('kyc_status', $filters['kyc_status']);
        }

        // Apply experience level filter
        if (!empty($filters['experience_level'])) {
            $this->applyExperienceFilter($query, $filters['experience_level']);
        }

        // Apply date range filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Apply experience level filter
     */
    private function applyExperienceFilter($query, $experienceLevel)
    {
        switch ($experienceLevel) {
            case '1-2 years':
                $query->whereBetween('experience_years', [1, 2]);
                break;
            case '3-5 years':
                $query->whereBetween('experience_years', [3, 5]);
                break;
            case '6-10 years':
                $query->whereBetween('experience_years', [6, 10]);
                break;
            case '10+ years':
                $query->where('experience_years', '>', 10);
                break;
        }
    }

    /**
     * Get driver analytics for Superadmin dashboard
     */
    private function getDriverAnalytics()
    {
        try {
            return [
                'average_rating' => DriverPerformance::avg('average_rating') ?? 0,
                'total_jobs_completed' => DriverPerformance::sum('total_jobs_completed') ?? 0,
                'total_earnings' => DriverPerformance::sum('total_earnings') ?? 0,
                'active_drivers_today' => Driver::where('last_active_at', '>=', today())->count(),
                'online_drivers' => Driver::where('status', 'active')
                    ->where('last_active_at', '>=', now()->subMinutes(30))->count(),
                'verification_trends' => $this->getVerificationTrends(),
                'performance_distribution' => $this->getPerformanceDistribution(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get driver analytics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get verification trends for the last 7 days
     */
    private function getVerificationTrends()
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = Driver::whereDate('verified_at', $date)->count();
            $trends[] = [
                'date' => $date,
                'count' => $count
            ];
        }
        return $trends;
    }

    /**
     * Get performance distribution
     */
    private function getPerformanceDistribution()
    {
        return [
            'excellent' => DriverPerformance::where('average_rating', '>=', 4.5)->count(),
            'good' => DriverPerformance::whereBetween('average_rating', [3.5, 4.4])->count(),
            'average' => DriverPerformance::whereBetween('average_rating', [2.5, 3.4])->count(),
            'poor' => DriverPerformance::where('average_rating', '<', 2.5)->count(),
        ];
    }

    /**
     * Show the form for creating a new driver
     */
    public function create()
    {
        return view('admin.superadmin.drivers.create');
    }

    /**
     * Store a newly created driver in storage
     */
    public function store(StoreDriverRequest $request)
    {
        try {
            DB::beginTransaction();

            // Extract core driver data (Blackbox fields only)
            $coreData = $request->only([
                'first_name', 'middle_name', 'surname', 'email', 'phone', 'phone_2',
                'password', 'status', 'verification_status', 'is_active', 'is_available',
                'kyc_status', 'kyc_retry_count', 'verified_at', 'verified_by', 'verification_notes'
            ]);

            $coreData['driver_id'] = 'DRV-' . strtoupper(uniqid());
            $coreData['password'] = bcrypt($request->password);

            // Create driver in Blackbox core table
            $driver = Driver::create($coreData);

            // Create transactional data in related tables
            $this->createTransactionalDriverData($driver, $request);

            // Calculate and update profile completion
            $driver->update(['profile_completion_percentage' => $driver->getProfileCompletionPercentage()]);

            // Log activity
            SuperadminActivityLogger::logDriverCreation($driver, $request);

            DB::commit();

            return redirect()->route('admin.superadmin.drivers.show', $driver)
                           ->with('success', 'Driver created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Create transactional data for new driver
     */
    private function createTransactionalDriverData(Driver $driver, Request $request)
    {
        // Personal Information (Next of Kin)
        if ($request->filled(['emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship'])) {
            $driver->personalInfo()->create([
                'name' => $request->emergency_contact_name,
                'phone' => $request->emergency_contact_phone,
                'relationship' => $request->emergency_contact_relationship,
                'is_primary' => true,
            ]);
        }

        // Banking Details
        if ($request->filled(['account_number', 'account_name', 'bank_id'])) {
            $driver->bankingDetails()->create([
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_id' => $request->bank_id,
                'is_primary' => true,
                'is_verified' => false,
            ]);
        }

        // Performance Data (initialize with zeros)
        $driver->performance()->create([
            'total_jobs_completed' => 0,
            'average_rating' => 0.00,
            'total_earnings' => 0.00,
        ]);

        // Documents (if uploaded during registration)
        if ($request->hasFile('profile_picture')) {
            $driver->documents()->create([
                'document_type' => 'profile_picture',
                'document_path' => $request->file('profile_picture')->store('drivers/' . $driver->driver_id),
                'verification_status' => 'pending',
            ]);
        }
    }

    /**
     * Display the specified driver with company and employment info
     */
    public function show(Driver $driver)
    {
        // Use optimized service for better performance
        $driver = $this->optimizationService->getDriverDetails($driver->id);

        if (!$driver) {
            abort(404, 'Driver not found');
        }

        return view('admin.superadmin.drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver
     */
    public function edit(Driver $driver)
    {
        return view('admin.superadmin.drivers.edit', compact('driver'));
    }

    /**
     * Update the specified driver in storage
     */
    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        try {
            DB::beginTransaction();

            $oldValues = $driver->toArray();

            // Update core driver data (Blackbox fields only)
            $coreData = $request->only([
                'first_name', 'middle_name', 'surname', 'email', 'phone', 'phone_2',
                'status', 'verification_status', 'is_active', 'is_available',
                'kyc_status', 'kyc_retry_count', 'verified_at', 'verified_by', 'verification_notes'
            ]);

            if ($request->filled('password')) {
                $coreData['password'] = bcrypt($request->password);
            }

            $driver->update($coreData);

            // Update transactional data
            $this->updateTransactionalDriverData($driver, $request);

            // Recalculate profile completion
            $driver->update(['profile_completion_percentage' => $driver->getProfileCompletionPercentage()]);

            // Log activity
            SuperadminActivityLogger::logDriverUpdate($driver, $oldValues, $driver->toArray(), $request);

            DB::commit();

            return redirect()->route('admin.superadmin.drivers.show', $driver)
                           ->with('success', 'Driver updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Update transactional data for driver
     */
    private function updateTransactionalDriverData(Driver $driver, Request $request)
    {
        // Update Personal Information (Next of Kin)
        if ($request->filled(['emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship'])) {
            $driver->personalInfo()->updateOrCreate(
                ['driver_id' => $driver->id],
                [
                    'name' => $request->emergency_contact_name,
                    'phone' => $request->emergency_contact_phone,
                    'relationship' => $request->emergency_contact_relationship,
                    'is_primary' => true,
                ]
            );
        }

        // Update Banking Details
        if ($request->filled(['account_number', 'account_name', 'bank_id'])) {
            $driver->bankingDetails()->updateOrCreate(
                ['driver_id' => $driver->id, 'is_primary' => true],
                [
                    'account_number' => $request->account_number,
                    'account_name' => $request->account_name,
                    'bank_id' => $request->bank_id,
                    'is_verified' => $request->boolean('bank_verified', false),
                ]
            );
        }

        // Update Performance Data (only if provided)
        if ($request->filled(['total_jobs_completed', 'average_rating', 'total_earnings'])) {
            $driver->performance()->updateOrCreate(
                ['driver_id' => $driver->id],
                [
                    'total_jobs_completed' => $request->total_jobs_completed ?? 0,
                    'average_rating' => $request->average_rating ?? 0.00,
                    'total_earnings' => $request->total_earnings ?? 0.00,
                ]
            );
        }

        // Handle document uploads
        if ($request->hasFile('profile_picture')) {
            $driver->documents()->updateOrCreate(
                ['driver_id' => $driver->id, 'document_type' => 'profile_picture'],
                [
                    'document_path' => $request->file('profile_picture')->store('drivers/' . $driver->driver_id),
                    'verification_status' => 'pending',
                ]
            );
        }
    }

    /**
     * Soft delete the specified driver
     */
    public function destroy(Driver $driver)
    {
        try {
            // Log activity before deletion
            SuperadminActivityLogger::logDriverDeletion($driver, request());

            $driver->delete();

            return redirect()->route('admin.superadmin.drivers.index')
                           ->with('success', 'Driver deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete driver: ' . $e->getMessage()]);
        }
    }

    /**
     * Flag driver with reason
     */
    public function flag(Request $request, Driver $driver)
    {
        $request->validate([
            'reason' => 'required|string|max=1000'
        ]);

        try {
            $driver->update(['status' => 'flagged']);

            // Log activity
            SuperadminActivityLogger::logDriverFlagging($driver, $request->reason, $request);

            return response()->json([
                'success' => true,
                'message' => 'Driver flagged successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to flag driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enhanced verification workflow with audit logging and notifications
     */
    public function verify(Request $request, Driver $driver)
    {
        $request->validate([
            'action' => 'required|in:verify,reject,undo',
            'admin_password' => 'required_if:action,verify|required_if:action,reject',
            'verification_notes' => 'nullable|string|max=1000',
            'rejection_reason' => 'required_if:action,reject|string|max=1000'
        ]);

        try {
            DB::beginTransaction();

            $admin = Auth::guard('admin')->user();
            $action = $request->action;
            $notes = $request->verification_notes;

            // Verify admin password for sensitive actions
            if (in_array($action, ['verify', 'reject']) && !password_verify($request->admin_password, $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid admin password.'
                ], 422);
            }

            switch ($action) {
                case 'verify':
                    $driver->update([
                        'verification_status' => 'verified',
                        'verified_at' => now(),
                        'verified_by' => $admin->id,
                        'verification_notes' => $notes,
                        'status' => 'active' // Activate verified drivers
                    ]);

                    // Send verification notification
                    $this->notificationService->sendVerificationNotification($driver, 'verified', $notes);

                    $message = 'Driver verified successfully.';
                    break;

                case 'reject':
                    $driver->update([
                        'verification_status' => 'rejected',
                        'rejected_at' => now(),
                        'rejection_reason' => $request->rejection_reason,
                        'verification_notes' => $notes,
                        'status' => 'inactive' // Deactivate rejected drivers
                    ]);

                    // Send rejection notification
                    $this->notificationService->sendVerificationNotification($driver, 'rejected', $request->rejection_reason);

                    $message = 'Driver rejected successfully.';
                    break;

                case 'undo':
                    $driver->update([
                        'verification_status' => 'pending',
                        'verified_at' => null,
                        'verified_by' => null,
                        'rejected_at' => null,
                        'rejection_reason' => null,
                        'verification_notes' => 'Verification undone: ' . $notes,
                    ]);

                    $message = 'Verification status reset to pending.';
                    break;
            }

            // Log audit activity
            SuperadminActivityLogger::logVerificationAction($driver, $action, $admin, $notes);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'driver' => [
                    'id' => $driver->id,
                    'verification_status' => $driver->fresh()->verification_status,
                    'status' => $driver->fresh()->status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verification action failed: ' . $e->getMessage(), [
                'driver_id' => $driver->id,
                'action' => $request->action,
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk verification actions for Superadmin
     */
    public function bulkVerify(Request $request)
    {
        $request->validate([
            'action' => 'required|in:verify,reject,activate,deactivate,suspend',
            'driver_ids' => 'required|array|min=1',
            'driver_ids.*' => 'exists:drivers,id',
            'admin_password' => 'required',
            'notes' => 'nullable|string|max=1000',
            'rejection_reason' => 'required_if:action,reject'
        ]);

        // Verify admin password
        $admin = Auth::guard('admin')->user();
        if (!password_verify($request->admin_password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid admin password.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $drivers = Driver::whereIn('id', $request->driver_ids)->get();
            $processed = 0;
            $failed = 0;
            $notifications = ['sent' => 0, 'failed' => 0];

            foreach ($drivers as $driver) {
                try {
                    switch ($request->action) {
                        case 'verify':
                            $driver->update([
                                'verification_status' => 'verified',
                                'verified_at' => now(),
                                'verified_by' => $admin->id,
                                'verification_notes' => $request->notes,
                                'status' => 'active'
                            ]);
                            $this->notificationService->sendVerificationNotification($driver, 'verified', $request->notes);
                            break;

                        case 'reject':
                            $driver->update([
                                'verification_status' => 'rejected',
                                'rejected_at' => now(),
                                'rejection_reason' => $request->rejection_reason,
                                'verification_notes' => $request->notes,
                                'status' => 'inactive'
                            ]);
                            $this->notificationService->sendVerificationNotification($driver, 'rejected', $request->rejection_reason);
                            break;

                        case 'activate':
                            $driver->update(['status' => 'active']);
                            break;

                        case 'deactivate':
                            $driver->update(['status' => 'inactive']);
                            break;

                        case 'suspend':
                            $driver->update(['status' => 'suspended']);
                            break;
                    }

                    $processed++;

                    // Log individual action
                    SuperadminActivityLogger::logBulkAction($driver, $request->action, $admin, $request->notes);

                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Bulk action failed for driver ' . $driver->id . ': ' . $e->getMessage());
                }
            }

            DB::commit();

            $actionLabels = [
                'verify' => 'verified',
                'reject' => 'rejected',
                'activate' => 'activated',
                'deactivate' => 'deactivated',
                'suspend' => 'suspended'
            ];

            $message = "Successfully {$actionLabels[$request->action]} {$processed} driver(s).";
            if ($failed > 0) {
                $message .= " {$failed} operations failed.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed' => $processed,
                'failed' => $failed
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Document management for Superadmin
     */
    public function documents(Request $request, Driver $driver)
    {
        $documents = $driver->documents()->with('verifiedBy')->get();

        return view('admin.superadmin.drivers.documents', compact('driver', 'documents'));
    }

    /**
     * Approve or reject document
     */
    public function updateDocumentStatus(Request $request, Driver $driver, DriverDocument $document)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max=1000'
        ]);

        try {
            $admin = Auth::guard('admin')->user();

            $document->update([
                'verification_status' => $request->action === 'approve' ? 'approved' : 'rejected',
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'verification_notes' => $request->notes
            ]);

            // Log activity
            SuperadminActivityLogger::logDocumentAction($document, $request->action, $admin, $request->notes);

            // Send notification
            $this->notificationService->sendDocumentActionNotification(
                $driver,
                $document->document_type,
                $request->action,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => "Document {$request->action}d successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Performance analytics for Superadmin
     */
    public function analytics(Request $request)
    {
        $analytics = [
            'performance_metrics' => $this->getPerformanceAnalytics(),
            'verification_stats' => $this->getVerificationAnalytics(),
            'document_stats' => $this->getDocumentAnalytics(),
            'activity_trends' => $this->getActivityTrends()
        ];

        return view('admin.superadmin.drivers.analytics', compact('analytics'));
    }

    /**
     * Get performance analytics
     */
    private function getPerformanceAnalytics()
    {
        return [
            'average_rating' => DriverPerformance::avg('average_rating') ?? 0,
            'total_jobs' => DriverPerformance::sum('total_jobs_completed') ?? 0,
            'total_earnings' => DriverPerformance::sum('total_earnings') ?? 0,
            'top_performers' => DriverPerformance::with('driver:id,first_name,surname')
                ->orderBy('average_rating', 'desc')
                ->limit(10)
                ->get(),
            'performance_distribution' => $this->getPerformanceDistribution()
        ];
    }

    /**
     * Get verification analytics
     */
    private function getVerificationAnalytics()
    {
        $total = Driver::count();
        $verified = Driver::where('verification_status', 'verified')->count();
        $pending = Driver::where('verification_status', 'pending')->count();
        $rejected = Driver::where('verification_status', 'rejected')->count();

        return [
            'total_drivers' => $total,
            'verified_count' => $verified,
            'pending_count' => $pending,
            'rejected_count' => $rejected,
            'verification_rate' => $total > 0 ? round(($verified / $total) * 100, 2) : 0,
            'rejection_rate' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
            'trends' => $this->getVerificationTrends()
        ];
    }

    /**
     * Get document analytics
     */
    private function getDocumentAnalytics()
    {
        return [
            'total_documents' => DriverDocument::count(),
            'approved_documents' => DriverDocument::where('verification_status', 'approved')->count(),
            'pending_documents' => DriverDocument::where('verification_status', 'pending')->count(),
            'rejected_documents' => DriverDocument::where('verification_status', 'rejected')->count(),
            'document_types' => DriverDocument::selectRaw('document_type, COUNT(*) as count')
                ->groupBy('document_type')
                ->get()
                ->pluck('count', 'document_type')
        ];
    }

    /**
     * Get activity trends
     */
    private function getActivityTrends()
    {
        $trends = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $trends[] = [
                'date' => $date,
                'registrations' => Driver::whereDate('created_at', $date)->count(),
                'verifications' => Driver::whereDate('verified_at', $date)->count(),
                'documents_uploaded' => DriverDocument::whereDate('created_at', $date)->count()
            ];
        }
        return $trends;
    }

    /**
     * Audit trail for Superadmin
     */
    public function audit(Request $request)
    {
        $query = Driver::with(['verifiedBy:id,name,email'])
            ->whereNotNull('verified_at')
            ->orWhereNotNull('updated_at');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('updated_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('updated_at', '<=', $request->date_to);
        }
        if ($request->filled('admin_id')) {
            $query->where('verified_by', $request->admin_id);
        }
        if ($request->filled('action')) {
            // This would need to be implemented with activity logs
        }

        $auditLogs = $query->orderBy('updated_at', 'desc')->paginate(50);

        return view('admin.superadmin.drivers.audit', compact('auditLogs'));
    }

    /**
     * Export driver data for Superadmin
     */
    public function export(Request $request)
    {
        $query = Driver::with(['performance', 'documents', 'verifiedBy']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $drivers = $query->get();

        // Generate CSV or Excel export
        $filename = 'drivers_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($drivers) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Driver ID', 'Name', 'Email', 'Phone', 'Status', 'Verification Status',
                'KYC Status', 'Average Rating', 'Total Jobs', 'Total Earnings',
                'Created At', 'Verified At', 'Verified By'
            ]);

            // CSV data
            foreach ($drivers as $driver) {
                fputcsv($file, [
                    $driver->driver_id,
                    $driver->full_name,
                    $driver->email,
                    $driver->phone,
                    $driver->status,
                    $driver->verification_status,
                    $driver->kyc_status,
                    $driver->performance?->average_rating ?? 0,
                    $driver->performance?->total_jobs_completed ?? 0,
                    $driver->performance?->total_earnings ?? 0,
                    $driver->created_at->format('Y-m-d H:i:s'),
                    $driver->verified_at?->format('Y-m-d H:i:s'),
                    $driver->verifiedBy?->name
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Verification dashboard for Superadmin
     */
    public function verificationDashboard(Request $request)
    {
        // Get drivers requiring verification
        $pendingDrivers = Driver::where('verification_status', 'pending')
            ->with(['documents', 'performance'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get verification statistics
        $stats = [
            'pending' => Driver::where('verification_status', 'pending')->count(),
            'verified' => Driver::where('verification_status', 'verified')->count(),
            'rejected' => Driver::where('verification_status', 'rejected')->count(),
            'total' => Driver::count(),
        ];

        // Get recent verification activities
        $recentActivities = Driver::whereNotNull('verified_at')
            ->with('verifiedBy:id,name')
            ->orderBy('verified_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.superadmin.drivers.verification-dashboard', compact('pendingDrivers', 'stats', 'recentActivities'));
    }
}
