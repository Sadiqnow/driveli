<?php

namespace App\Services;

use App\Models\DriverNormalized;
use App\Models\CompanyRequest;
use App\Models\MatchingCriteria;
use App\Models\MatchingLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessDriverMatching;

class DriverMatcherService
{
    protected $redis;
    protected $cacheTtl = 3600; // 1 hour

    public function __construct()
    {
        $this->redis = Cache::store('redis');
    }

    /**
     * Main matching method that returns ranked driver list
     */
    public function findMatchingDrivers(int $companyRequestId, array $criteria = []): array
    {
        try {
            $companyRequest = CompanyRequest::with('company')->findOrFail($companyRequestId);

            // Check cache first
            $cacheKey = "driver_matching_{$companyRequestId}_" . md5(json_encode($criteria));
            $cachedResult = $this->redis->get($cacheKey);

            if ($cachedResult) {
                Log::info("Driver matching cache hit for request {$companyRequestId}");
                return json_decode($cachedResult, true);
            }

            // Get matching criteria
            $matchingCriteria = $this->getMatchingCriteria();

            // Build query for eligible drivers
            $query = $this->buildDriverQuery($companyRequest, $criteria);

            // Get potential drivers
            $drivers = $query->get();

            // Calculate scores for each driver
            $rankedDrivers = $this->calculateDriverScores($drivers, $companyRequest, $matchingCriteria);

            // Sort by final score
            usort($rankedDrivers, function($a, $b) {
                return $b['final_score'] <=> $a['final_score'];
            });

            // Log matching results
            $this->logMatchingResults($companyRequestId, $rankedDrivers);

            // Cache results
            $this->redis->put($cacheKey, json_encode($rankedDrivers), $this->cacheTtl);

            return $rankedDrivers;

        } catch (\Exception $e) {
            Log::error("Driver matching failed for request {$companyRequestId}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'drivers' => []
            ];
        }
    }

    /**
     * Build the base query for eligible drivers
     */
    protected function buildDriverQuery(CompanyRequest $companyRequest, array $criteria): \Illuminate\Database\Eloquent\Builder
    {
        $query = DriverNormalized::query()
            ->where('is_active', true)
            ->where('verification_status', 'verified')
            ->where('availability_status', 'available');

        // Location-based filtering (using PostGIS if available)
        if (isset($criteria['location'])) {
            $query = $this->applyLocationFilter($query, $criteria['location'], $criteria['radius'] ?? 50);
        }

        // License class filter
        if (isset($criteria['license_class'])) {
            $query->where('license_class', $criteria['license_class']);
        }

        // Experience filter
        if (isset($criteria['min_experience'])) {
            $query->where('years_of_experience', '>=', $criteria['min_experience']);
        }

        // Rating filter
        if (isset($criteria['min_rating'])) {
            $query->where('average_rating', '>=', $criteria['min_rating']);
        }

        // Vehicle type filter
        if (isset($criteria['vehicle_type'])) {
            $query->whereJsonContains('vehicle_types', $criteria['vehicle_type']);
        }

        // Skills filter
        if (isset($criteria['required_skills'])) {
            foreach ($criteria['required_skills'] as $skill) {
                $query->whereJsonContains('skills', $skill);
            }
        }

        return $query;
    }

