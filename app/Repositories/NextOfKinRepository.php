<?php

namespace App\Repositories;

use App\Models\DriverNextOfKin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Next of Kin Repository
 *
 * Handles all data access operations for the DriverNextOfKin model.
 * Provides specialized methods for next of kin management.
 *
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class NextOfKinRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new DriverNextOfKin();
    }

    /**
     * Get next of kin by driver ID.
     *
     * @param int $driverId Driver ID
     * @param array $columns Columns to select
     * @return Collection
     */
    public function getByDriverId(int $driverId, array $columns = ['*']): Collection
    {
        return $this->findBy('driver_id', $driverId, $columns);
    }

    /**
     * Get primary next of kin for driver.
     *
     * @param int $driverId Driver ID
     * @return Model|null
     */
    public function getPrimaryForDriver(int $driverId): ?Model
    {
        return $this->findOneWhere([
            'driver_id' => $driverId,
            'is_primary' => true
        ]);
    }

    /**
     * Get next of kin by relationship type.
     *
     * @param string $relationship Relationship type
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByRelationship(string $relationship, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['relationship' => $relationship],
            ['created_at' => 'desc'],
            $perPage,
            ['driver']
        );
    }

    /**
     * Set next of kin as primary.
     *
     * @param int $nextOfKinId Next of kin ID
     * @param int $driverId Driver ID
     * @return Model
     */
    public function setAsPrimary(int $nextOfKinId, int $driverId): Model
    {
        // First, unset all primary next of kin for this driver
        $this->newQuery()
            ->where('driver_id', $driverId)
            ->update(['is_primary' => false]);

        // Then set this one as primary
        return $this->update($nextOfKinId, ['is_primary' => true]);
    }

    /**
     * Search next of kin by name.
     *
     * @param string $name Name pattern
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchByName(string $name, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->where('name', 'LIKE', "%{$name}%")
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Search next of kin by phone.
     *
     * @param string $phone Phone pattern
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchByPhone(string $phone, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->where('phone', 'LIKE', "%{$phone}%")
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get next of kin statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'primary' => (clone $baseQuery)->where('is_primary', true)->count(),
            'relationships' => $this->newQuery()
                ->selectRaw('relationship, COUNT(*) as count')
                ->groupBy('relationship')
                ->pluck('count', 'relationship')
                ->toArray(),
        ];
    }

    /**
     * Delete next of kin by driver ID.
     *
     * @param int $driverId Driver ID
     * @return int Number of deleted next of kin records
     */
    public function deleteByDriverId(int $driverId): int
    {
        return $this->deleteWhere(['driver_id' => $driverId]);
    }

    /**
     * Check if driver has primary next of kin.
     *
     * @param int $driverId Driver ID
     * @return bool
     */
    public function driverHasPrimary(int $driverId): bool
    {
        return $this->exists([
            'driver_id' => $driverId,
            'is_primary' => true
        ]);
    }

    /**
     * Get next of kin by phone number.
     *
     * @param string $phone Phone number
     * @return Collection
     */
    public function getByPhone(string $phone): Collection
    {
        return $this->findBy('phone', $phone, ['*'], ['driver']);
    }

    /**
     * Get unique relationships.
     *
     * @return array
     */
    public function getUniqueRelationships(): array
    {
        return $this->newQuery()
            ->select('relationship')
            ->distinct()
            ->pluck('relationship')
            ->toArray();
    }
}
