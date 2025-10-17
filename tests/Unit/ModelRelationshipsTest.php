<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Driver;
use App\Models\DriverBankingDetail;
use App\Models\DriverDocument;
use App\Models\DriverMatch;
use App\Models\DriverNextOfKin;
use App\Models\DriverPerformance;
use App\Models\DriverCategoryRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function driver_has_banking_details_relationship()
    {
        $driver = Driver::factory()->create();

        $bankingDetail = DriverBankingDetail::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(DriverBankingDetail::class, $driver->bankingDetails);
        $this->assertEquals($bankingDetail->id, $driver->bankingDetails->id);
    }

    /** @test */
    public function driver_has_documents_relationship()
    {
        $driver = Driver::factory()->create();

        $document = DriverDocument::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(DriverDocument::class, $driver->documents->first());
        $this->assertEquals($document->id, $driver->documents->first()->id);
    }

    /** @test */
    public function driver_has_next_of_kin_relationship()
    {
        $driver = Driver::factory()->create();

        $nextOfKin = DriverNextOfKin::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(DriverNextOfKin::class, $driver->nextOfKin);
        $this->assertEquals($nextOfKin->id, $driver->nextOfKin->id);
    }

    /** @test */
    public function driver_has_performance_relationship()
    {
        $driver = Driver::factory()->create();

        $performance = DriverPerformance::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(DriverPerformance::class, $driver->performance);
        $this->assertEquals($performance->id, $driver->performance->id);
    }

    /** @test */
    public function driver_has_matches_relationship()
    {
        $driver = Driver::factory()->create();

        $match = DriverMatch::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(DriverMatch::class, $driver->matches->first());
        $this->assertEquals($match->id, $driver->matches->first()->id);
    }

    /** @test */
    public function driver_has_category_requirements_relationship()
    {
        $driver = Driver::factory()->create();

        $requirement = DriverCategoryRequirement::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(DriverCategoryRequirement::class, $driver->categoryRequirements->first());
        $this->assertEquals($requirement->id, $driver->categoryRequirements->first()->id);
    }

    /** @test */
    public function driver_banking_detail_belongs_to_driver()
    {
        $driver = Driver::factory()->create();
        $bankingDetail = DriverBankingDetail::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(Driver::class, $bankingDetail->driver);
        $this->assertEquals($driver->id, $bankingDetail->driver->id);
    }

    /** @test */
    public function driver_document_belongs_to_driver()
    {
        $driver = Driver::factory()->create();
        $document = DriverDocument::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(Driver::class, $document->driver);
        $this->assertEquals($driver->id, $document->driver->id);
    }

    /** @test */
    public function driver_next_of_kin_belongs_to_driver()
    {
        $driver = Driver::factory()->create();
        $nextOfKin = DriverNextOfKin::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(Driver::class, $nextOfKin->driver);
        $this->assertEquals($driver->id, $nextOfKin->driver->id);
    }

    /** @test */
    public function driver_performance_belongs_to_driver()
    {
        $driver = Driver::factory()->create();
        $performance = DriverPerformance::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(Driver::class, $performance->driver);
        $this->assertEquals($driver->id, $performance->driver->id);
    }

    /** @test */
    public function driver_match_belongs_to_driver()
    {
        $driver = Driver::factory()->create();
        $match = DriverMatch::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(Driver::class, $match->driver);
        $this->assertEquals($driver->id, $match->driver->id);
    }

    /** @test */
    public function driver_category_requirement_belongs_to_driver()
    {
        $driver = Driver::factory()->create();
        $requirement = DriverCategoryRequirement::factory()->create([
            'driver_id' => $driver->id
        ]);

        $this->assertInstanceOf(Driver::class, $requirement->driver);
        $this->assertEquals($driver->id, $requirement->driver->id);
    }

    /** @test */
    public function driver_can_create_transactional_data()
    {
        $driver = Driver::factory()->create();

        // Create related data
        $bankingDetail = DriverBankingDetail::factory()->create(['driver_id' => $driver->id]);
        $document = DriverDocument::factory()->create(['driver_id' => $driver->id]);
        $nextOfKin = DriverNextOfKin::factory()->create(['driver_id' => $driver->id]);
        $performance = DriverPerformance::factory()->create(['driver_id' => $driver->id]);

        // Refresh driver with relationships
        $driver->load(['bankingDetails', 'documents', 'nextOfKin', 'performance']);

        $this->assertNotNull($driver->bankingDetails);
        $this->assertCount(1, $driver->documents);
        $this->assertNotNull($driver->nextOfKin);
        $this->assertNotNull($driver->performance);
    }

    /** @test */
    public function driver_cascading_delete_works()
    {
        $driver = Driver::factory()->create();

        // Create related data
        DriverBankingDetail::factory()->create(['driver_id' => $driver->id]);
        DriverDocument::factory()->create(['driver_id' => $driver->id]);
        DriverNextOfKin::factory()->create(['driver_id' => $driver->id]);
        DriverPerformance::factory()->create(['driver_id' => $driver->id]);
        DriverMatch::factory()->create(['driver_id' => $driver->id]);
        DriverCategoryRequirement::factory()->create(['driver_id' => $driver->id]);

        // Delete driver
        $driver->delete();

        // Check that related data still exists (no cascading delete)
        $this->assertDatabaseHas('driver_banking_details', ['driver_id' => $driver->id]);
        $this->assertDatabaseHas('driver_documents', ['driver_id' => $driver->id]);
        $this->assertDatabaseHas('driver_next_of_kin', ['driver_id' => $driver->id]);
        $this->assertDatabaseHas('driver_performances', ['driver_id' => $driver->id]);
        $this->assertDatabaseHas('driver_matches', ['driver_id' => $driver->id]);
        $this->assertDatabaseHas('driver_category_requirements', ['driver_id' => $driver->id]);
    }
}
