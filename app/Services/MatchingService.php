<?php

namespace App\Services;

use App\Models\CompanyRequest;
use App\Models\Driver;
use App\Models\CompanyMatch;
use App\Jobs\MatchDriversJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MatchingService
{
    public function matchDriversForRequest(CompanyRequest $request): Collection
    {
        Log::info('Starting driver matching for request', ['request_id' => $request->id]);

        // Get available drivers based on criteria
        $drivers = $this->findMatchingDrivers($request);

        $matches = collect();

        foreach ($drivers as $driver) {
            $matchScore = $this->calculateMatchScore($request, $driver);

            if ($matchScore >= 70) { // Minimum score threshold
                $match = CompanyMatch::create([
                    'company_request_id' => $request->id,
                    'driver_id' => $driver->id,
                    'match_score' => $matchScore,
                    'matching_criteria' => $this->getMatchingCriteria($request, $driver),
                    'status' => 'pending',
                    'matched_by' => null, // Automated
                ]);

                $matches->push($match);
            }
        }

        Log::info('Driver matching completed', ['request_id' => $request->id, 'matches_found' => $matches->count()]);

        return $matches;
    }

    public function findMatchingDrivers(CompanyRequest $request): Collection
    {
        $query = Driver::where('status', 'active')
            ->where('verification_status', 'verified');

        // Location matching
        if ($request->pickup_location) {
            $query->whereHas('locations', function ($q) use ($request) {
                $q->where('state_id', $request->pickup_state_id)
                  ->where('lga_id', $request->pickup_lga_id);
            });
        }

        // Vehicle type matching
        if ($request->vehicle_type) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        // Experience level
        if ($request->experience_required) {
            $query->where('years_of_experience', '>=', $request->experience_required);
        }

        // Rating threshold
        $query->where('rating', '>=', 3.5);

        return $query->get();
    }

    public function calculateMatchScore(CompanyRequest $request, Driver $driver): float
    {
        $score = 0;
        $totalWeight = 0;

        // Location match (30%)
        if ($this->locationMatches($request, $driver)) {
            $score += 30;
        }
        $totalWeight += 30;

        // Vehicle type match (25%)
        if ($driver->vehicle_type === $request->vehicle_type) {
            $score += 25;
        }
        $totalWeight += 25;

        // Experience match (20%)
        if ($driver->years_of_experience >= $request->experience_required) {
            $score += 20;
        }
        $totalWeight += 20;

        // Rating match (15%)
        $ratingScore = min(15, $driver->rating * 3);
        $score += $ratingScore;
        $totalWeight += 15;

        // Availability match (10%)
        if ($this->isAvailable($driver, $request)) {
            $score += 10;
        }
        $totalWeight += 10;

        return $totalWeight > 0 ? ($score / $totalWeight) * 100 : 0;
    }

    private function locationMatches(CompanyRequest $request, Driver $driver): bool
    {
        return $driver->locations()
            ->where('state_id', $request->pickup_state_id)
            ->where('lga_id', $request->pickup_lga_id)
            ->exists();
    }

    private function isAvailable(Driver $driver, CompanyRequest $request): bool
    {
        // Check if driver has no conflicting bookings
        return true; // Simplified
    }

    private function getMatchingCriteria(CompanyRequest $request, Driver $driver): array
    {
        return [
            'location_match' => $this->locationMatches($request, $driver),
            'vehicle_type_match' => $driver->vehicle_type === $request->vehicle_type,
            'experience_match' => $driver->years_of_experience >= $request->experience_required,
            'rating' => $driver->rating,
            'availability' => $this->isAvailable($driver, $request),
        ];
    }

    public function dispatchMatchingJob(CompanyRequest $request): void
    {
        MatchDriversJob::dispatch($request);
    }
}
