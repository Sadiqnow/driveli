<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service class for handling driver data retrieval operations
 *
 * This service provides methods for fetching driver data including:
 * - Dashboard statistics and listings
 * - Verification data and status tracking
 * - KYC review data and processing
 * - OCR verification data
 *
 * @package App\Services
 */
class DriverDataService
{
    /**
     * Get dashboard data for driver overview
     *
     * Retrieves paginated driver list with applied filters and comprehensive statistics
     * for the admin dashboard. Includes driver counts by status, verification status,
     * and registration periods.
     *
     * @param array $requestData Request parameters containing:
     *                          - per_page: int (optional, default: 20) - Items per page
     *                          - search: string (optional) - Search term
     *                          - status: string (optional) - Filter by driver status
     *                          - verification_status: string (optional) - Filter by verification status
     *                          - experience_level: string (optional) - Filter by experience level
     *
     * @return array Returns array with 'drivers' (paginated collection) and 'stats' (statistics array)
     *
     * @example
     * ```php
     * $service = new DriverDataService();
     * $data = $service->getDashboardData(['per_page' => 10, 'status' => 'active']);
     * // Returns: ['drivers' => PaginatedCollection, 'stats' => ['total_drivers' => 150, ...]]
     * ```
     */
    public function getDashboardData(array $requestData = []): array
    {
        $query = Driver::query()->forAdminList();

        // Apply filters
        $this->applyFilters($query, $requestData);

        // Get stats
        $stats = $this->getDashboardStats();

        // Get drivers with pagination
        $perPage = $requestData['per_page'] ?? 20;
        $drivers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return [
            'drivers' => $drivers,
            'stats' => $stats
        ];
    }

    /**
     * Get verification data
     */
    public function getVerificationData(array $requestData = []): array
    {
        $query = Driver::forAdminList();

        // Default to pending verification if no status specified
        $verificationType = $requestData['type'] ?? 'pending';

        switch ($verificationType) {
            case 'pending':
                $query->where('verification_status', 'pending');
                break;
            case 'verified':
                $query->where('verification_status', 'verified');
                break;
            case 'rejected':
                $query->where('verification_status', 'rejected');
                break;
            default:
                $query->whereIn('verification_status', ['pending', 'verified', 'rejected']);
        }

        // Apply search
        if (isset($requestData['search'])) {
            $this->applySearch($query, $requestData['search']);
        }

        $drivers = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get verification counts
        $counts = [
            'pending' => Driver::where('verification_status', 'pending')->count(),
            'verified' => Driver::where('verification_status', 'verified')->count(),
            'rejected' => Driver::where('verification_status', 'rejected')->count(),
        ];

        return [
            'drivers' => $drivers,
            'verification_type' => $verificationType,
            'counts' => $counts
        ];
    }

    /**
     * Get verification dashboard data
     */
    public function getVerificationDashboardData(array $requestData = []): array
    {
        // Get pending drivers with complete details for verification
        $query = Driver::where('verification_status', 'pending')
                      ->where('kyc_status', 'completed')
                      ->with(['documents', 'nationality', 'verifiedBy']);

        // Apply search
        if (isset($requestData['search'])) {
            $this->applySearch($query, $requestData['search']);
        }

        // Order by priority: new registrations first, then by creation date
        $pendingDrivers = $query->orderByRaw('CASE WHEN created_at >= ? THEN 0 ELSE 1 END', [now()->subHours(24)])
                               ->orderBy('created_at', 'asc')
                               ->limit(20)
                               ->get();

        // Calculate verification statistics
        $stats = [
            'pending_count' => Driver::where('verification_status', 'pending')->count(),
            'verified_today' => Driver::where('verification_status', 'verified')
                                     ->whereDate('verified_at', today())
                                     ->count(),
            'avg_processing_time' => $this->calculateAverageVerificationTime(),
        ];

        return [
            'pending_drivers' => $pendingDrivers,
            'stats' => $stats
        ];
    }

