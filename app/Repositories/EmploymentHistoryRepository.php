<?php

namespace App\Repositories;

use App\Models\DriverEmploymentHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Employment History Repository
 *
 * Handles all data access operations for the DriverEmploymentHistory model.
 * Provides specialized methods for employment history management.
 *
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class EmploymentHistoryRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new DriverEmploymentHistory();
    }

    /**
     * Get employment history by driver ID.
     *
     * @param int $driverId Driver ID
     * @param array $columns Columns to select
     * @return Collection
     */
    public function getByDriverId(int $driverId, array $columns = ['*']): Collection
    {
        return $this->findBy('driver_id', $driverId, $columns)
            ->sortByDesc('start_date');
    }

    /**
     * Get current employment for driver.
     *
     * @param int $driverId Driver ID
     * @return Model|null
     */
    public function getCurrentForDriver(int $driverId): ?Model
    {
        return $this->findOneWhere([
            'driver_id' => $driverId,
            ['end_date', '=', null]
        ]);
    }

    /**
     * Get previous employment for driver.
     *
     * @param int $driverId Driver ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getPreviousForDriver(int $driverId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['driver_id' => $driverId, ['end_date', '!=', null]],
            ['end_date' => 'desc'],
            $perPage
        );
    }

    /**
     * Get employment history by company name.
     *
     * @param string $companyName Company name
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByCompany(string $companyName, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['company_name' => $companyName],
            ['start_date' => 'desc'],
            $perPage,
            ['driver']
        );
    }

    /**
     * Search employment history by company name pattern.
     *
     * @param string $pattern Company name pattern
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchByCompany(string $pattern, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->where('company_name', 'LIKE', "%{$pattern}%")
            ->orderBy('company_name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get employment history by RC number.
     *
     * @param string $rcNumber RC number
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByRcNumber(string $rcNumber, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['rc_number' => $rcNumber],
            ['start_date' => 'desc'],
            $perPage,
            ['driver']
        );
    }

    /**
     * Get current employments.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getCurrentEmployments(int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereNull('end_date')
            ->orderBy('start_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get employment history within date range.
     *
     * @param string $from Start date
     * @param string $to End date
     * @param int|null $perPage Number of records per page
     * @return Collection|LengthAwarePaginator
     */
    public function getEmployedBetween(string $from, string $to, ?int $perPage = null)
    {
        $query = $this->newQuery()
            ->with('driver')
            ->where('start_date', '<=', $to)
            ->where(function ($q) use ($from) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $from);
            })
            ->orderBy('start_date', 'desc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get employment statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'current' => (clone $baseQuery)->whereNull('end_date')->count(),
            'previous' => (clone $baseQuery)->whereNotNull('end_date')->count(),
            'unique_companies' => $this->newQuery()->distinct('company_name')->count('company_name'),
            'average_duration_months' => $this->getAverageDuration(),
        ];
    }

    /**
     * Get average employment duration in months.
     *
     * @return float
     */
    private function getAverageDuration(): float
    {
        $avgMonths = $this->newQuery()
            ->whereNotNull('end_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(MONTH, start_date, end_date)) as avg_months')
            ->first()
            ->avg_months ?? 0;

        return round($avgMonths, 2);
    }

    /**
     * Get employment history by duration range.
     *
     * @param int $minMonths Minimum duration in months
     * @param int $maxMonths Maximum duration in months
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByDurationRange(int $minMonths, int $maxMonths, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->with('driver')
            ->whereNotNull('end_date')
            ->havingRaw('TIMESTAMPDIFF(MONTH, start_date, end_date) BETWEEN ? AND ?', [$minMonths, $maxMonths])
            ->orderBy('start_date', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Delete employment history by driver ID.
     *
     * @param int $driverId Driver ID
     * @return int Number of deleted employment history records
     */
    public function deleteByDriverId(int $driverId): int
    {
        return $this->deleteWhere(['driver_id' => $driverId]);
    }

    /**
     * Check if driver has current employment.
     *
     * @param int $driverId Driver ID
     * @return bool
     */
    public function driverHasCurrentEmployment(int $driverId): bool
    {
        return $this->exists([
            'driver_id' => $driverId,
            ['end_date', '=', null]
        ]);
    }

    /**
     * Get most common companies.
     *
     * @param int $limit Number of companies to return
     * @return array
     */
    public function getMostCommonCompanies(int $limit = 10): array
    {
        return $this->newQuery()
            ->selectRaw('company_name, COUNT(*) as count')
            ->groupBy('company_name')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->pluck('count', 'company_name')
            ->toArray();
    }

    /**
     * Get employment history with documents.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getWithDocuments(int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with('driver')
            ->whereNotNull('employment_letter_path')
            ->orWhereNotNull('service_certificate_path')
            ->orderBy('start_date', 'desc')
            ->paginate($perPage);
    }
}
