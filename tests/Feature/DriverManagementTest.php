<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AdminUser;
use App\Models\Drivers as Driver;
use App\Services\DriverManagementService;
use App\Constants\DrivelinkConstants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DriverManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Avoid wrapping this test class in transactions to prevent savepoint errors
     * on some MySQL setups during nested transaction rollback tests.
     *
     * RefreshDatabase will still run migrations; tests will manage DB cleanup.
     */
    protected $connectionsToTransact = [];

    private DriverManagementService $driverService;
    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure a clean drivers table for deterministic counts
        if (\Illuminate\Support\Facades\Schema::hasTable((new \App\Models\Drivers)->getTable())) {
            // Some DB engines (MySQL) won't allow truncating a table referenced by FK constraints.
            // Truncate dependent tables first in a safe order inside a FK-check toggle.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            if (Schema::hasTable('commissions')) {
                DB::table('commissions')->truncate();
            }
            DB::table((new \App\Models\Drivers)->getTable())->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        
        $this->driverService = app(DriverManagementService::class);
        $this->admin = AdminUser::factory()->create([
            'role' => DrivelinkConstants::ADMIN_ROLE_ADMIN
        ]);
        $this->actingAs($this->admin, 'admin');
    }

    public function test_can_create_driver_with_encrypted_fields()
    {
        $driverData = [
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@test.com',
            'phone' => '08012345678',
            'nin_number' => '12345678901',
            'date_of_birth' => '1990-01-01',
            'gender' => DrivelinkConstants::GENDER_MALE,
        ];

        $driver = $this->driverService->createDriver($driverData);

        $this->assertInstanceOf(Driver::class, $driver);
        $this->assertEquals('John', $driver->first_name);
        $this->assertEquals('john@test.com', $driver->email);
        $this->assertDatabaseHas('drivers', [
            'email' => 'john@test.com',
            'status' => DrivelinkConstants::DRIVER_STATUS_PENDING,
        ]);

        // Sensitive fields should be encrypted in database
        $rawDriver = \DB::table('drivers')->where('id', $driver->id)->first();
        $this->assertNotEquals('08012345678', $rawDriver->phone);
        $this->assertNotEquals('12345678901', $rawDriver->nin_number);
    }

    public function test_can_filter_drivers_by_status()
    {
        Driver::factory()->create(['status' => DrivelinkConstants::DRIVER_STATUS_ACTIVE]);
        Driver::factory()->create(['status' => DrivelinkConstants::DRIVER_STATUS_PENDING]);
        Driver::factory()->create(['status' => DrivelinkConstants::DRIVER_STATUS_SUSPENDED]);

        $filters = ['status' => DrivelinkConstants::DRIVER_STATUS_ACTIVE];
        $result = $this->driverService->getDriversWithFilters($filters);

        $this->assertEquals(1, $result->count());
        $this->assertEquals(DrivelinkConstants::DRIVER_STATUS_ACTIVE, $result->first()->status);
    }

    public function test_can_search_drivers_by_name()
    {
        Driver::factory()->create(['first_name' => 'John', 'surname' => 'Doe']);
        Driver::factory()->create(['first_name' => 'Jane', 'surname' => 'Smith']);

        $filters = ['search' => 'John'];
        $result = $this->driverService->getDriversWithFilters($filters);

        $this->assertEquals(1, $result->count());
        $this->assertEquals('John', $result->first()->first_name);
    }

    public function test_can_approve_driver_kyc()
    {
        Notification::fake();

        $driver = Driver::factory()->create([
            'kyc_status' => DrivelinkConstants::KYC_STATUS_IN_PROGRESS,
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_PENDING,
        ]);

        $result = $this->driverService->approveKyc($driver, 'All documents verified');

        $this->assertTrue($result);
        $driver->refresh();
        
        $this->assertEquals(DrivelinkConstants::KYC_STATUS_COMPLETED, $driver->kyc_status);
        $this->assertEquals(DrivelinkConstants::VERIFICATION_STATUS_VERIFIED, $driver->verification_status);
        $this->assertEquals(DrivelinkConstants::DRIVER_STATUS_ACTIVE, $driver->status);
        $this->assertNotNull($driver->verified_at);
        $this->assertEquals($this->admin->id, $driver->verified_by);

        // Should send notification
        // Notification::assertSentTo($driver, KycApprovedNotification::class);
    }

    public function test_can_reject_driver_kyc()
    {
        Notification::fake();

        $driver = Driver::factory()->create([
            'kyc_status' => DrivelinkConstants::KYC_STATUS_IN_PROGRESS,
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_PENDING,
        ]);

        $reason = 'Invalid documents provided';
        $result = $this->driverService->rejectKyc($driver, $reason);

        $this->assertTrue($result);
        $driver->refresh();
        
        $this->assertEquals(DrivelinkConstants::KYC_STATUS_REJECTED, $driver->kyc_status);
        $this->assertEquals(DrivelinkConstants::VERIFICATION_STATUS_REJECTED, $driver->verification_status);
        $this->assertEquals($reason, $driver->kyc_rejection_reason);
        $this->assertNotNull($driver->rejected_at);

        // Should send notification
        // Notification::assertSentTo($driver, KycRejectedNotification::class);
    }

    public function test_bulk_approval_of_drivers()
    {
        $drivers = Driver::factory()->count(3)->create([
            'kyc_status' => DrivelinkConstants::KYC_STATUS_IN_PROGRESS,
        ]);

        $driverIds = $drivers->pluck('id')->toArray();
        $result = $this->driverService->bulkAction($driverIds, 'approve', [
            'notes' => 'Bulk approval'
        ]);

        $this->assertEquals(3, $result['success']);
        $this->assertEquals(0, $result['failed']);

        foreach ($drivers as $driver) {
            $driver->refresh();
            $this->assertEquals(DrivelinkConstants::KYC_STATUS_COMPLETED, $driver->kyc_status);
            $this->assertEquals(DrivelinkConstants::DRIVER_STATUS_ACTIVE, $driver->status);
        }
    }

    public function test_driver_statistics_calculation()
    {
        // Create four distinct drivers where each contributes to exactly one statistic
        Driver::factory()->create([
            'status' => DrivelinkConstants::DRIVER_STATUS_ACTIVE,
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_PENDING,
            'kyc_status' => DrivelinkConstants::KYC_STATUS_NOT_STARTED,
        ]);

        Driver::factory()->create([
            'status' => DrivelinkConstants::DRIVER_STATUS_PENDING,
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_PENDING,
            'kyc_status' => DrivelinkConstants::KYC_STATUS_NOT_STARTED,
        ]);

        Driver::factory()->create([
            'status' => DrivelinkConstants::DRIVER_STATUS_INACTIVE,
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_VERIFIED,
            'kyc_status' => DrivelinkConstants::KYC_STATUS_NOT_STARTED,
        ]);

        Driver::factory()->create([
            'status' => DrivelinkConstants::DRIVER_STATUS_INACTIVE,
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_PENDING,
            'kyc_status' => DrivelinkConstants::KYC_STATUS_COMPLETED,
        ]);

        $stats = $this->driverService->getDriverStatistics();

        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(1, $stats['active']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(1, $stats['verified']);
        $this->assertEquals(1, $stats['kyc_completed']);
    }

    public function test_verification_readiness_calculation()
    {
        $driver = Driver::factory()->create([
            'profile_completion_percentage' => 90,
            'kyc_status' => DrivelinkConstants::KYC_STATUS_COMPLETED,
            'nin_document' => 'nin.jpg',
            'license_front_image' => 'license_front.jpg',
            'license_back_image' => 'license_back.jpg',
            'passport_photograph' => 'passport.jpg',
            'profile_picture' => 'profile.jpg',
        ]);

        $readiness = $this->driverService->calculateVerificationReadiness($driver);

        $this->assertArrayHasKey('overall_score', $readiness);
        $this->assertArrayHasKey('overall_status', $readiness);
        $this->assertArrayHasKey('criteria', $readiness);
        
        $this->assertGreaterThan(80, $readiness['overall_score']);
        $this->assertEquals('ready', $readiness['overall_status']);
    }

    public function test_pagination_limits_are_enforced()
    {
        Driver::factory()->count(150)->create();

        // Test maximum page size enforcement
        $result = $this->driverService->getDriversWithFilters([], 150);
        $this->assertEquals(DrivelinkConstants::MAX_PAGE_SIZE, $result->perPage());

        // Test valid page size
        $result = $this->driverService->getDriversWithFilters([], 50);
        $this->assertEquals(50, $result->perPage());
    }

    public function test_search_term_length_validation()
    {
        Driver::factory()->create(['first_name' => 'John']);

        // Too short search term should be ignored
        $result = $this->driverService->getDriversWithFilters(['search' => 'J']);
        $this->assertEquals(1, $result->count()); // Should return all drivers

        // Valid search term should work
        $result = $this->driverService->getDriversWithFilters(['search' => 'John']);
        $this->assertEquals(1, $result->count());
    }

    public function test_driver_id_generation_is_unique()
    {
        $driver1 = $this->driverService->createDriver([
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@test.com',
        ]);

        $driver2 = $this->driverService->createDriver([
            'first_name' => 'Jane',
            'surname' => 'Smith',
            'email' => 'jane@test.com',
        ]);

        $this->assertNotEmpty($driver1->driver_id);
        $this->assertNotEmpty($driver2->driver_id);
        $this->assertNotEquals($driver1->driver_id, $driver2->driver_id);
        $this->assertStringStartsWith('DRV', $driver1->driver_id);
        $this->assertStringStartsWith('DRV', $driver2->driver_id);
    }

    public function test_transaction_rollback_on_failure()
    {
        $initialCount = Driver::count();

        // Bind a lightweight fake Drivers model that throws during create()
        app()->bind(\App\Models\Drivers::class, function () {
            return new class {
                public function create($data)
                {
                    throw new \Exception('Database error');
                }
            };
        });

        // Re-resolve the service so it uses the bound Drivers implementation
        $this->driverService = app(DriverManagementService::class);

        try {
            $this->driverService->createDriver([
                'first_name' => 'John',
                'surname' => 'Doe',
                'email' => 'john@test.com',
            ]);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Ensure DB remains consistent (no driver created)
            $this->assertEquals($initialCount, Driver::count(), 'Driver count should be unchanged after failed transaction');
        }
    }
}