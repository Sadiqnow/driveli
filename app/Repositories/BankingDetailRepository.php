<?php

namespace App\Repositories;

use App\Models\DriverBankingDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Banking Detail Repository
 *
 * Handles all data access operations for the DriverBankingDetail model.
 * Provides specialized methods for banking detail management and verification.
 *
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class BankingDetailRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new DriverBankingDetail();
    }

    /**
     * Get banking details by driver ID.
     *
     * @param int $driverId Driver ID
     * @param array $columns Columns to select
     * @return Collection
     */
    public function getByDriverId(int $driverId, array $columns = ['*']): Collection
    {
        return $this->findBy('driver_id', $driverId, $columns, ['bank']);
    }

    /**
     * Get primary banking detail for driver.
     *
     * @param int $driverId Driver ID
     * @return Model|null
     */
    public function getPrimaryForDriver(int $driverId): ?Model
    {
        return $this->findOneWhere([
            'driver_id' => $driverId,
            'is_primary' => true
        ], ['*'], ['bank']);
    }

    /**
     * Get verified banking details.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getVerified(int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['is_verified' => true],
            ['verified_at' => 'desc'],
            $perPage,
            ['driver', 'bank']
        );
    }

    /**
     * Get unverified banking details.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getUnverified(int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['is_verified' => false],
            ['created_at' => 'desc'],
            $perPage,
            ['driver', 'bank']
        );
    }

    /**
     * Get banking details by bank ID.
     *
     * @param int $bankId Bank ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByBank(int $bankId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['bank_id' => $bankId],
            ['created_at' => 'desc'],
            $perPage,
            ['driver']
        );
    }

    /**
     * Find banking detail by driver and account number.
     *
     * @param int $driverId Driver ID
     * @param string $accountNumber Account number
     * @return Model|null
     */
    public function findByDriverAndAccount(int $driverId, string $accountNumber): ?Model
    {
        return $this->findOneWhere([
            'driver_id' => $driverId,
            'account_number' => $accountNumber
        ], ['*'], ['bank']);
    }

    /**
     * Set banking detail as primary.
     *
     * @param int $bankingDetailId Banking detail ID
     * @param int $driverId Driver ID
     * @return Model
     */
    public function setAsPrimary(int $bankingDetailId, int $driverId): Model
    {
        // First, unset all primary banking details for this driver
        $this->newQuery()
            ->where('driver_id', $driverId)
            ->update(['is_primary' => false]);

        // Then set this one as primary
        return $this->update($bankingDetailId, ['is_primary' => true]);
    }

    /**
     * Verify banking detail.
     *
     * @param int $bankingDetailId Banking detail ID
     * @param int $verifiedBy Admin user ID who verified
     * @return Model
     */
    public function verify(int $bankingDetailId, int $verifiedBy): Model
    {
        return $this->update($bankingDetailId, [
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Unverify banking detail.
     *
     * @param int $bankingDetailId Banking detail ID
     * @return Model
     */
    public function unverify(int $bankingDetailId): Model
    {
        return $this->update($bankingDetailId, [
            'is_verified' => false,
            'verified_at' => null,
        ]);
    }

    /**
     * Get banking details statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'verified' => (clone $baseQuery)->where('is_verified', true)->count(),
            'unverified' => (clone $baseQuery)->where('is_verified', false)->count(),
            'primary' => (clone $baseQuery)->where('is_primary', true)->count(),
            'verified_today' => (clone $baseQuery)->whereDate('verified_at', today())->count(),
            'verified_this_week' => (clone $baseQuery)->whereBetween('verified_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'verified_this_month' => (clone $baseQuery)->whereMonth('verified_at', now()->month)->whereYear('verified_at', now()->year)->count(),
        ];
    }

    /**
     * Get banking details by account name pattern.
     *
     * @param string $pattern Account name pattern
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchByAccountName(string $pattern, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['driver', 'bank'])
            ->where('account_name', 'LIKE', "%{$pattern}%")
            ->orderBy('account_name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Delete banking details by driver ID.
     *
     * @param int $driverId Driver ID
     * @return int Number of deleted banking details
     */
    public function deleteByDriverId(int $driverId): int
    {
        return $this->deleteWhere(['driver_id' => $driverId]);
    }

    /**
     * Check if driver has verified banking detail.
     *
     * @param int $driverId Driver ID
     * @return bool
     */
    public function driverHasVerifiedDetail(int $driverId): bool
    {
        return $this->exists([
            'driver_id' => $driverId,
            'is_verified' => true
        ]);
    }

    /**
     * Get banking details recently verified.
     *
     * @param int $hours Hours threshold
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getRecentlyVerified(int $hours = 24, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['driver', 'bank'])
            ->where('verified_at', '>=', now()->subHours($hours))
            ->orderBy('verified_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Bulk verify banking details.
     *
     * @param array $bankingDetailIds Array of banking detail IDs
     * @param int $verifiedBy Admin user ID
     * @return int Number of updated records
     */
    public function bulkVerify(array $bankingDetailIds, int $verifiedBy): int
    {
        return $this->newQuery()
            ->whereIn('id', $bankingDetailIds)
            ->update([
                'is_verified' => true,
                'verified_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