    /**
     * Apply location-based filtering using PostGIS
     */
    protected function applyLocationFilter($query, array $location, float $radiusKm = 50)
    {
        // If PostGIS is available, use spatial queries
        if (DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return $query->whereRaw("ST_DWithin(location, ST_MakePoint(?, ?), ?)",
                [$location['lng'], $location['lat'], $radiusKm * 1000]);
        }

        // Fallback to simple bounding box calculation
        $lat = $location['lat'];
        $lng = $location['lng'];
        $latDelta = $radiusKm / 111.32; // 1 degree lat ≈ 111.32 km
        $lngDelta = $radiusKm / (111.32 * cos(deg2rad($lat)));

        return $query->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
                    ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);
    }

    /**
     * Calculate comprehensive scores for each driver
     */
    protected function calculateDriverScores($drivers, CompanyRequest $companyRequest, $criteria): array
    {
        $rankedDrivers = [];

        foreach ($drivers as $driver) {
            $scores = [];

            // Location score
            $scores['location'] = $this->calculateLocationScore($driver, $companyRequest);

            // License score
            $scores['license'] = $this->calculateLicenseScore($driver, $companyRequest);

            // Experience score
            $scores['experience'] = $this->calculateExperienceScore($driver, $companyRequest);

            // Rating score
            $scores['rating'] = $this->calculateRatingScore($driver);

            // Availability score
            $scores['availability'] = $this->calculateAvailabilityScore($driver);

            // Vehicle type score
            $scores['vehicle'] = $this->calculateVehicleScore($driver, $companyRequest);

            // Skills score
            $scores['skills'] = $this->calculateSkillsScore($driver, $companyRequest);

            // Calculate weighted final score
            $finalScore = $this->calculateWeightedScore($scores, $criteria);

            $rankedDrivers[] = [
                'driver_id' => $driver->id,
                'driver_name' => $driver->first_name . ' ' . $driver->last_name,
                'scores' => $scores,
                'final_score' => $finalScore,
                'match_details' => [
                    'distance_km' => $this->calculateDistance($driver, $companyRequest),
                    'estimated_arrival' => $this->estimateArrivalTime($driver, $companyRequest),
                    'vehicle_types' => $driver->vehicle_types,
                    'skills' => $driver->skills,
                    'rating' => $driver->average_rating,
                    'experience_years' => $driver->years_of_experience
                ]
            ];
        }

        return $rankedDrivers;
    }

    /**
     * Calculate location-based score
     */
    protected function calculateLocationScore($driver, CompanyRequest $companyRequest): float
    {
        $distance = $this->calculateDistance($driver, $companyRequest);

        // Score based on distance (closer = higher score)
        if ($distance <= 5) return 100;
        if ($distance <= 10) return 90;
        if ($distance <= 25) return 75;
        if ($distance <= 50) return 50;
        return max(0, 100 - ($distance - 50) * 2);
    }

    /**
     * Calculate license compatibility score
     */
    protected function calculateLicenseScore($driver, CompanyRequest $companyRequest): float
    {
        // This would be more complex in real implementation
        // For now, return 100 if license is valid and not expired
        if ($driver->license_expiry_date && $driver->license_expiry_date->isFuture()) {
            return 100;
        }
        return 50; // Penalty for expired or missing license
    }

    /**
     * Calculate experience score
     */
    protected function calculateExperienceScore($driver, CompanyRequest $companyRequest): float
    {
        $experience = $driver->years_of_experience ?? 0;
        return min(100, $experience * 10); // 10 points per year, max 100
    }

    /**
     * Calculate rating score
     */
    protected function calculateRatingScore($driver): float
    {
        $rating = $driver->average_rating ?? 3.0;
        return ($rating / 5.0) * 100; // Convert 5-star rating to percentage
    }

    /**
     * Calculate availability score
     */
    protected function calculateAvailabilityScore($driver): float
    {
        return $driver->availability_status === 'available' ? 100 : 0;
    }

    /**
     * Calculate vehicle type compatibility score
     */
    protected function calculateVehicleScore($driver, CompanyRequest $companyRequest): float
    {
        $requestedVehicle = $companyRequest->vehicle_type ?? 'any';
        $driverVehicles = $driver->vehicle_types ?? [];

        if (in_array($requestedVehicle, $driverVehicles) || $requestedVehicle === 'any') {
            return 100;
        }

        return 50; // Partial match
    }

    /**
     * Calculate skills compatibility score
     */
    protected function calculateSkillsScore($driver, CompanyRequest $companyRequest): float
    {
        $requiredSkills = $companyRequest->required_skills ?? [];
        $driverSkills = $driver->skills ?? [];

        if (empty($requiredSkills)) return 100;

        $matchedSkills = array_intersect($requiredSkills, $driverSkills);
        $matchRatio = count($matchedSkills) / count($requiredSkills);

        return $matchRatio * 100;
    }

    /**
     * Calculate weighted final score
     */
    protected function calculateWeightedScore(array $scores, $criteria): float
    {
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($criteria as $criterion) {
            $key = $criterion->criteria_type;
            if (isset($scores[$key])) {
                $weight = $criterion->weight;
                $weightedSum += $scores[$key] * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Calculate distance between driver and company request location
     */
    protected function calculateDistance($driver, CompanyRequest $companyRequest): float
    {
        // Simple haversine formula
        $lat1 = deg2rad($driver->latitude ?? 0);
        $lon1 = deg2rad($driver->longitude ?? 0);
        $lat2 = deg2rad($companyRequest->latitude ?? 0);
        $lon2 = deg2rad($companyRequest->longitude ?? 0);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat/2)**2 + cos($lat1) * cos($lat2) * sin($dlon/2)**2;
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return 6371 * $c; // Earth radius in km
    }

    /**
     * Estimate arrival time based on distance
     */
    protected function estimateArrivalTime($driver, CompanyRequest $companyRequest): string
    {
        $distance = $this->calculateDistance($driver, $companyRequest);
        $avgSpeedKmh = 40; // Assume average speed
        $hours = $distance / $avgSpeedKmh;

        if ($hours < 1) {
            return round($hours * 60) . ' minutes';
        }

        return round($hours, 1) . ' hours';
    }

    /**
     * Get matching criteria from database
     */
    protected function getMatchingCriteria()
    {
        return Cache::remember('matching_criteria', 3600, function () {
            return MatchingCriteria::where('is_active', true)->get();
        });
    }

    /**
     * Log matching results to database
     */
    protected function logMatchingResults(int $companyRequestId, array $rankedDrivers): void
    {
        $logs = [];

        foreach ($rankedDrivers as $driver) {
            $logs[] = [
                'company_request_id' => $companyRequestId,
                'driver_id' => $driver['driver_id'],
                'algorithm_version' => 'v1.0',
                'criteria_scores' => json_encode($driver['scores']),
                'final_score' => $driver['final_score'],
                'matched' => $driver['final_score'] >= 70, // Consider matched if score >= 70
                'execution_time' => 0.001, // Placeholder
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        MatchingLog::insert($logs);
    }

    /**
     * Process matching asynchronously
     */
    public function processMatchingAsync(int $companyRequestId, array $criteria = []): void
    {
        ProcessDriverMatching::dispatch($companyRequestId, $criteria);
    }

    /**
     * Get matching statistics
     */
    public function getMatchingStatistics(array $dateRange = []): array
    {
        $query = MatchingLog::query();

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', $dateRange);
        }

        return [
            'total_matches' => $query->count(),
            'successful_matches' => $query->where('matched', true)->count(),
            'average_score' => $query->avg('final_score'),
            'top_performing_drivers' => $query->select('driver_id', DB::raw('AVG(final_score) as avg_score'))
                ->groupBy('driver_id')
                ->orderBy('avg_score', 'desc')
                ->take(10)
                ->get()
        ];
    }

    /**
     * Clear matching cache
     */
    public function clearCache(): void
    {
        $this->redis->flush();
    }
}
