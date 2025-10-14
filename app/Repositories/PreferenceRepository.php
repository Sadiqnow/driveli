<?php

namespace App\Repositories;

use App\Models\DriverPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Preference Repository
 *
 * Handles all data access operations for the DriverPreference model.
 * Provides specialized methods for driver preference management.
 *
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class PreferenceRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new DriverPreference();
    }

    /**
     * Get preference by driver ID.
     *
     * @param int $driverId Driver ID
     * @return Model|null
     */
    public function getByDriverId(int $driverId): ?Model
    {
        return $this->findOneWhere(['driver_id' => $driverId]);
    }

    /**
     * Get drivers by vehicle type preference.
     *
     * @param string $vehicleType Vehicle type
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByVehicleType(string $vehicleType, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereJsonContains('vehicle_types', $vehicleType)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get drivers by experience level.
     *
     * @param string $experienceLevel Experience level
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByExperienceLevel(string $experienceLevel, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['experience_level' => $experienceLevel],
            ['created_at' => 'desc'],
            $perPage,
            ['driver']
        );
    }

    /**
     * Get drivers by years of experience range.
     *
     * @param int $minYears Minimum years
     * @param int $maxYears Maximum years
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByExperienceRange(int $minYears, int $maxYears, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereBetween('years_of_experience', [$minYears, $maxYears])
            ->orderBy('years_of_experience', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get drivers by preferred route.
     *
     * @param string $route Preferred route
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByPreferredRoute(string $route, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereJsonContains('preferred_routes', $route)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get drivers by special skill.
     *
     * @param string $skill Special skill
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getBySpecialSkill(string $skill, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereJsonContains('special_skills', $skill)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get drivers available during specific hours.
     *
     * @param string $startTime Start time (HH:MM)
     * @param string $endTime End time (HH:MM)
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getAvailableDuringHours(string $startTime, string $endTime, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereJsonContains('working_hours->start', '>=', $startTime)
            ->whereJsonContains('working_hours->end', '<=', $endTime)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get preference statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'experience_levels' => $this->newQuery()
                ->selectRaw('experience_level, COUNT(*) as count')
                ->groupBy('experience_level')
                ->pluck('count', 'experience_level')
                ->toArray(),
            'average_experience_years' => round($baseQuery->avg('years_of_experience'), 1),
            'vehicle_types' => $this->getVehicleTypeStats(),
            'special_skills' => $this->getSpecialSkillsStats(),
        ];
    }

    /**
     * Get vehicle type statistics.
     *
     * @return array
     */
    private function getVehicleTypeStats(): array
    {
        $stats = [];
        $records = $this->newQuery()->select('vehicle_types')->get();

        foreach ($records as $record) {
            if (is_array($record->vehicle_types)) {
                foreach ($record->vehicle_types as $type) {
                    $stats[$type] = ($stats[$type] ?? 0) + 1;
                }
            }
        }

        arsort($stats);
        return $stats;
    }

    /**
     * Get special skills statistics.
     *
     * @return array
     */
    private function getSpecialSkillsStats(): array
    {
        $stats = [];
        $records = $this->newQuery()->select('special_skills')->get();

        foreach ($records as $record) {
            if (is_array($record->special_skills)) {
                foreach ($record->special_skills as $skill) {
                    $stats[$skill] = ($stats[$skill] ?? 0) + 1;
                }
            }
        }

        arsort($stats);
        return $stats;
    }

    /**
     * Search preferences by multiple criteria.
     *
     * @param array $criteria Search criteria
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchPreferences(array $criteria, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()->with('driver');

        // Experience level filter
        if (!empty($criteria['experience_level'])) {
            $query->where('experience_level', $criteria['experience_level']);
        }

        // Years of experience range
        if (!empty($criteria['min_experience'])) {
            $query->where('years_of_experience', '>=', $criteria['min_experience']);
        }
        if (!empty($criteria['max_experience'])) {
            $query->where('years_of_experience', '<=', $criteria['max_experience']);
        }

        // Vehicle types filter
        if (!empty($criteria['vehicle_types'])) {
            foreach ($criteria['vehicle_types'] as $type) {
                $query->whereJsonContains('vehicle_types', $type);
            }
        }

        // Preferred routes filter
        if (!empty($criteria['preferred_routes'])) {
            foreach ($criteria['preferred_routes'] as $route) {
                $query->whereJsonContains('preferred_routes', $route);
            }
        }

        // Special skills filter
        if (!empty($criteria['special_skills'])) {
            foreach ($criteria['special_skills'] as $skill) {
                $query->whereJsonContains('special_skills', $skill);
            }
        }

        // Working hours filter
        if (!empty($criteria['working_hours'])) {
            $hours = $criteria['working_hours'];
            if (!empty($hours['start'])) {
                $query->where('working_hours->start', '>=', $hours['start']);
            }
            if (!empty($hours['end'])) {
                $query->where('working_hours->end', '<=', $hours['end']);
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * Delete preference by driver ID.
     *
     * @param int $driverId Driver ID
     * @return int Number of deleted preferences
     */
    public function deleteByDriverId(int $driverId): int
    {
        return $this->deleteWhere(['driver_id' => $driverId]);
    }

    /**
     * Check if driver has preferences set.
     *
     * @param int $driverId Driver ID
     * @return bool
     */
    public function driverHasPreferences(int $driverId): bool
    {
        return $this->exists(['driver_id' => $driverId]);
    }

    /**
     * Get unique experience levels.
     *
     * @return array
     */
    public function getUniqueExperienceLevels(): array
    {
        return $this->newQuery()
            ->select('experience_level')
            ->distinct()
            ->pluck('experience_level')
            ->toArray();
    }

    /**
     * Get unique vehicle types across all preferences.
     *
     * @return array
     */
    public function getUniqueVehicleTypes(): array
    {
        $types = [];
        $records = $this->newQuery()->select('vehicle_types')->get();

        foreach ($records as $record) {
            if (is_array($record->vehicle_types)) {
                $types = array_merge($types, $record->vehicle_types);
            }
        }

        return array_unique($types);
    }
}
