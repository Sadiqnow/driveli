<?php

namespace App\Repositories;

use App\Models\Verification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Verification Repository
 * 
 * Handles all data access operations for the Verification model.
 * Provides specialized methods for verification tracking and management.
 * 
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class VerificationRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new Verification();
    }

    /**
     * Get verifications by driver ID.
     *
     * @param int $driverId Driver ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByDriverId(int $driverId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['driver_id' => $driverId],
            ['created_at' => 'desc'],
            $perPage,
            ['verifiedBy']
        );
    }

    /**
     * Get verifications by type.
     *
     * @param string $type Verification type (nin, bvn, frsc, etc.)
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByType(string $type, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['verification_type' => $type],
            ['created_at' => 'desc'],
            $perPage,
            ['driver', 'verifiedBy']
        );
    }

    /**
     * Get verifications by status.
     *
     * @param string $status Verification status
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByStatus(string $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['status' => $status],
            ['created_at' => 'desc'],
            $perPage,
            ['driver', 'verifiedBy']
        );
    }

    /**
     * Get pending verifications.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getPending(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByStatus('pending', $perPage);
    }

    /**
     * Get successful verifications.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getSuccessful(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByStatus('success', $perPage);
    }

    /**
     * Get failed verifications.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getFailed(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByStatus('failed', $perPage);
    }

    /**
     * Find verification by driver and type.
     *
     * @param int $driverId Driver ID
     * @param string $type Verification type
     * @return Model|null
     */
    public function findByDriverAndType(int $driverId, string $type): ?Model
    {
        return $this->findOneWhere([
            'driver_id' => $driverId,
            'verification_type' => $type
        ], ['*'], ['verifiedBy']);
    }

    /**
     * Get latest verification for driver and type.
     *
     * @param int $driverId Driver ID
     * @param string $type Verification type
     * @return Model|null
     */
    public function getLatestByDriverAndType(int $driverId, string $type): ?Model
    {
        return $this->newQuery()
            ->where('driver_id', $driverId)
            ->where('verification_type', $type)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Create verification record.
     *
     * @param array $data Verification data
     * @return Model
     */
    public function createVerification(array $data): Model
    {
        return $this->create(array_merge($data, [
            'verification_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    /**
     * Update verification status.
     *
     * @param int $verificationId Verification ID
     * @param string $status New status
     * @param array $additionalData Additional data to update
     * @return Model
     */
    public function updateStatus(int $verificationId, string $status, array $additionalData = []): Model
    {
        $data = array_merge(['status' => $status], $additionalData);

        if ($status === 'success') {
            $data['verified_at'] = $data['verified_at'] ?? now();
        } elseif ($status === 'failed') {
            $data['failed_at'] = $data['failed_at'] ?? now();
        }

        return $this->update($verificationId, $data);
    }

    /**
     * Get verifications within date range.
     *
     * @param string $from Start date
     * @param string $to End date
     * @param int|null $perPage Number of records per page
     * @return Collection|LengthAwarePaginator
     */
    public function getVerifiedBetween(string $from, string $to, ?int $perPage = null)
    {
        $query = $this->newQuery()
            ->whereDate('verification_date', '>=', $from)
            ->whereDate('verification_date', '<=', $to)
            ->with(['driver', 'verifiedBy'])
            ->orderBy('verification_date', 'desc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get verification statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'success' => (clone $baseQuery)->where('status', 'success')->count(),
            'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
            'today' => (clone $baseQuery)->whereDate('verification_date', today())->count(),
            'this_week' => (clone $baseQuery)->whereBetween('verification_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => (clone $baseQuery)->whereMonth('verification_date', now()->month)->whereYear('verification_date', now()->year)->count(),
        ];
    }

    /**
     * Get verification statistics by type.
     *
     * @return array
     */
    public function getStatisticsByType(): array
    {
        return $this->newQuery()
            ->selectRaw('verification_type, status, COUNT(*) as count')
            ->groupBy('verification_type', 'status')
            ->get()
            ->groupBy('verification_type')
            ->map(function ($items) {
                return $items->pluck('count', 'status')->toArray();
            })
            ->toArray();
    }

    /**
     * Check if driver has successful verification of type.
     *
     * @param int $driverId Driver ID
     * @param string $type Verification type
     * @return bool
     */
    public function hasSuccessfulVerification(int $driverId, string $type): bool
    {
        return $this->exists([
            'driver_id' => $driverId,
            'verification_type' => $type,
            'status' => 'success'
        ]);
    }

    /**
     * Get verifications by admin user.
     *
     * @param int $adminId Admin user ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByAdmin(int $adminId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['verified_by' => $adminId],
            ['created_at' => 'desc'],
            $perPage,
            ['driver']
        );
    }

    /**
     * Get recent verifications.
     *
     * @param int $hours Hours threshold
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getRecent(int $hours = 24, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where('verification_date', '>=', now()->subHours($hours))
            ->with(['driver', 'verifiedBy'])
            ->orderBy('verification_date', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get average verification time by type.
     *
     * @param string $type Verification type
     * @return float Average time in minutes
     */
    public function getAverageVerificationTime(string $type): float
    {
        $avgMinutes = $this->newQuery()
            ->where('verification_type', $type)
            ->where('status', 'success')
            ->whereNotNull('verified_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, verified_at)) as avg_minutes')
            ->first()
            ->avg_minutes ?? 0;

        return round($avgMinutes, 2);
    }

    /**
     * Get verification success rate by type.
     *
     * @param string $type Verification type
     * @return float Success rate percentage
     */
    public function getSuccessRate(string $type): float
    {
        $total = $this->count(['verification_type' => $type]);
        
        if ($total === 0) {
            return 0;
        }

        $successful = $this->count([
            'verification_type' => $type,
            'status' => 'success'
        ]);

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Delete verifications by driver ID.
     *
     * @param int $driverId Driver ID
     * @return int Number of deleted verifications
     */
    public function deleteByDriverId(int $driverId): int
    {
        return $this->deleteWhere(['driver_id' => $driverId]);
    }

    /**
     * Get verifications requiring retry.
     *
     * @param int $maxRetries Maximum retry attempts
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getRequiringRetry(int $maxRetries = 3, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where('status', 'failed')
            ->where('retry_count', '<', $maxRetries)
            ->with(['driver'])
            ->orderBy('created_at', 'asc');

        return $query->paginate($perPage);
    }

    /**
     * Increment retry count.
     *
     * @param int $verificationId Verification ID
     * @return Model
     */
    public function incrementRetryCount(int $verificationId): Model
    {
        $verification = $this->findOrFail($verificationId);
        $verification->increment('retry_count');
        
        return $verification->fresh();
    }
}
