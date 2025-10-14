<?php

namespace App\Repositories;

use App\Models\DriverPerformance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Performance Repository
 *
 * Handles all data access operations for the DriverPerformance model.
 * Provides specialized methods for performance tracking and analytics.
 *
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class PerformanceRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new DriverPerformance();
    }

    /**
     * Get performance by driver ID.
     *
     * @param int $driverId Driver ID
     * @return Model|null
     */
    public function getByDriverId(int $driverId): ?Model
    {
        return $this->findOneWhere(['driver_id' => $driverId]);
    }

    /**
     * Get top performing drivers by rating.
     *
     * @param int $limit Number of drivers to return
     * @return Collection
     */
    public function getTopRated(int $limit = 10): Collection
    {
        return $this->newQuery()
            ->with('driver')
            ->orderBy('average_rating', 'desc')
            ->orderBy('total_ratings', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top performing drivers by jobs completed.
     *
     * @param int $limit Number of drivers to return
     * @return Collection
     */
    public function getMostActive(int $limit = 10): Collection
    {
        return $this->newQuery()
            ->with('driver')
            ->orderBy('total_jobs_completed', 'desc')
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top earning drivers.
     *
     * @param int $limit Number of drivers to return
     * @return Collection
     */
    public function getTopEarners(int $limit = 10): Collection
    {
        return $this->newQuery()
            ->with('driver')
            ->orderBy('total_earnings', 'desc')
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get drivers with low performance.
     *
     * @param float $minRating Minimum rating threshold
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getLowPerformers(float $minRating = 3.0, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->where('average_rating', '<', $minRating)
            ->orderBy('average_rating', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get drivers in specific city.
     *
     * @param string $city City name
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByCity(string $city, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->where('current_city', $city)
            ->orderBy('average_rating', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update driver location.
     *
     * @param int $driverId Driver ID
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param string $city City name
     * @return Model
     */
    public function updateLocation(int $driverId, float $lat, float $lng, string $city): Model
    {
        $performance = $this->getByDriverId($driverId);

        if (!$performance) {
            return $this->create([
                'driver_id' => $driverId,
                'current_location_lat' => $lat,
                'current_location_lng' => $lng,
                'current_city' => $city,
            ]);
        }

        return $this->update($performance->id, [
            'current_location_lat' => $lat,
            'current_location_lng' => $lng,
            'current_city' => $city,
        ]);
    }

    /**
     * Update driver rating.
     *
     * @param int $driverId Driver ID
     * @param float $newRating New rating value
     * @return Model
     */
    public function updateRating(int $driverId, float $newRating): Model
    {
        $performance = $this->getByDriverId($driverId);

        if (!$performance) {
            return $this->create([
                'driver_id' => $driverId,
                'average_rating' => $newRating,
                'total_ratings' => 1,
            ]);
        }

        $currentTotal = $performance->total_ratings;
        $currentAverage = $performance->average_rating;

        $newTotal = $currentTotal + 1;
        $newAverage = (($currentAverage * $currentTotal) + $newRating) / $newTotal;

        return $this->update($performance->id, [
            'average_rating' => round($newAverage, 2),
            'total_ratings' => $newTotal,
        ]);
    }

    /**
     * Increment jobs completed.
     *
     * @param int $driverId Driver ID
     * @param float $earnings Earnings from the job
     * @return Model
     */
    public function incrementJobsCompleted(int $driverId, float $earnings = 0): Model
    {
        $performance = $this->getByDriverId($driverId);

        if (!$performance) {
            return $this->create([
                'driver_id' => $driverId,
                'total_jobs_completed' => 1,
                'total_earnings' => $earnings,
                'last_job_completed_at' => now(),
            ]);
        }

        return $this->update($performance->id, [
            'total_jobs_completed' => $performance->total_jobs_completed + 1,
            'total_earnings' => $performance->total_earnings + $earnings,
            'last_job_completed_at' => now(),
        ]);
    }

    /**
     * Get performance statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total_drivers' => $baseQuery->count(),
            'average_rating' => round($baseQuery->avg('average_rating'), 2),
            'total_jobs_completed' => $baseQuery->sum('total_jobs_completed'),
            'total_earnings' => $baseQuery->sum('total_earnings'),
            'top_rating' => round($baseQuery->max('average_rating'), 2),
            'drivers_with_ratings' => (clone $baseQuery)->where('total_ratings', '>', 0)->count(),
            'active_today' => (clone $baseQuery)->whereDate('last_job_completed_at', today())->count(),
        ];
    }

    /**
     * Get drivers with recent activity.
     *
     * @param int $hours Hours threshold
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getRecentlyActive(int $hours = 24, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->where('last_job_completed_at', '>=', now()->subHours($hours))
            ->orderBy('last_job_completed_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get drivers by rating range.
     *
     * @param float $minRating Minimum rating
     * @param float $maxRating Maximum rating
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByRatingRange(float $minRating, float $maxRating, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereBetween('average_rating', [$minRating, $maxRating])
            ->orderBy('average_rating', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get drivers by earnings range.
     *
     * @param float $minEarnings Minimum earnings
     * @param float $maxEarnings Maximum earnings
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByEarningsRange(float $minEarnings, float $maxEarnings, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereBetween('total_earnings', [$minEarnings, $maxEarnings])
            ->orderBy('total_earnings', 'desc')
            ->paginate($perPage);
    }
}
