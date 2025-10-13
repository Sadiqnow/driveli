<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\DeactivationRequest;
use App\Models\DriverNormalized;
use App\Models\OtpNotification;
use App\Models\ActivityLog;
use App\Services\DeactivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DeactivationFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminI;
    protected $adminII;
    protected $driver;
    protected $deactivationService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions if they don't exist
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);

        // Create test users
        $this->adminI = AdminUser::factory()->create([
            'role' => 'admin_i',
        ]);

        $this->adminII = AdminUser::factory()->create([
            'role' => 'admin_ii',
        ]);

        $this->driver = DriverNormalized::factory()->create([
            'is_current' => true,
            'status' => 'active',
        ]);

        $this->deactivationService = app(DeactivationService::class);
    }

    /** @test */
    public function driver_can_submit_deactivation_request()
    {
        $reason = 'Personal reasons';

        $response = $this->actingAs($this->driver, 'api')
            ->postJson('/driver/deactivation-request', [
                'reason' => $reason,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Deactivation request submitted',
            ]);

        $this->assertDatabaseHas('deactivation_requests', [
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'action' => 'deactivation_requested',
        ]);
    }

    /** @test */
    public function admin_can_create_deactivation_request()
    {
        $reason = 'Policy violation';

        $response = $this->actingAs($this->adminII, 'admin')
            ->postJson('/admin/deactivation', [
                'user_type' => 'driver',
                'user_id' => $this->driver->id,
                'reason' => $reason,
            ]);

        $response->assertStatus(302); // Redirect after successful creation

        $this->assertDatabaseHas('deactivation_requests', [
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'reason' => $reason,
            'status' => 'pending',
            'requested_by' => $this->adminII->id,
        ]);
    }

    /** @test */
    public function admin_ii_can_review_deactivation_request()
    {
        $request = DeactivationRequest::create([
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'reason' => 'Test reason',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminII, 'admin')
            ->postJson("/admin/deactivation/{$request->id}/review");

        $response->assertStatus(302);

        $request->refresh();
        $this->assertEquals('Under Admin-I review', $request->notes);

        $this->assertDatabaseHas('activity_logs', [
            'user_type' => 'App\\Models\\AdminUser',
            'user_id' => $this->adminII->id,
            'action' => 'deactivation_reviewed',
        ]);
    }

    /** @test */
    public function admin_i_can_approve_deactivation_request()
    {
        $request = DeactivationRequest::create([
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'reason' => 'Test reason',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminI, 'admin')
            ->postJson("/admin/deactivation/{$request->id}/approve", [
                'notes' => 'Approved for testing',
            ]);

        $response->assertStatus(302);

        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($this->adminI->id, $request->approved_by);

        $this->assertDatabaseHas('otp_notifications', [
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'type' => 'deactivation_confirmation',
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function otp_can_be_verified_to_complete_deactivation()
    {
        // Create approved request
        $request = DeactivationRequest::create([
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'reason' => 'Test reason',
            'status' => 'approved',
            'approved_by' => $this->adminI->id,
        ]);

        // Create OTP
        $otp = OtpNotification::create([
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'otp_code' => '123456',
            'type' => 'deactivation_confirmation',
            'status' => 'sent',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->adminI, 'admin')
            ->postJson("/admin/deactivation/otp/{$otp->id}/verify", [
                'otp_code' => '123456',
            ]);

        $response->assertStatus(302);

        $request->refresh();
        $this->assertEquals('Deactivated via OTP confirmation', $request->notes);

        $this->driver->refresh();
        $this->assertFalse($this->driver->is_current);
        $this->assertEquals('inactive', $this->driver->status);

        $this->assertDatabaseHas('activity_logs', [
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'action' => 'driver_deactivated',
        ]);
    }

    /** @test */
    public function driver_can_submit_location_updates()
    {
        $locationData = [
            'latitude' => 6.5244,
            'longitude' => 3.3792,
            'accuracy' => 10.5,
            'device_info' => 'iPhone 12',
            'metadata' => [
                'speed' => 45.2,
                'heading' => 90.5,
                'battery_level' => 85,
            ],
        ];

        Queue::fake();

        $response = $this->actingAs($this->driver, 'api')
            ->postJson('/api/driver/location', $locationData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Location update queued successfully',
            ]);

        Queue::assertPushed(\App\Jobs\ProcessLocationUpdate::class, function ($job) use ($locationData) {
            return $job->locationData['latitude'] === $locationData['latitude'] &&
                   $job->locationData['longitude'] === $locationData['longitude'] &&
                   $job->driverId === $this->driver->id;
        });
    }

    /** @test */
    public function driver_can_verify_otp_challenge()
    {
        $otp = OtpNotification::create([
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'otp_code' => '654321',
            'type' => 'location_challenge',
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->driver, 'api')
            ->postJson('/api/driver/location/verify-challenge', [
                'otp_code' => '654321',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'OTP challenge verified successfully',
            ]);

        $otp->refresh();
        $this->assertEquals('verified', $otp->status);
        $this->assertNotNull($otp->verified_at);
    }

    /** @test */
    public function admin_can_monitor_driver_locations()
    {
        // Create some location data
        $this->driver->locations()->create([
            'latitude' => 6.5244,
            'longitude' => 3.3792,
            'accuracy' => 10.0,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->adminII, 'admin')
            ->getJson("/admin/monitoring/driver/{$this->driver->id}/locations");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'coordinates',
                        'accuracy',
                        'recorded_at',
                        'device_info',
                    ],
                ],
            ]);
    }

    /** @test */
    public function deactivation_request_requires_valid_driver()
    {
        $response = $this->actingAs($this->driver, 'api')
            ->postJson('/driver/deactivation-request', [
                'reason' => 'Test',
            ]);

        $response->assertStatus(200);

        // Try to create another request (should fail if driver is not active)
        $this->driver->update(['is_current' => false]);

        $response = $this->actingAs($this->driver, 'api')
            ->postJson('/driver/deactivation-request', [
                'reason' => 'Another test',
            ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function otp_expires_after_time_limit()
    {
        $otp = OtpNotification::create([
            'user_type' => 'driver',
            'user_id' => $this->driver->id,
            'otp_code' => '111111',
            'type' => 'deactivation_confirmation',
            'status' => 'sent',
            'expires_at' => now()->subMinutes(1), // Already expired
        ]);

        $response = $this->actingAs($this->adminI, 'admin')
            ->postJson("/admin/deactivation/otp/{$otp->id}/verify", [
                'otp_code' => '111111',
            ]);

        $response->assertStatus(302);

        // Should redirect with error (OTP expired)
        $this->assertStringContains('expired', session('error'));
    }
}
