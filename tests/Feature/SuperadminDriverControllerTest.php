<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Drivers;
use App\Models\SuperadminActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SuperadminDriverControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a superadmin user
        $this->superadmin = AdminUser::factory()->superAdmin()->create();
    }

    /** @test */
    public function superadmin_can_view_drivers_index()
    {
        $this->actingAs($this->superadmin, 'admin');

        $response = $this->get(route('admin.superadmin.drivers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.superadmin.drivers.index');
        $response->assertViewHas('drivers');
    }

    /** @test */
    public function non_superadmin_cannot_access_drivers_index()
    {
        $regularAdmin = AdminUser::factory()->create([
            'role' => 'Admin',
            'is_active' => true,
        ]);

        $this->actingAs($regularAdmin, 'admin');

        $response = $this->get(route('admin.superadmin.drivers.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function superadmin_can_create_driver()
    {
        $this->actingAs($this->superadmin, 'admin');

        $driverData = [
            '_token' => session()->token(),
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'status' => 'active',
            'verification_status' => 'pending',
            'kyc_status' => 'pending',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('admin.superadmin.drivers.store'), $driverData);

        $response->assertRedirect(route('admin.superadmin.drivers.show', 1));
        $this->assertDatabaseHas('drivers', [
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        // Check activity logging
        $this->assertDatabaseHas('superadmin_activity_logs', [
            'action' => 'create',
            'resource_type' => 'driver',
        ]);
    }

    /** @test */
    public function superadmin_can_view_driver_details()
    {
        $this->actingAs($this->superadmin, 'admin');

        $driver = Drivers::factory()->create();

        $response = $this->get(route('admin.superadmin.drivers.show', $driver));

        $response->assertStatus(200);
        $response->assertViewIs('admin.superadmin.drivers.show');
        $response->assertViewHas('driver');
    }

    /** @test */
    public function superadmin_can_update_driver()
    {
        $this->actingAs($this->superadmin, 'admin');

        $driver = Drivers::factory()->create([
            'first_name' => 'Old Name',
        ]);

        $updateData = [
            '_token' => session()->token(),
            'first_name' => 'Updated Name',
            'surname' => $driver->surname,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'status' => $driver->status,
            'verification_status' => $driver->verification_status,
            'kyc_status' => $driver->kyc_status,
        ];

        $response = $this->put(route('admin.superadmin.drivers.update', $driver), $updateData);

        $response->assertRedirect(route('admin.superadmin.drivers.show', $driver));
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'first_name' => 'Updated Name',
        ]);

        // Check activity logging
        $this->assertDatabaseHas('superadmin_activity_logs', [
            'action' => 'update',
            'resource_type' => 'driver',
        ]);
    }

    /** @test */
    public function superadmin_can_soft_delete_driver()
    {
        $this->actingAs($this->superadmin, 'admin');

        $driver = Drivers::factory()->create();

        $response = $this->delete(route('admin.superadmin.drivers.destroy', $driver), [
            '_token' => session()->token(),
        ]);

        $response->assertRedirect(route('admin.superadmin.drivers.index'));
        $this->assertSoftDeleted('drivers', ['id' => $driver->id]);

        // Check activity logging
        $this->assertDatabaseHas('superadmin_activity_logs', [
            'action' => 'delete',
            'resource_type' => 'driver',
        ]);
    }

    /** @test */
    public function superadmin_can_approve_driver()
    {
        $this->actingAs($this->superadmin, 'admin');

        $driver = Drivers::factory()->create([
            'verification_status' => 'pending',
        ]);

        $response = $this->post(route('admin.superadmin.drivers.approve', $driver), [
            '_token' => session()->token(),
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'verification_status' => 'verified',
        ]);

        // Check activity logging
        $this->assertDatabaseHas('superadmin_activity_logs', [
            'action' => 'approve',
            'resource_type' => 'driver',
        ]);
    }

    /** @test */
    public function superadmin_can_reject_driver()
    {
        $this->actingAs($this->superadmin, 'admin');

        $driver = Drivers::factory()->create([
            'verification_status' => 'pending',
        ]);

        $response = $this->post(route('admin.superadmin.drivers.reject', $driver), [
            '_token' => session()->token(),
            'reason' => 'Invalid documents',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'verification_status' => 'rejected',
        ]);

        // Check activity logging
        $this->assertDatabaseHas('superadmin_activity_logs', [
            'action' => 'reject',
            'resource_type' => 'driver',
        ]);
    }

    /** @test */
    public function superadmin_can_flag_driver()
    {
        $this->actingAs($this->superadmin, 'admin');

        $driver = Drivers::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->post(route('admin.superadmin.drivers.flag', $driver), [
            '_token' => session()->token(),
            'reason' => 'Suspicious activity',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'status' => 'flagged',
        ]);

        // Check activity logging
        $this->assertDatabaseHas('superadmin_activity_logs', [
            'action' => 'flag',
            'resource_type' => 'driver',
        ]);
    }

    /** @test */
    public function validation_fails_for_invalid_driver_data()
    {
        $this->actingAs($this->superadmin, 'admin');

        $invalidData = [
            '_token' => session()->token(),
            'first_name' => '', // Required
            'surname' => '', // Required
            'email' => 'invalid-email', // Invalid format
            'phone' => 'invalid-phone', // Invalid format
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ];

        $response = $this->post(route('admin.superadmin.drivers.store'), $invalidData);

        $response->assertSessionHasErrors(['first_name', 'surname', 'email', 'phone']);
    }

    /** @test */
    public function driver_search_functionality_works()
    {
        $this->actingAs($this->superadmin, 'admin');

        Drivers::factory()->create([
            'first_name' => 'John',
            'email' => 'john@example.com',
        ]);

        Drivers::factory()->create([
            'first_name' => 'Jane',
            'email' => 'jane@example.com',
        ]);

        $response = $this->get(route('admin.superadmin.drivers.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertViewHas('drivers');
        // The view should contain only the John driver
    }

    /** @test */
    public function driver_filtering_by_status_works()
    {
        $this->actingAs($this->superadmin, 'admin');

        Drivers::factory()->create(['status' => 'active']);
        Drivers::factory()->create(['status' => 'pending']);
        Drivers::factory()->create(['status' => 'flagged']);

        $response = $this->get(route('admin.superadmin.drivers.index', ['status' => 'active']));

        $response->assertStatus(200);
        $response->assertViewHas('drivers');
        // The view should contain only active drivers
    }
}
