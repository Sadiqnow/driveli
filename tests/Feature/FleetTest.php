<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Fleet;
use App\Models\Vehicle;
use App\Services\FleetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class FleetTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
    }

    public function test_company_can_create_fleet()
    {
        $fleetData = [
            'name' => 'Main Fleet',
            'description' => 'Primary fleet for operations',
            'manager_name' => 'John Doe',
            'manager_phone' => '+2348012345678',
            'manager_email' => 'john@example.com',
            'base_location' => 'Lagos',
            'operating_regions' => 'Lagos, Abuja',
        ];

        $response = $this->actingAs($this->company, 'sanctum')
            ->postJson('/api/company/fleets', $fleetData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'company_id',
                    'manager_name',
                ]
            ]);

        $this->assertDatabaseHas('fleets', [
            'company_id' => $this->company->id,
            'name' => 'Main Fleet',
            'manager_name' => 'John Doe',
        ]);
    }

    public function test_company_can_list_fleets()
    {
        Fleet::factory()->count(3)->create(['company_id' => $this->company->id]);
        Fleet::factory()->count(2)->create(); // Other companies' fleets

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson('/api/company/fleets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'company_id',
                        ]
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_company_can_view_fleet()
    {
        $fleet = Fleet::factory()->create(['company_id' => $this->company->id]);
        Vehicle::factory()->count(2)->create(['fleet_id' => $fleet->id]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson("/api/company/fleets/{$fleet->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'fleet' => [
                        'id',
                        'name',
                        'vehicles',
                    ],
                    'stats' => [
                        'total_vehicles',
                        'active_vehicles',
                    ]
                ]
            ]);
    }

    public function test_company_can_update_fleet()
    {
        $fleet = Fleet::factory()->create(['company_id' => $this->company->id]);

        $updateData = [
            'name' => 'Updated Fleet Name',
            'description' => 'Updated description',
            'manager_name' => 'Jane Smith',
        ];

        $response = $this->actingAs($this->company, 'sanctum')
            ->putJson("/api/company/fleets/{$fleet->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Fleet updated successfully'
            ]);

        $this->assertDatabaseHas('fleets', [
            'id' => $fleet->id,
            'name' => 'Updated Fleet Name',
            'manager_name' => 'Jane Smith',
        ]);
    }

    public function test_company_can_delete_fleet()
    {
        $fleet = Fleet::factory()->create(['company_id' => $this->company->id]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->deleteJson("/api/company/fleets/{$fleet->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Fleet deleted successfully'
            ]);

        $this->assertSoftDeleted('fleets', ['id' => $fleet->id]);
    }

    public function test_company_can_add_vehicle_to_fleet()
    {
        $fleet = Fleet::factory()->create(['company_id' => $this->company->id]);

        $vehicleData = [
            'registration_number' => 'ABC123DE',
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2020,
            'vehicle_type' => 'car',
            'seating_capacity' => 5,
        ];

        $response = $this->actingAs($this->company, 'sanctum')
            ->postJson("/api/company/fleets/{$fleet->id}/vehicles", $vehicleData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'registration_number',
                    'fleet_id',
                ]
            ]);

        $this->assertDatabaseHas('vehicles', [
            'fleet_id' => $fleet->id,
            'registration_number' => 'ABC123DE',
        ]);
    }

    public function test_company_can_remove_vehicle_from_fleet()
    {
        $fleet = Fleet::factory()->create(['company_id' => $this->company->id]);
        $vehicle = Vehicle::factory()->create(['fleet_id' => $fleet->id]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->deleteJson("/api/company/fleets/{$fleet->id}/vehicles/{$vehicle->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Vehicle removed successfully'
            ]);

        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
    }

    public function test_company_can_view_fleet_vehicles()
    {
        $fleet = Fleet::factory()->create(['company_id' => $this->company->id]);
        Vehicle::factory()->count(3)->create(['fleet_id' => $fleet->id]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson("/api/company/fleets/{$fleet->id}/vehicles");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'registration_number',
                            'fleet_id',
                        ]
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_company_cannot_view_other_company_fleet()
    {
        $otherCompany = Company::factory()->create();
        $fleet = Fleet::factory()->create(['company_id' => $otherCompany->id]);

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson("/api/company/fleets/{$fleet->id}");

        $response->assertStatus(403);
    }

    public function test_validation_errors_on_create_fleet()
    {
        $invalidData = [
            'name' => '', // Required
            'manager_email' => 'invalid-email', // Invalid email
        ];

        $response = $this->actingAs($this->company, 'sanctum')
            ->postJson('/api/company/fleets', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    public function test_fleet_stats_are_calculated_correctly()
    {
        $fleet = Fleet::factory()->create(['company_id' => $this->company->id]);
        Vehicle::factory()->count(2)->create(['fleet_id' => $fleet->id, 'status' => 'active']);
        Vehicle::factory()->create(['fleet_id' => $fleet->id, 'status' => 'maintenance']);

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson("/api/company/fleets/{$fleet->id}");

        $response->assertStatus(200);

        $stats = $response->json('data.stats');
        $this->assertEquals(3, $stats['total_vehicles']);
        $this->assertEquals(2, $stats['active_vehicles']);
    }

    public function test_fleet_service_is_used_for_stats_calculation()
    {
        $fleet = Fleet::factory()->create(['company_id' => $this->company->id]);
        Vehicle::factory()->count(2)->create(['fleet_id' => $fleet->id, 'status' => 'active']);

        $mockFleetService = Mockery::mock(FleetService::class);
        $mockFleetService->shouldReceive('getFleetStats')
            ->once()
            ->with($fleet)
            ->andReturn([
                'total_vehicles' => 2,
                'active_vehicles' => 2,
                'maintenance_vehicles' => 0,
            ]);

        $this->app->instance(FleetService::class, $mockFleetService);

        $response = $this->actingAs($this->company, 'sanctum')
            ->getJson("/api/company/fleets/{$fleet->id}");

        $response->assertStatus(200);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