    /**
     * Get OCR verification data
     */
    public function getOcrVerificationData(array $requestData = []): array
    {
        $query = Driver::forDocumentVerification();

        // Filter by OCR verification status
        $ocrStatus = $requestData['ocr_status'] ?? 'pending';
        if ($ocrStatus !== 'all') {
            $query->where('ocr_verification_status', $ocrStatus);
        }

        // Apply search
        if (isset($requestData['search'])) {
            $this->applySearch($query, $requestData['search']);
        }

        $drivers = $query->with(['verifiedBy'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

        // Get statistics
        $stats = [
            'pending' => Driver::where('ocr_verification_status', 'pending')->count(),
            'passed' => Driver::where('ocr_verification_status', 'passed')->count(),
            'failed' => Driver::where('ocr_verification_status', 'failed')->count(),
            'total' => Driver::count()
        ];

        return [
            'drivers' => $drivers,
            'stats' => $stats
        ];
    }

    /**
     * Get KYC review data
     */
    public function getKycReviewData(array $requestData = []): array
    {
        $query = Driver::select([
            'id', 'driver_id', 'first_name', 'surname', 'email', 'phone',
            'kyc_status', 'kyc_step', 'kyc_submitted_at', 'kyc_completed_at',
            'kyc_retry_count', 'profile_completion_percentage', 'created_at'
        ]);

        // Filter by KYC status
        $kycStatus = $requestData['kyc_status'] ?? null;
        if ($kycStatus) {
            $query->where('kyc_status', $kycStatus);
        } else {
            // Default to completed KYC submissions awaiting review
            $query->where('kyc_status', 'completed')
                  ->where('verification_status', 'pending');
        }

        // Apply search
        if (isset($requestData['search'])) {
            $this->applySearch($query, $requestData['search']);
        }

        // Date range filter
        if (isset($requestData['date_from'])) {
            $query->whereDate('kyc_submitted_at', '>=', $requestData['date_from']);
        }
        if (isset($requestData['date_to'])) {
            $query->whereDate('kyc_submitted_at', '<=', $requestData['date_to']);
        }

        $drivers = $query->orderBy('kyc_submitted_at', 'desc')->paginate(20);

        // Get KYC statistics
        $stats = [
            'pending_review' => Driver::where('kyc_status', 'completed')
                                    ->where('verification_status', 'pending')
                                    ->count(),
            'approved' => Driver::where('verification_status', 'verified')
                                ->where('kyc_status', 'completed')
                                ->count(),
            'rejected' => Driver::where('kyc_status', 'rejected')->count(),
            'in_progress' => Driver::where('kyc_status', 'in_progress')->count(),
        ];

        return [
            'drivers' => $drivers,
            'stats' => $stats
        ];
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        // Search functionality
        if (isset($filters['search'])) {
            $this->applySearch($query, $filters['search']);
        }

        // Status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Verification status filter
        if (isset($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        // Experience level filter
        if (isset($filters['experience_level'])) {
            try {
                $query->where('experience_level', $filters['experience_level']);
            } catch (\Exception $e) {
                // Column doesn't exist yet, skip this filter
            }
        }

        // Handle JSON requests for OCR dashboard
        if (isset($filters['format']) && $filters['format'] === 'json') {
            if (isset($filters['include_ocr']) && $filters['include_ocr'] === 'true') {
                // For OCR verification dashboard - get all drivers with OCR data
                // Additional OCR-specific logic can be added here
            }
        }
    }

    /**
     * Apply search to query
     */
    private function applySearch($query, string $search): void
    {
        $query->where(function($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
              ->orWhere('surname', 'LIKE', "%{$search}%")
              ->orWhere('phone', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('driver_id', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        return [
            'total_drivers' => Driver::count(),
            'active_drivers' => Driver::where('status', 'active')->count(),
            'inactive_drivers' => Driver::where('status', 'inactive')->count(),
            'suspended_drivers' => Driver::where('status', 'suspended')->count(),
            'verified_drivers' => Driver::where('verification_status', 'verified')->count(),
            'pending_verification' => Driver::where('verification_status', 'pending')->count(),
            'rejected_drivers' => Driver::where('verification_status', 'rejected')->count(),
            'drivers_registered_today' => Driver::whereDate('created_at', today())->count(),
            'drivers_registered_this_week' => Driver::where('created_at', '>=', now()->startOfWeek())->count(),
            'drivers_registered_this_month' => Driver::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Calculate average verification processing time in minutes
     */
    private function calculateAverageVerificationTime(): int
    {
        $recentVerifications = Driver::where('verification_status', 'verified')
                                   ->whereNotNull('verified_at')
                                   ->whereDate('verified_at', '>=', now()->subDays(7))
                                   ->get(['created_at', 'verified_at']);

        if ($recentVerifications->isEmpty()) {
            return 0;
        }

        $totalMinutes = $recentVerifications->sum(function($driver) {
            return $driver->created_at->diffInMinutes($driver->verified_at);
        });

        return round($totalMinutes / $recentVerifications->count());
    }
}
