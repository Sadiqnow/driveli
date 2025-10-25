<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\State;
use App\Models\Lga;
use App\Services\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class CompanyRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $company;
    protected $state;
    protected $lga;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->state = State::factory()->create();
        $this->lga = Lga::factory()->create(['state_id' => $this->state->id]);
        $this->company = Company::factory()->create();
    }

    public function test_company_can_create_request()
    {
        $requestData = [
            'pickup_location' => $this->faker->address,
            'pickup_state_id' => $this->state->id,
            'pickup_lga_id' => $this->lga->id,
            'dropoff_location' => $this->faker->address,
            'vehicle_type' => 'truck',
            'cargo_type' => 'general',
            'cargo_description' => $this->faker->sentence,
            'weight_kg' => 1000,
            'value_naira' => 50000,
            'pickup_date' => now()->addDays(2)->format('Y-m-d H:i'),
            'urgency' => 'medium',
            'budget_min' => 10000,
            'budget_max' => 20000,
            'experience_required' => 2,
            'special_requirements' => $this->faker->sentence,
        ];

        $response = $this->actingAs($this->company, 'sanctum')
            ->postJson('/api/company/requests', $requestData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'request_id',
                    'company_id',
                    'status',
                    'pickup_location',
                    'vehicle_type',
                ]
            ]);

        $this->assertDatabaseHas('company_requests', [
            'company_id' => $this->company->id,
            'pickup_location' => $requestData['pickup_location'],
            'vehicle_type' => $requestData['vehicle_type'],
        ]);
    }

    public function test_company_can_list_requests()
    {
        CompanyRequest::factory()->count(3)->create(['company_id' => $this->company->id]);
        CompanyRequest::factory()->count(2)->create(); // Other companies' requests

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson('/api/company/requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'request_id',
                            'company_id',
                            'status',
                        ]
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_company_can_view_request()
    {
        $request = CompanyRequest::factory()->create(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson("/api/company/requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'request_id',
                    'company_id',
                    'pickup_location',
                ]
            ]);
    }

    public function test_company_can_update_request()
    {
        $request = CompanyRequest::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'pending'
        ]);

        $updateData = [
            'pickup_location' => 'Updated Location',
            'cargo_description' => 'Updated description',
        ];

        $response = $this->actingAs($this->company, 'sanctum')
            ->putJson("/api/company/requests/{$request->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Request updated successfully'
            ]);

        $this->assertDatabaseHas('company_requests', [
            'id' => $request->id,
            'pickup_location' => 'Updated Location',
        ]);
    }

    public function test_company_can_delete_request()
    {
        $request = CompanyRequest::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->deleteJson("/api/company/requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Request deleted successfully'
            ]);

        $this->assertSoftDeleted('company_requests', ['id' => $request->id]);
    }

    public function test_company_cannot_view_other_company_request()
    {
        $otherCompany = Company::factory()->create();
        $request = CompanyRequest::factory()->create(['company_id' => $otherCompany->id]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson("/api/company/requests/{$request->id}");

        $response->assertStatus(403);
    }

    public function test_validation_errors_on_create_request()
    {
        $invalidData = [
            'pickup_location' => '', // Required
            'vehicle_type' => 'invalid_type', // Invalid enum
            'pickup_date' => now()->subDay()->format('Y-m-d H:i'), // Past date
        ];

        $response = $this->actingAs($this->company, 'sanctum')
            ->postJson('/api/company/requests', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    public function test_request_filters_work()
    {
        CompanyRequest::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'active'
        ]);
        CompanyRequest::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson('/api/company/requests?status=active');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('active', $response->json('data.data.0.status'));
    }

    public function test_matching_job_is_dispatched_on_request_creation()
    {
        $mockMatchingService = Mockery::mock(MatchingService::class);
        $mockMatchingService->shouldReceive('dispatchMatchingJob')
            ->once()
            ->andReturnNull();

        $this->app->instance(MatchingService::class, $mockMatchingService);

        $requestData = [
            'pickup_location' => $this->faker->address,
            'pickup_state_id' => $this->state->id,
            'pickup_lga_id' => $this->lga->id,
            'dropoff_location' => $this->faker->address,
            'vehicle_type' => 'truck',
            'cargo_type' => 'general',
            'cargo_description' => $this->faker->sentence,
            'weight_kg' => 1000,
            'value_naira' => 50000,
            'pickup_date' => now()->addDays(2)->format('Y-m-d H:i'),
            'urgency' => 'medium',
            'budget_min' => 10000,
            'budget_max' => 20000,
            'experience_required' => 2,
            'special_requirements' => $this->faker->sentence,
        ];

        $response = $this->actingAs($this->company, 'sanctum')
            ->postJson('/api/company/requests', $requestData);

        $response->assertStatus(201);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
