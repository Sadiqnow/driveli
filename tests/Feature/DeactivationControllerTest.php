<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeactivationRequest;
use App\Models\OtpNotification;
use App\Models\DriverNormalized;
use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class DeactivationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminUser::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    /** @test */
    public function it_displays_deactivation_dashboard()
    {
        // Create some test data
        DeactivationRequest::factory()->count(3)->create(['status' => 'pending']);

        $response = $this->get(route('admin.deactivation.index'));

        $response->assertStatus(200);
        $response->assertViewHas(['stats', 'pendingRequests']);
        $response->assertViewHas('stats', function ($stats) {
            return isset($stats['pending_requests']) &&
                   isset($stats['approved_today']) &&
                   isset($stats['total_deactivated_drivers']) &&
                   isset($stats['total_deactivated_companies']);
        });
    }

    /** @test */
    public function it_displays_create_form()
    {
        $response = $this->get(route('admin.deactivation.create', ['user_type' => 'driver']));

        $response->assertStatus(200);
        $response->assertViewHas('userType', 'driver');
    }

    /** @test */
    public function it_creates_driver_deactivation_request()
    {
        $driver = DriverNormalized::factory()->create(['is_current' => true]);

        $data = [
            'user_type' => 'driver',
            'user_id' => $driver->id,
            'reason' => 'Test deactivation reason'
        ];

        $response = $this->post(route('admin.deactivation.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('deactivation_requests', [
            'user_type' => 'driver',
            'user_id' => $driver->id,
            'reason' => 'Test deactivation reason',
            'status' => 'pending',
            'requested_by' => $this->admin->id
        ]);
    }

    /** @test */
    public function it_creates_company_deactivation_request()
    {
        $company = \App\Models\Company::factory()->create();

        $data = [
            'user_type' => 'company',
            'user_id' => $company->id,
            'reason' => 'Test deactivation reason'
        ];

        $response = $this->post(route('admin.deactivation.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('deactivation_requests', [
            'user_type' => 'company',
            'user_id' => $company->id,
            'reason' => 'Test deactivation reason',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_shows_deactivation_request_details()
    {
        $request = DeactivationRequest::factory()->create();

        $response = $this->get(route('admin.deactivation.show', $request));

        $response->assertStatus(200);
        $response->assertViewHas('deactivationRequest', $request);
    }

    /** @test */
    public function it_handles_admin_ii_review()
    {
        $request = DeactivationRequest::factory()->create(['status' => 'pending']);

        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('review-deactivations')->andReturn(true);

        $response = $this->post(route('admin.deactivation.review', $request));

        $response->assertRedirect();
        $request->refresh();
        $this->assertEquals('Under Admin-I review', $request->notes);
    }

    /** @test */
    public function it_handles_admin_i_approval()
    {
        $request = DeactivationRequest::factory()->create([
            'status' => 'pending',
            'user_type' => 'driver',
            'user_id' => 1
        ]);

        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('approve-deactivations')->andReturn(true);

        $response = $this->post(route('admin.deactivation.approve', $request), [
            'notes' => 'Approved for testing'
        ]);

        $response->assertRedirect();
        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals($this->admin->id, $request->approved_by);
        $this->assertEquals('Approved for testing', $request->notes);

        // Check OTP was created
        $this->assertDatabaseHas('otp_notifications', [
            'user_type' => 'driver',
            'user_id' => 1,
            'type' => 'deactivation_confirmation',
            'status' => 'sent'
        ]);
    }

    /** @test */
    public function it_handles_request_rejection()
    {
        $request = DeactivationRequest::factory()->create(['status' => 'pending']);

        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('approve-deactivations')->andReturn(true);

        $response = $this->post(route('admin.deactivation.reject', $request), [
            'rejection_reason' => 'Not eligible for deactivation'
        ]);

        $response->assertRedirect();
        $request->refresh();
        $this->assertEquals('rejected', $request->status);
        $this->assertEquals($this->admin->id, $request->approved_by);
        $this->assertEquals('Not eligible for deactivation', $request->notes);
    }

    /** @test */
    public function it_shows_otp_verification_form()
    {
        $otp = OtpNotification::factory()->create([
            'status' => 'sent',
            'expires_at' => now()->addMinutes(10)
        ]);

        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('manage-deactivations')->andReturn(true);

        $response = $this->get(route('admin.deactivation.otp', $otp));

        $response->assertStatus(200);
        $response->assertViewHas('otp', $otp);
    }

    /** @test */
    public function it_verifies_otp_and_completes_deactivation()
    {
        $driver = DriverNormalized::factory()->create(['is_current' => true]);
        $request = DeactivationRequest::factory()->create([
            'user_type' => 'driver',
            'user_id' => $driver->id,
            'status' => 'approved'
        ]);
        $otp = OtpNotification::factory()->create([
            'user_type' => 'driver',
            'user_id' => $driver->id,
            'otp_code' => '123456',
            'status' => 'sent',
            'expires_at' => now()->addMinutes(10)
        ]);

        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('manage-deactivations')->andReturn(true);

        $response = $this->post(route('admin.deactivation.verify-otp', $otp), [
            'otp_code' => '123456'
        ]);

        $response->assertRedirect();
        $otp->refresh();
        $this->assertEquals('verified', $otp->status);

        // Check driver was deactivated
        $driver->refresh();
        $this->assertFalse($driver->is_current);
        $this->assertEquals('inactive', $driver->status);
    }

    /** @test */
    public function it_fails_verification_with_wrong_otp()
    {
        $otp = OtpNotification::factory()->create([
            'otp_code' => '123456',
            'status' => 'sent',
            'expires_at' => now()->addMinutes(10)
        ]);

        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('manage-deactivations')->andReturn(true);

        $response = $this->post(route('admin.deactivation.verify-otp', $otp), [
            'otp_code' => '654321'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_resends_otp()
    {
        $otp = OtpNotification::factory()->create([
            'status' => 'sent',
            'user_type' => 'driver',
            'user_id' => 1
        ]);

        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('manage-deactivations')->andReturn(true);

        $response = $this->post(route('admin.deactivation.resend-otp', $otp));

        $response->assertRedirect();

        // Check new OTP was created
        $this->assertDatabaseHas('otp_notifications', [
            'user_type' => 'driver',
            'user_id' => 1,
            'type' => 'deactivation_confirmation',
            'status' => 'sent'
        ]);
    }

    /** @test */
    public function it_sends_otp_challenge_via_api()
    {
        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('monitor-drivers')->andReturn(true);

        $response = $this->postJson('/admin/deactivation/send-challenge', [
            'user_type' => 'driver',
            'user_id' => 1,
            'reason' => 'suspicious_activity'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'OTP challenge sent'
        ]);

        $this->assertDatabaseHas('otp_notifications', [
            'user_type' => 'driver',
            'user_id' => 1,
            'type' => 'security_challenge'
        ]);
    }

    /** @test */
    public function it_returns_deactivation_stats_via_api()
    {
        // Create test data
        DeactivationRequest::factory()->count(5)->create(['status' => 'pending']);

        // Mock the gate authorization
        Gate::shouldReceive('authorize')->with('manage-deactivations')->andReturn(true);

        $response = $this->getJson('/admin/deactivation/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'pending_requests',
                'approved_today',
                'total_deactivated_drivers',
                'total_deactivated_companies'
            ]
        ]);
    }

    /** @test */
    public function it_validates_request_data()
    {
        $response = $this->post(route('admin.deactivation.store'), [
            'user_type' => 'invalid',
            'user_id' => 'not_a_number',
            'reason' => ''
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['user_type', 'user_id', 'reason']);
    }
}
