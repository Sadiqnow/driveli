<?php

namespace App\Repositories;

use App\Models\Drivers as Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Driver Repository
 * 
 * Handles all data access operations for the Driver model.
 * Provides specialized methods for driver-specific queries and operations.
 * 
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class DriverRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new Driver();
    }

    /**
     * Get drivers with verification status filter.
     *
     * @param string $status Verification status (pending, verified, rejected)
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByVerificationStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->search(
            ['verification_status' => $status],
            ['created_at' => 'desc'],
            $perPage,
            ['nationality', 'verifiedBy']
        );
    }

    /**
     * Get active drivers.
     *
     * @param int|null $perPage Number of records per page (null for all)
     * @return Collection|LengthAwarePaginator
     */
    public function getActive(?int $perPage = null)
    {
        return $this->search(
            ['status' => 'active', 'is_active' => true],
            ['created_at' => 'desc'],
            $perPage,
            ['nationality', 'performance']
        );
    }

    /**
     * Get drivers pending verification.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getPendingVerification(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByVerificationStatus('pending', $perPage);
    }

    /**
     * Get verified drivers.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getVerified(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByVerificationStatus('verified', $perPage);
    }

    /**
     * Get drivers by KYC status.
     *
     * @param string $status KYC status (pending, in_progress, completed, rejected)
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByKycStatus(string $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['kyc_status' => $status],
            ['kyc_submitted_at' => 'desc'],
            $perPage,
            ['nationality', 'residenceState', 'residenceLga']
        );
    }

    /**
     * Get drivers with complete KYC pending review.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getKycPendingReview(int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where('kyc_status', 'completed')
            ->where('verification_status', 'pending')
            ->with(['nationality', 'residenceState', 'residenceLga'])
            ->orderBy('kyc_submitted_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Search drivers with advanced filters.
     *
     * @param array $filters Search filters
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchDrivers(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()->with(['nationality', 'verifiedBy', 'performance']);

        // Apply search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
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

        // Apply gender filter
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Apply nationality filter
        if (!empty($filters['nationality_id'])) {
            $query->where('nationality_id', $filters['nationality_id']);
        }

        // Apply date range filters
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // Apply age range filters
        if (!empty($filters['age_min']) || !empty($filters['age_max'])) {
            $this->applyAgeFilter($query, $filters['age_min'] ?? null, $filters['age_max'] ?? null);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get drivers registered within a date range.
     *
     * @param string $from Start date
     * @param string $to End date
     * @param int|null $perPage Number of records per page (null for all)
     * @return Collection|LengthAwarePaginator
     */
    public function getRegisteredBetween(string $from, string $to, ?int $perPage = null)
    {
        $query = $this->newQuery()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get drivers by nationality.
     *
     * @param int $nationalityId Nationality ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByNationality(int $nationalityId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['nationality_id' => $nationalityId],
            ['created_at' => 'desc'],
            $perPage,
            ['nationality']
        );
    }

    /**
     * Get drivers by state of residence.
     *
     * @param int $stateId State ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByResidenceState(int $stateId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['residence_state_id' => $stateId],
            ['created_at' => 'desc'],
            $perPage,
            ['residenceState', 'residenceLga']
        );
    }

    /**
     * Get driver statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'inactive' => (clone $baseQuery)->where('status', 'inactive')->count(),
            'suspended' => (clone $baseQuery)->where('status', 'suspended')->count(),
            'verified' => (clone $baseQuery)->where('verification_status', 'verified')->count(),
            'pending_verification' => (clone $baseQuery)->where('verification_status', 'pending')->count(),
            'rejected' => (clone $baseQuery)->where('verification_status', 'rejected')->count(),
            'kyc_completed' => (clone $baseQuery)->where('kyc_status', 'completed')->count(),
            'kyc_pending' => (clone $baseQuery)->where('kyc_status', 'pending')->count(),
            'registered_today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'registered_this_week' => (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'registered_this_month' => (clone $baseQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    /**
     * Get dashboard statistics (optimized).
     *
     * @return array
     */
    public function getDashboardStats(): array
    {
        return [
            'verified_count' => $this->count(['verification_status' => 'verified']),
            'pending_count' => $this->count(['verification_status' => 'pending']),
            'rejected_count' => $this->count(['verification_status' => 'rejected']),
            'active_count' => $this->count(['status' => 'active', 'is_active' => true]),
            'new_this_month' => $this->getRegisteredBetween(
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString()
            )->count(),
        ];
    }

    /**
     * Get drivers with incomplete profiles.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getIncompleteProfiles(int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where(function ($q) {
                $q->whereNull('profile_picture')
                  ->orWhereNull('nin_document')
                  ->orWhereNull('license_front_image')
                  ->orWhereNull('date_of_birth')
                  ->orWhereNull('gender');
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get drivers with OCR verification status.
     *
     * @param string $status OCR verification status (pending, passed, failed)
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByOcrStatus(string $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['ocr_verification_status' => $status],
            ['created_at' => 'desc'],
            $perPage,
            ['verifiedBy']
        );
    }

    /**
     * Get recently active drivers.
     *
     * @param int $minutes Minutes of inactivity threshold
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getRecentlyActive(int $minutes = 15, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where('last_active_at', '>=', now()->subMinutes($minutes))
            ->where('is_active', true)
            ->orderBy('last_active_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Find driver by email.
     *
     * @param string $email Driver email
     * @return Model|null
     */
    public function findByEmail(string $email): ?Model
    {
        return $this->findOneBy('email', $email);
    }

    /**
     * Find driver by phone.
     *
     * @param string $phone Driver phone
     * @return Model|null
     */
    public function findByPhone(string $phone): ?Model
    {
        return $this->findOneBy('phone', $phone);
    }

    /**
     * Find driver by driver ID.
     *
     * @param string $driverId Driver ID
     * @return Model|null
     */
    public function findByDriverId(string $driverId): ?Model
    {
        return $this->findOneBy('driver_id', $driverId);
    }

    /**
     * Find driver by license number.
     *
     * @param string $licenseNumber License number
     * @return Model|null
     */
    public function findByLicenseNumber(string $licenseNumber): ?Model
    {
        return $this->findOneBy('license_number', $licenseNumber);
    }

    /**
     * Check if email exists.
     *
     * @param string $email Email to check
     * @param int|null $excludeId Driver ID to exclude from check
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = $this->newQuery()->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if phone exists.
     *
     * @param string $phone Phone to check
     * @param int|null $excludeId Driver ID to exclude from check
     * @return bool
     */
    public function phoneExists(string $phone, ?int $excludeId = null): bool
    {
        $query = $this->newQuery()->where('phone', $phone);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Check if license number exists.
     *
     * @param string $licenseNumber License number to check
     * @param int|null $excludeId Driver ID to exclude from check
     * @return bool
     */
    public function licenseNumberExists(string $licenseNumber, ?int $excludeId = null): bool
    {
        $query = $this->newQuery()->where('license_number', $licenseNumber);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Update driver verification status.
     *
     * @param int $driverId Driver ID
     * @param string $status Verification status
     * @param int $adminId Admin user ID
     * @param string|null $notes Verification notes
     * @return Model
     */
    public function updateVerificationStatus(int $driverId, string $status, int $adminId, ?string $notes = null): Model
    {
        $data = [
            'verification_status' => $status,
            'verified_by' => $adminId,
            'verification_notes' => $notes,
        ];

        if ($status === 'verified') {
            $data['verified_at'] = now();
            $data['rejected_at'] = null;
            $data['rejection_reason'] = null;
            $data['status'] = 'active';
            $data['is_active'] = true;
        } elseif ($status === 'rejected') {
            $data['rejected_at'] = now();
            $data['rejection_reason'] = $notes;
            $data['verified_at'] = null;
            $data['status'] = 'inactive';
            $data['is_active'] = false;
        }

        return $this->update($driverId, $data);
    }

    /**
     * Update driver KYC status.
     *
     * @param int $driverId Driver ID
     * @param string $status KYC status
     * @param array $additionalData Additional data to update
     * @return Model
     */
    public function updateKycStatus(int $driverId, string $status, array $additionalData = []): Model
    {
        $data = array_merge(['kyc_status' => $status], $additionalData);

        if ($status === 'completed') {
            $data['kyc_completed_at'] = $data['kyc_completed_at'] ?? now();
        } elseif ($status === 'rejected') {
            $data['kyc_rejected_at'] = $data['kyc_rejected_at'] ?? now();
        }

        return $this->update($driverId, $data);
    }

    /**
     * Bulk update driver status.
     *
     * @param array $driverIds Array of driver IDs
     * @param string $status New status
     * @return int Number of updated records
     */
    public function bulkUpdateStatus(array $driverIds, string $status): int
    {
        return $this->newQuery()
            ->whereIn('id', $driverIds)
            ->update(['status' => $status, 'updated_at' => now()]);
    }

    /**
     * Bulk update verification status.
     *
     * @param array $driverIds Array of driver IDs
     * @param string $status Verification status
     * @param int $adminId Admin user ID
     * @return int Number of updated records
     */
    public function bulkUpdateVerificationStatus(array $driverIds, string $status, int $adminId): int
    {
        $data = [
            'verification_status' => $status,
            'verified_by' => $adminId,
            'updated_at' => now(),
        ];

        if ($status === 'verified') {
            $data['verified_at'] = now();
        } elseif ($status === 'rejected') {
            $data['rejected_at'] = now();
        }

        return $this->newQuery()
            ->whereIn('id', $driverIds)
            ->update($data);
    }

    /**
     * Apply age filter to query.
     *
     * @param Builder $query Query builder instance
     * @param int|null $minAge Minimum age
     * @param int|null $maxAge Maximum age
     * @return void
     */
    protected function applyAgeFilter(Builder $query, ?int $minAge, ?int $maxAge): void
    {
        if ($minAge !== null) {
            $maxDate = now()->subYears($minAge)->toDateString();
            $query->where('date_of_birth', '<=', $maxDate);
        }

        if ($maxAge !== null) {
            $minDate = now()->subYears($maxAge + 1)->addDay()->toDateString();
            $query->where('date_of_birth', '>=', $minDate);
        }
    }

    /**
     * Get drivers with relationships loaded.
     *
     * @param array $driverIds Array of driver IDs
     * @param array $relations Relationships to load
     * @return Collection
     */
    public function getWithRelations(array $driverIds, array $relations = []): Collection
    {
        return $this->newQuery()
            ->whereIn('id', $driverIds)
            ->with($relations)
            ->get();
    }

    /**
     * Soft delete driver.
     *
     * @param int $driverId Driver ID
     * @return bool
     */
    public function softDelete(int $driverId): bool
    {
        return $this->delete($driverId);
    }

    /**
     * Restore soft deleted driver.
     *
     * @param int $driverId Driver ID
     * @return bool
     */
    public function restore(int $driverId): bool
    {
        return $this->model->withTrashed()->find($driverId)?->restore() ?? false;
    }

    /**
     * Get trashed drivers.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getTrashed(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }
}
