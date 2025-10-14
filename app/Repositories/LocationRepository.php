<?php

namespace App\Repositories;

use App\Models\DriverLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Location Repository
 *
 * Handles all data access operations for the DriverLocation model.
 * Provides specialized methods for location management and tracking.
 *
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class LocationRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new DriverLocation();
    }

    /**
     * Get locations by driver ID.
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
     * Get locations by type.
     *
     * @param string $type Location type (origin, residence, birth)
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByType(string $type, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['location_type' => $type],
            ['created_at' => 'desc'],
            $perPage,
            ['driver', 'state', 'localGovernment']
        );
    }

    /**
     * Get primary locations for driver.
     *
     * @param int $driverId Driver ID
     * @return Collection
     */
    public function getPrimaryForDriver(int $driverId): Collection
    {
        return $this->findWhere([
            'driver_id' => $driverId,
            'is_primary' => true
        ], ['*'], ['state', 'localGovernment']);
    }

    /**
     * Get origin locations.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getOrigins(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByType('origin', $perPage);
    }

    /**
     * Get residence locations.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getResidences(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByType('residence', $perPage);
    }

    /**
     * Get birth locations.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getBirthLocations(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByType('birth', $perPage);
    }

    /**
     * Find location by driver and type.
     *
     * @param int $driverId Driver ID
     * @param string $type Location type
     * @return Model|null
     */
    public function findByDriverAndType(int $driverId, string $type): ?Model
    {
        return $this->findOneWhere([
            'driver_id' => $driverId,
            'location_type' => $type
        ], ['*'], ['state', 'localGovernment']);
    }

    /**
     * Get locations by state.
     *
     * @param int $stateId State ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByState(int $stateId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['state_id' => $stateId],
            ['created_at' => 'desc'],
            $perPage,
            ['driver', 'localGovernment']
        );
    }

    /**
     * Get locations by LGA.
     *
     * @param int $lgaId LGA ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByLga(int $lgaId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['lga_id' => $lgaId],
            ['created_at' => 'desc'],
            $perPage,
            ['driver', 'state']
        );
    }

    /**
     * Set primary location for driver.
     *
     * @param int $locationId Location ID
     * @param int $driverId Driver ID
     * @return Model
     */
    public function setAsPrimary(int $locationId, int $driverId): Model
    {
        // First, unset all primary locations for this driver
        $this->newQuery()
            ->where('driver_id', $driverId)
            ->update(['is_primary' => false]);

        // Then set this one as primary
        return $this->update($locationId, ['is_primary' => true]);
    }

    /**
     * Get location statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'origins' => (clone $baseQuery)->where('location_type', 'origin')->count(),
            'residences' => (clone $baseQuery)->where('location_type', 'residence')->count(),
            'births' => (clone $baseQuery)->where('location_type', 'birth')->count(),
            'primary' => (clone $baseQuery)->where('is_primary', true)->count(),
        ];
    }

    /**
     * Delete locations by driver ID.
     *
     * @param int $driverId Driver ID
     * @return int Number of deleted locations
     */
    public function deleteByDriverId(int $driverId): int
    {
        return $this->deleteWhere(['driver_id' => $driverId]);
    }

    /**
     * Get locations by city.
     *
     * @param string $city City name
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByCity(string $city, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['city' => $city],
            ['created_at' => 'desc'],
            $perPage,
            ['driver', 'state', 'localGovernment']
        );
    }
}
