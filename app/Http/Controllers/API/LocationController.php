<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\LocalGovernment;
use App\Models\Nationality;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Location API Controller
 * 
 * Handles API requests for states, local governments, and nationality data
 * for dynamic form population in driver registration forms.
 * 
 * @package App\Http\Controllers\API
 */
class LocationController extends Controller
{
    /**
     * Get all states
     * 
     * @return JsonResponse
     */
    public function getStates(): JsonResponse
    {
        try {
            $states = State::select(['id', 'name', 'code'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'States retrieved successfully',
                'data' => $states,
                'count' => $states->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving states: ' . $e->getMessage(),
                'data' => [],
                'count' => 0
            ], 500);
        }
    }

    /**
     * Get local governments by state ID
     * 
     * @param Request $request
     * @param int|null $stateId
     * @return JsonResponse
     */
    public function getLocalGovernments(Request $request, $stateId = null): JsonResponse
    {
        try {
            // Get state ID from route parameter or request
            $stateId = $stateId ?? $request->input('state_id');

            if (!$stateId) {
                return response()->json([
                    'success' => false,
                    'message' => 'State ID is required',
                    'data' => [],
                    'count' => 0
                ], 400);
            }

            // Verify state exists
            $state = State::find($stateId);
            if (!$state) {
                return response()->json([
                    'success' => false,
                    'message' => 'State not found',
                    'data' => [],
                    'count' => 0
                ], 404);
            }

            $lgas = LocalGovernment::select(['id', 'name', 'state_id'])
                ->where('state_id', $stateId)
                ->orderBy('name')
                ->get();

            // If no LGAs found, try to seed the data automatically
            if ($lgas->isEmpty()) {
                try {
                    \Log::info("No LGAs found for state {$stateId}, attempting to seed data");
                    \Artisan::call('db:seed', ['--class' => 'NigerianStatesLGASeeder']);
                    
                    // Retry the query after seeding
                    $lgas = LocalGovernment::select(['id', 'name', 'state_id'])
                        ->where('state_id', $stateId)
                        ->orderBy('name')
                        ->get();
                        
                    if ($lgas->isNotEmpty()) {
                        \Log::info("Successfully seeded and retrieved {$lgas->count()} LGAs for state {$stateId}");
                    }
                } catch (\Exception $seedError) {
                    \Log::warning("Failed to seed LGA data: " . $seedError->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Local governments for {$state->name} retrieved successfully",
                'data' => $lgas,
                'count' => $lgas->count(),
                'state' => [
                    'id' => $state->id,
                    'name' => $state->name,
                    'code' => $state->code
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("LocationController error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving local governments: ' . $e->getMessage(),
                'data' => [],
                'count' => 0
            ], 500);
        }
    }

    /**
     * Get all nationalities
     * 
     * @return JsonResponse
     */
    public function getNationalities(): JsonResponse
    {
        try {
            // If Nationality model doesn't exist yet, return default data
            if (!class_exists('App\Models\Nationality')) {
                $defaultNationalities = [
                    ['id' => 1, 'name' => 'Nigerian', 'code' => 'NG'],
                    ['id' => 2, 'name' => 'Ghanaian', 'code' => 'GH'],
                    ['id' => 3, 'name' => 'Cameroonian', 'code' => 'CM'],
                    ['id' => 4, 'name' => 'South African', 'code' => 'ZA'],
                    ['id' => 5, 'name' => 'Kenyan', 'code' => 'KE'],
                    ['id' => 6, 'name' => 'Beninese', 'code' => 'BJ'],
                    ['id' => 7, 'name' => 'Togolese', 'code' => 'TG'],
                    ['id' => 8, 'name' => 'Other', 'code' => 'XX']
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Nationalities retrieved successfully (default data)',
                    'data' => $defaultNationalities,
                    'count' => count($defaultNationalities)
                ]);
            }

            $nationalities = Nationality::select(['id', 'name', 'code'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Nationalities retrieved successfully',
                'data' => $nationalities,
                'count' => $nationalities->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving nationalities: ' . $e->getMessage(),
                'data' => [],
                'count' => 0
            ], 500);
        }
    }

    /**
     * Get complete location data (states with their LGAs)
     * 
     * @return JsonResponse
     */
    public function getCompleteLocationData(): JsonResponse
    {
        try {
            $states = State::with(['localGovernments' => function($query) {
                $query->select(['id', 'name', 'state_id'])->orderBy('name');
            }])
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

            $totalLgas = LocalGovernment::count();

            return response()->json([
                'success' => true,
                'message' => 'Complete location data retrieved successfully',
                'data' => [
                    'states' => $states,
                    'statistics' => [
                        'total_states' => $states->count(),
                        'total_lgas' => $totalLgas
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving location data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Search locations by name
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function searchLocations(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query');
            $type = $request->input('type', 'all'); // 'states', 'lgas', or 'all'

            if (!$query || strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters long',
                    'data' => []
                ], 400);
            }

            $results = [];

            if (in_array($type, ['states', 'all'])) {
                $states = State::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('code', 'LIKE', "%{$query}%")
                    ->select(['id', 'name', 'code'])
                    ->orderBy('name')
                    ->get()
                    ->map(function($state) {
                        return [
                            'id' => $state->id,
                            'name' => $state->name,
                            'code' => $state->code,
                            'type' => 'state'
                        ];
                    });

                $results = array_merge($results, $states->toArray());
            }

            if (in_array($type, ['lgas', 'all'])) {
                $lgas = LocalGovernment::with('state:id,name,code')
                    ->where('name', 'LIKE', "%{$query}%")
                    ->select(['id', 'name', 'state_id'])
                    ->orderBy('name')
                    ->get()
                    ->map(function($lga) {
                        return [
                            'id' => $lga->id,
                            'name' => $lga->name,
                            'state_id' => $lga->state_id,
                            'state_name' => $lga->state->name ?? 'Unknown',
                            'full_name' => $lga->name . ', ' . ($lga->state->name ?? 'Unknown State'),
                            'type' => 'lga'
                        ];
                    });

                $results = array_merge($results, $lgas->toArray());
            }

            return response()->json([
                'success' => true,
                'message' => "Found " . count($results) . " locations matching '{$query}'",
                'data' => $results,
                'count' => count($results),
                'search_params' => [
                    'query' => $query,
                    'type' => $type
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching locations: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get location statistics
     * 
     * @return JsonResponse
     */
    public function getLocationStatistics(): JsonResponse
    {
        try {
            $stateCount = State::count();
            $lgaCount = LocalGovernment::count();
            
            $statesWithMostLgas = State::withCount('localGovernments')
                ->orderBy('local_governments_count', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'local_governments_count']);

            $statesWithFewestLgas = State::withCount('localGovernments')
                ->orderBy('local_governments_count', 'asc')
                ->limit(5)
                ->get(['id', 'name', 'local_governments_count']);

            return response()->json([
                'success' => true,
                'message' => 'Location statistics retrieved successfully',
                'data' => [
                    'totals' => [
                        'states' => $stateCount,
                        'local_governments' => $lgaCount,
                        'average_lgas_per_state' => $stateCount > 0 ? round($lgaCount / $stateCount, 2) : 0
                    ],
                    'top_states' => [
                        'most_lgas' => $statesWithMostLgas,
                        'fewest_lgas' => $statesWithFewestLgas
                    ],
                    'generated_at' => now()->toDateTimeString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving location statistics: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}