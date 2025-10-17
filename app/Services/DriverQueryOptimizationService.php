<?php

namespace App\Services;

use App\Models\Drivers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service for optimizing driver queries and reducing N+1 problems
 */
class DriverQueryOptimizationService
{
    /**
     * Cache TTL for different types of data
     */
    const CACHE_TTL_STATS = 300; // 5 minutes
    const CACHE_TTL_LISTS = 120; // 2 minutes
    const CACHE_TTL_DETAILS = 600; // 10 minutes

    /**
     * Get optimized driver statistics for dashboard
     */
    public function getDashboardStats(): array
    {
        try {
            return Cache::remember('driver_dashboard_stats', self::CACHE_TTL_STATS, function () {
                return [
                    'total' => Drivers::count(),
                    'active' => Drivers::where('status', 'active')->count(),
                    'inactive' => Drivers::where('status', 'inactive')->count(),
                    'flagged' => Drivers::where('status', 'flagged')->count(),
                    'verified' => Drivers::where('verification_status', 'verified')->count(),
                    'kyc_completed' => Drivers::where('kyc_status', 'completed')->count(),
                ];
            });
        } catch (\Exception $e) {
            // Fallback if cache/database fails
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'flagged' => 0,
                'verified' => 0,
                'kyc_completed' => 0,
            ];
        }
    }

    /**
     * Get optimized driver list for admin with pagination
     */
    public function getAdminDriverList(array $filters = [], int $perPage = 25): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        try {
            $cacheKey = 'admin_driver_list_' . md5(serialize($filters) . $perPage);

            return Cache::remember($cacheKey, self::CACHE_TTL_LISTS, function () use ($filters, $perPage) {
                $query = Drivers::query()
                    ->with([
                        'nationality:id,name',
                        'verifiedBy:id,name,email',
                        'performance:id,driver_id,average_rating,total_jobs_completed'
                    ])
                    ->select([
                        'id', 'driver_id', 'first_name', 'middle_name', 'surname', 'nickname',
                        'email', 'phone', 'status', 'verification_status', 'is_active',
                        'created_at', 'verified_at', 'profile_picture', 'kyc_status'
                    ]);

                // Apply filters efficiently
                $this->applyFilters($query, $filters);

                return $query->orderBy('created_at', 'desc')->paginate($perPage);
            });
        } catch (\Exception $e) {
            // Fallback if cache/database fails
            return Drivers::query()
                ->select([
                    'id', 'driver_id', 'first_name', 'middle_name', 'surname', 'nickname',
                    'email', 'phone', 'status', 'verification_status', 'is_active',
                    'created_at', 'verified_at', 'profile_picture', 'kyc_status'
                ])
                ->paginate($perPage);
        }
    }

    /**
     * Get driver details with optimized relationships
     */
    public function getDriverDetails(int $driverId): ?Drivers
    {
        $cacheKey = "driver_details_{$driverId}";

        return Cache::remember($cacheKey, self::CACHE_TTL_DETAILS, function () use ($driverId) {
            return Drivers::with([
                'nationality:id,name,code',
                'verifiedBy:id,name,email',
                'originLocation.state:id,name',
                'originLocation.lga:id,name',
                'residenceLocation.state:id,name',
                'residenceLocation.lga:id,name',
                'primaryBankingDetail.bank:id,name,code',
                'primaryNextOfKin:id,name,relationship,phone',
                'performance:id,total_jobs_completed,average_rating,total_earnings',
                'documents' => function ($query) {
                    $query->select(['id', 'driver_id', 'document_type', 'verification_status', 'created_at'])
                          ->limit(20);
                }
            ])
            ->select([
                'id', 'driver_id', 'first_name', 'middle_name', 'surname', 'nickname',
                'email', 'phone', 'date_of_birth', 'gender', 'nationality_id',
                'status', 'verification_status', 'is_active', 'verified_by',
                'profile_picture', 'created_at', 'updated_at', 'kyc_status',
                'license_number', 'license_class', 'license_expiry_date'
            ])
            ->find($driverId);
        });
    }

    /**
     * Get drivers for bulk operations (minimal data)
     */
    public function getDriversForBulkOperations(array $driverIds): \Illuminate\Database\Eloquent\Collection
    {
        return Drivers::whereIn('id', $driverIds)
            ->select(['id', 'driver_id', 'first_name', 'surname', 'email', 'status', 'verification_status'])
            ->get();
    }

    /**
     * Get drivers for matching system (optimized for performance)
     */
    public function getDriversForMatching(array $criteria = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Drivers::verified()
            ->available()
            ->with([
                'preferences:id,driver_id,preferred_work_areas,vehicle_type_preference',
                'performance:id,driver_id,average_rating,total_jobs_completed',
                'residenceState:id,name',
                'residenceLga:id,name'
            ])
            ->select([
                'id', 'driver_id', 'first_name', 'surname', 'phone',
                'license_class', 'last_active_at', 'residence_state_id', 'residence_lga_id',
                'vehicle_types', 'work_regions'
            ]);

        // Apply matching criteria efficiently
        if (!empty($criteria['state_id'])) {
            $query->where('residence_state_id', $criteria['state_id']);
        }

        if (!empty($criteria['lga_id'])) {
            $query->where('residence_lga_id', $criteria['lga_id']);
        }

        if (!empty($criteria['vehicle_type'])) {
            $query->whereJsonContains('vehicle_types', $criteria['vehicle_type']);
        }

        return $query->limit(100)->get(); // Limit for performance
    }

    /**
     * Clear all driver-related caches
     */
    public function clearDriverCaches(): void
    {
        Cache::forget('driver_dashboard_stats');

        // For Redis cache store, we can use tags if available
        if (method_exists(Cache::store(), 'tags')) {
            Cache::tags(['drivers'])->flush();
        } else {
            // Fallback: clear specific known keys
            // In production, consider using cache tags or a more sophisticated cache management system
            Cache::forget('driver_dashboard_stats');
        }
    }

    /**
     * Apply filters to query efficiently
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('driver_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        if (!empty($filters['kyc_status'])) {
            $query->where('kyc_status', $filters['kyc_status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Get paginated results with cursor-based pagination for better performance
     */
    public function getDriversCursorPaginated(array $filters = [], int $perPage = 25): \Illuminate\Pagination\CursorPaginator
    {
        $query = Drivers::query()
            ->with([
                'nationality:id,name',
                'verifiedBy:id,name'
            ])
            ->select([
                'id', 'driver_id', 'first_name', 'surname', 'email',
                'status', 'verification_status', 'created_at'
            ]);

        $this->applyFilters($query, $filters);

        return $query->orderBy('id')->cursorPaginate($perPage);
    }
}
