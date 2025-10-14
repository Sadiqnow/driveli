<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Company Repository
 * 
 * Handles all data access operations for the Company model.
 * Provides specialized methods for company-specific queries and operations.
 * 
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class CompanyRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new Company();
    }

    /**
     * Get companies by verification status.
     *
     * @param string $status Verification status
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByVerificationStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->search(
            ['verification_status' => $status],
            ['created_at' => 'desc'],
            $perPage
        );
    }

    /**
     * Get active companies.
     *
     * @param int|null $perPage Number of records per page (null for all)
     * @return Collection|LengthAwarePaginator
     */
    public function getActive(?int $perPage = null)
    {
        return $this->search(
            ['status' => 'active'],
            ['created_at' => 'desc'],
            $perPage
        );
    }

    /**
     * Get pending verification companies.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getPendingVerification(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByVerificationStatus('pending', $perPage);
    }

    /**
     * Get verified companies.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getVerified(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByVerificationStatus('verified', $perPage);
    }

    /**
     * Search companies with advanced filters.
     *
     * @param array $filters Search filters
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchCompanies(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery();

        // Apply search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('company_id', 'LIKE', "%{$search}%");
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

        // Apply date range filters
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find company by email.
     *
     * @param string $email Company email
     * @return Model|null
     */
    public function findByEmail(string $email): ?Model
    {
        return $this->findOneBy('email', $email);
    }

    /**
     * Find company by company ID.
     *
     * @param string $companyId Company ID
     * @return Model|null
     */
    public function findByCompanyId(string $companyId): ?Model
    {
        return $this->findOneBy('company_id', $companyId);
    }

    /**
     * Check if email exists.
     *
     * @param string $email Email to check
     * @param int|null $excludeId Company ID to exclude from check
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
     * Get company statistics.
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
            'verified' => (clone $baseQuery)->where('verification_status', 'verified')->count(),
            'pending_verification' => (clone $baseQuery)->where('verification_status', 'pending')->count(),
            'rejected' => (clone $baseQuery)->where('verification_status', 'rejected')->count(),
            'registered_today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'registered_this_month' => (clone $baseQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    /**
     * Update company verification status.
     *
     * @param int $companyId Company ID
     * @param string $status Verification status
     * @param int $adminId Admin user ID
     * @param string|null $notes Verification notes
     * @return Model
     */
    public function updateVerificationStatus(int $companyId, string $status, int $adminId, ?string $notes = null): Model
    {
        $data = [
            'verification_status' => $status,
            'verified_by' => $adminId,
            'verification_notes' => $notes,
        ];

        if ($status === 'verified') {
            $data['verified_at'] = now();
            $data['status'] = 'active';
        } elseif ($status === 'rejected') {
            $data['rejected_at'] = now();
            $data['rejection_reason'] = $notes;
        }

        return $this->update($companyId, $data);
    }

    /**
     * Bulk update company status.
     *
     * @param array $companyIds Array of company IDs
     * @param string $status New status
     * @return int Number of updated records
     */
    public function bulkUpdateStatus(array $companyIds, string $status): int
    {
        return $this->newQuery()
            ->whereIn('id', $companyIds)
            ->update(['status' => $status, 'updated_at' => now()]);
    }
}
