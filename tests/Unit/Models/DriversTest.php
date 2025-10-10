<?php

namespace Tests\Unit\Models;

use App\Models\Drivers;
use App\Models\Nationality;
use App\Models\AdminUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DriversTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a nationality for foreign key constraint
        Nationality::firstOrCreate(['code' => 'NG'], ['name' => 'Nigerian', 'is_active' => true]);
    }

    public function test_driver_can_be_created_with_valid_data()
    {
        $driver = Drivers::factory()->create([
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        $this->assertDatabaseHas('drivers', [
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_full_name_accessor_returns_correct_format()
    {
        $driver = Drivers::factory()->create([
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John Michael Doe', $driver->full_name);
    }

    public function test_full_name_accessor_works_without_middle_name()
    {
        $driver = Drivers::factory()->create([
            'first_name' => 'John',
            'middle_name' => null,
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $driver->full_name);
    }

    public function test_display_name_returns_nickname_when_available()
    {
        $driver = Drivers::factory()->create([
            'nickname' => 'Johnny',
            'first_name' => 'John',
        ]);

        $this->assertEquals('Johnny', $driver->display_name);
    }

    public function test_display_name_returns_first_name_when_no_nickname()
    {
        $driver = Drivers::factory()->create([
            'nickname' => null,
            'first_name' => 'John',
        ]);

        $this->assertEquals('John', $driver->display_name);
    }

    public function test_age_accessor_calculates_correct_age()
    {
        $birthDate = Carbon::now()->subYears(25)->subMonths(6);

        $driver = Drivers::factory()->create([
            'date_of_birth' => $birthDate,
        ]);

        $this->assertEquals(25, $driver->age);
    }

    public function test_is_verified_accessor_returns_true_for_verified_status()
    {
        $driver = Drivers::factory()->create([
            'verification_status' => 'verified',
        ]);

        $this->assertTrue($driver->is_verified);
    }

    public function test_is_verified_accessor_returns_false_for_non_verified_status()
    {
        $driver = Drivers::factory()->create([
            'verification_status' => 'pending',
        ]);

        $this->assertFalse($driver->is_verified);
    }

    public function test_status_badge_accessor_returns_correct_format()
    {
        $driver = Drivers::factory()->create([
            'status' => 'active',
        ]);

        $badge = $driver->status_badge;

        $this->assertIsArray($badge);
        $this->assertEquals('Active', $badge['text']);
        $this->assertEquals('success', $badge['class']);
    }

    public function test_verification_badge_accessor_returns_correct_format()
    {
        $driver = Drivers::factory()->create([
            'verification_status' => 'verified',
        ]);

        $badge = $driver->verification_badge;

        $this->assertIsArray($badge);
        $this->assertEquals('Verified', $badge['text']);
        $this->assertEquals('success', $badge['class']);
    }

    public function test_verified_scope_filters_verified_drivers()
    {
        Drivers::factory()->create(['verification_status' => 'verified']);
        Drivers::factory()->create(['verification_status' => 'pending']);
        Drivers::factory()->create(['verification_status' => 'rejected']);

        $verifiedDrivers = Drivers::verified()->get();

        $this->assertCount(1, $verifiedDrivers);
        $this->assertEquals('verified', $verifiedDrivers->first()->verification_status);
    }

    public function test_active_scope_filters_active_drivers()
    {
        Drivers::factory()->create(['status' => 'active']);
        Drivers::factory()->create(['status' => 'inactive']);
        Drivers::factory()->create(['status' => 'suspended']);

        $activeDrivers = Drivers::active()->get();

        $this->assertCount(1, $activeDrivers);
        $this->assertEquals('active', $activeDrivers->first()->status);
    }

    public function test_available_scope_filters_active_and_online_drivers()
    {
        Drivers::factory()->create(['status' => 'active', 'is_active' => true]);
        Drivers::factory()->create(['status' => 'active', 'is_active' => false]);
        Drivers::factory()->create(['status' => 'inactive', 'is_active' => true]);

        $availableDrivers = Drivers::available()->get();

        $this->assertCount(1, $availableDrivers);
        $this->assertEquals('active', $availableDrivers->first()->status);
        $this->assertTrue($availableDrivers->first()->is_active);
    }

    public function test_by_gender_scope_filters_by_gender()
    {
        Drivers::factory()->create(['gender' => 'Male']);
        Drivers::factory()->create(['gender' => 'Female']);
        Drivers::factory()->create(['gender' => 'Male']);

        $maleDrivers = Drivers::byGender('Male')->get();

        $this->assertCount(2, $maleDrivers);
        $this->assertTrue($maleDrivers->every(fn($driver) => $driver->gender === 'Male'));
    }

    public function test_by_age_scope_filters_by_minimum_age()
    {
        $young = Carbon::now()->subYears(20);
        $old = Carbon::now()->subYears(40);

        Drivers::factory()->create(['date_of_birth' => $young]);
        Drivers::factory()->create(['date_of_birth' => $old]);

        $drivers = Drivers::byAge(25)->get();

        $this->assertCount(1, $drivers);
        $this->assertTrue($drivers->first()->age >= 25);
    }

    public function test_is_verified_method_returns_correct_boolean()
    {
        $verifiedDriver = Drivers::factory()->verified()->create();
        $pendingDriver = Drivers::factory()->pending()->create();

        $this->assertTrue($verifiedDriver->isVerified());
        $this->assertFalse($pendingDriver->isVerified());
    }

    public function test_is_active_method_returns_correct_boolean()
    {
        $activeDriver = Drivers::factory()->create([
            'status' => 'active',
            'is_active' => true,
        ]);

        $inactiveDriver = Drivers::factory()->create([
            'status' => 'inactive',
            'is_active' => false,
        ]);

        $this->assertTrue($activeDriver->isActive());
        $this->assertFalse($inactiveDriver->isActive());
    }

    public function test_nationality_relationship_works()
    {
        $nationality = Nationality::factory()->create(['name' => 'Test Nationality']);
        $driver = Drivers::factory()->create(['nationality_id' => $nationality->id]);

        $this->assertInstanceOf(Nationality::class, $driver->nationality);
        $this->assertEquals('Test Nationality', $driver->nationality->name);
    }

    public function test_verified_by_relationship_works()
    {
        $admin = AdminUser::factory()->create(['name' => 'Test Admin']);
        $driver = Drivers::factory()->create(['verified_by' => $admin->id]);

        $this->assertInstanceOf(AdminUser::class, $driver->verifiedBy);
        $this->assertEquals('Test Admin', $driver->verifiedBy->name);
    }

    public function test_soft_deletes_work_correctly()
    {
        $driver = Drivers::factory()->create();
        $driverId = $driver->id;

        $driver->delete();

        $this->assertSoftDeleted('drivers', ['id' => $driverId]);
        $this->assertCount(0, Drivers::all());
        $this->assertCount(1, Drivers::withTrashed()->get());
    }
}