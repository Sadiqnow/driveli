<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\Driver;
use App\Services\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class MatchingServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $matchingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchingService = app(MatchingService::class);
    }

    public function test_find_matches_for_request()
    {
        // Create test data
        $company = Company::factory()->create();
        $request = CompanyRequest::factory()->create([
            'company_id' => $company->id,
            'pickup_location' => 'Lagos',
            'vehicle_type' => 'truck',
            'experience_required' => 2,
        ]);

        // Create drivers with different attributes
        $perfectMatch = Driver::factory()->create([
            'location' => 'Lagos',
            'vehicle_type' => 'truck',
            'experience_years' => 5,
            'rating' => 4.8,
            'status' => 'available',
        ]);

        $goodMatch = Driver::factory()->create([
            'location' => 'Lagos',
            'vehicle_type' => 'truck',
            'experience_years' => 3,
            'rating' => 4.2,
            'status' => 'available',
        ]);

        $poorMatch = Driver::factory()->create([
            'location' => 'Abuja', // Different location
            'vehicle_type' => 'car', // Different vehicle type
            'experience_years' => 1,
            'rating' => 3.5,
            'status' => 'available',
        ]);

        // Run matching
        $matches = $this->matchingService->findMatchesForRequest($request);

        // Assert that matches are returned
        $this->assertIsArray($matches);
        $this->assertGreaterThan(0, count($matches));

        // Check that matches have required structure
        foreach ($matches as $match) {
            $this->assertArrayHasKey('driver', $match);
            $this->assertArrayHasKey('match_score', $match);
            $this->assertArrayHasKey('reasons', $match);
            $this->assertGreaterThan(0, $match['match_score']);
        }
    }

    public function test_calculate_match_score()
    {
        $request = CompanyRequest::factory()->create([
            'pickup_location' => 'Lagos',
            'vehicle_type' => 'truck',
            'experience_required' => 2,
        ]);

        $driver = Driver::factory()->create([
            'location' => 'Lagos',
            'vehicle_type' => 'truck',
            'experience_years' => 5,
            'rating' => 4.8,
        ]);

        $score = $this->matchingService->calculateMatchScore($request, $driver);

        // Score should be between 0 and 100
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);

        // Perfect match should have high score
        $this->assertGreaterThan(80, $score);
    }

    public function test_location_matching_bonus()
    {
        $request = CompanyRequest::factory()->create(['pickup_location' => 'Lagos']);
        $driver = Driver::factory()->create(['location' => 'Lagos']);

        $score = $this->matchingService->calculateMatchScore($request, $driver);

        // Same location should get bonus points
        $this->assertGreaterThan(50, $score);
    }

    public function test_vehicle_type_matching()
    {
        $request = CompanyRequest::factory()->create(['vehicle_type' => 'truck']);
        $matchingDriver = Driver::factory()->create(['vehicle_type' => 'truck']);
        $nonMatchingDriver = Driver::factory()->create(['vehicle_type' => 'car']);

        $matchingScore = $this->matchingService->calculateMatchScore($request, $matchingDriver);
        $nonMatchingScore = $this->matchingService->calculateMatchScore($request, $nonMatchingDriver);

        // Matching vehicle type should have higher score
        $this->assertGreaterThan($nonMatchingScore, $matchingScore);
    }

    public function test_experience_requirement_matching()
    {
        $request = CompanyRequest::factory()->create(['experience_required' => 3]);
        $experiencedDriver = Driver::factory()->create(['experience_years' => 5]);
        $inexperiencedDriver = Driver::factory()->create(['experience_years' => 1]);

        $experiencedScore = $this->matchingService->calculateMatchScore($request, $experiencedDriver);
        $inexperiencedScore = $this->matchingService->calculateMatchScore($request, $inexperiencedDriver);

        // More experienced driver should have higher score
        $this->assertGreaterThan($inexperiencedScore, $experiencedScore);
    }

    public function test_rating_influences_score()
    {
        $request = CompanyRequest::factory()->create();
        $highRatedDriver = Driver::factory()->create(['rating' => 4.8]);
        $lowRatedDriver = Driver::factory()->create(['rating' => 2.5]);

        $highScore = $this->matchingService->calculateMatchScore($request, $highRatedDriver);
        $lowScore = $this->matchingService->calculateMatchScore($request, $lowRatedDriver);

        // Higher rated driver should have higher score
        $this->assertGreaterThan($lowScore, $highScore);
    }

    public function test_only_available_drivers_are_matched()
    {
        $request = CompanyRequest::factory()->create();

        Driver::factory()->create(['status' => 'available']);
        Driver::factory()->create(['status' => 'busy']);
        Driver::factory()->create(['status' => 'offline']);

        $matches = $this->matchingService->findMatchesForRequest($request);

        // Should only match available drivers
        foreach ($matches as $match) {
            $this->assertEquals('available', $match['driver']->status);
        }
    }

    public function test_match_reasons_are_provided()
    {
        $request = CompanyRequest::factory()->create([
            'pickup_location' => 'Lagos',
            'vehicle_type' => 'truck',
        ]);

        $driver = Driver::factory()->create([
            'location' => 'Lagos',
            'vehicle_type' => 'truck',
            'experience_years' => 4,
            'rating' => 4.5,
        ]);

        $match = $this->matchingService->findMatchesForRequest($request)[0];

        $this->assertArrayHasKey('reasons', $match);
        $this->assertIsArray($match['reasons']);
        $this->assertGreaterThan(0, count($match['reasons']));
    }

    public function test_matches_are_ordered_by_score()
    {
        $request = CompanyRequest::factory()->create();

        // Create drivers with different scores
        $highScoreDriver = Driver::factory()->create([
            'location' => $request->pickup_location,
            'vehicle_type' => $request->vehicle_type,
            'experience_years' => 10,
            'rating' => 5.0,
        ]);

        $lowScoreDriver = Driver::factory()->create([
            'location' => 'Different City',
            'vehicle_type' => 'different',
            'experience_years' => 0,
            'rating' => 1.0,
        ]);

        $matches = $this->matchingService->findMatchesForRequest($request);

        // First match should have higher score than second
        if (count($matches) >= 2) {
            $this->assertGreaterThanOrEqual($matches[1]['match_score'], $matches[0]['match_score']);
        }
    }

    public function test_empty_matches_when_no_drivers_available()
    {
        $request = CompanyRequest::factory()->create();

        // Don't create any drivers
        $matches = $this->matchingService->findMatchesForRequest($request);

        $this->assertIsArray($matches);
        $this->assertCount(0, $matches);
    }

    public function test_dispatch_matching_job_method()
    {
        $request = CompanyRequest::factory()->create();

        // Mock the job dispatch
        $this->matchingService->dispatchMatchingJob($request);

        // Since it's a void method, we just ensure no exceptions are thrown
        $this->assertTrue(true);
    }

    public function test_match_score_calculation_with_edge_cases()
    {
        $request = CompanyRequest::factory()->create([
            'pickup_location' => 'Lagos',
            'vehicle_type' => 'truck',
            'experience_required' => 0, // Minimum experience
        ]);

        $driver = Driver::factory()->create([
            'location' => 'Abuja', // Different location
            'vehicle_type' => 'car', // Different vehicle type
            'experience_years' => 0, // Minimum experience
            'rating' => 1.0, // Minimum rating
        ]);

        $score = $this->matchingService->calculateMatchScore($request, $driver);

        // Score should be low but not zero
        $this->assertGreaterThan(0, $score);
        $this->assertLessThan(30, $score); // Should be low due to mismatches
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
