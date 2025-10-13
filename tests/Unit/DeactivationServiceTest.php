<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DeactivationService;
use App\Models\DeactivationRequest;
use App\Models\OtpNotification;
use App\Models\DriverNormalized;
use App\Models\Company;
use App\Models\AdminUser;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class DeactivationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $deactivationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deactivationService = new DeactivationService();
    }

    /** @test */
    public function it_creates_driver_deactivation_request()
    {
        $admin = AdminUser::factory()->create();
        $driver = DriverNormalized::factory()->create(['is_current' => true]);

        $request = $this->deactivationService->createDriverDeactivationRequest(
            $driver->id,
            'Test reason',
            $admin
        );

        $this->assertInstanceOf(DeactivationRequest::class, $request);
        $this->assertEquals('driver', $request->user_type);
        $this->assertEquals($driver->id, $request->user_id);
        $this->assertEquals('Test reason', $request->reason);
        $this->assertEquals('pending', $request->status);
        $this->assertEquals($admin->id, $request->requested_by);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'user_type' => AdminUser::class,
            'user_id' => $admin->id,
            'action' => 'deactivation_requested',
        ]);
    }

    /** @test */
    public function it_fails_to_create_request_for_inactive_driver()
    {
        $admin = AdminUser::factory()->create();
        $driver = DriverNormalized::factory()->create(['is_current' => false]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Driver is not currently active');

        $this->deactivationService->createDriverDeactivationRequest(
            $driver->id,
            'Test reason',
            $admin
        );
    }

    /** @test */
    public function it_creates_company_deactivation_request()
    {
        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();

        $request = $this->deactivationService->createCompanyDeactivationRequest(
            $company->id,
            'Test reason',
            $admin
        );

        $this->assertInstanceOf(DeactivationRequest::class, $request);
        $this->assertEquals('company', $request->user_type);
        $this->assertEquals($company->id, $request->user_id);
        $this->assertEquals('Test reason', $request->reason);
        $this->assertEquals('pending', $request->status);
    }

    /** @test */
    public function it_handles_admin_ii_review()
    {
        $adminII = AdminUser::factory()->create();
        $request = DeactivationRequest::factory()->create(['status' => 'pending']);

        $result = $this->deactivationService->adminIIReview($request->id, $adminII);

        $this->assertEquals($request->id, $result->id);
        $this->assertEquals('Under Admin-I review', $result->notes);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'user_type' => AdminUser::class,
            'user_id' => $adminII->id,
            'action' => 'deactivation_reviewed',
            'metadata->admin_level' => 'admin_ii',
        ]);
    }

    /** @test */
    public function it_handles_admin_i_approval_with_otp_generation()
    {
        $adminI = AdminUser::factory()->create();
        $request = DeactivationRequest::factory()->create([
            'status' => 'pending',
            'user_type' => 'driver',
            'user_id' => 1
        ]);

        $result = $this->deactivationService->adminIApprove($request->id, $adminI);

        $this->assertArrayHasKey('request', $result);
        $this->assertArrayHasKey('otp', $result);
        $this->assertInstanceOf(OtpNotification::class, $result['otp']);
        $this->assertEquals('approved', $result['request']->status);
        $this->assertEquals($adminI->id, $result['request']->approved_by);
        $this->assertEquals('deactivation_confirmation', $result['otp']->type);
    }

    /** @test */
    public function it_generates_valid_otp()
    {
        $otp = $this->deactivationService->generateOTP('driver', 1, 'test_type');

        $this->assertInstanceOf(OtpNotification::class, $otp);
        $this->assertEquals('driver', $otp->user_type);
        $this->assertEquals(1, $otp->user_id);
        $this->assertEquals('test_type', $otp->type);
        $this->assertEquals('sent', $otp->status);
        $this->assertEquals(6, strlen($otp->otp_code));
        $this->assertNotNull($otp->expires_at);
    }

    /** @test */
    public function it_verifies_otp_and_deactivates_driver()
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

        $result = $this->deactivationService->verifyOTPAndDeactivate(
            $otp->id,
            '123456',
            '127.0.0.1',
            'Test Agent'
        );

        $this->assertInstanceOf(DeactivationRequest::class, $result);
        $this->assertEquals('Deactivated via OTP confirmation', $result->notes);

        // Check driver is deactivated
        $driver->refresh();
        $this->assertFalse($driver->is_current);
        $this->assertEquals('inactive', $driver->status);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'user_type' => 'driver',
            'user_id' => $driver->id,
            'action' => 'driver_deactivated',
        ]);
    }

    /** @test */
    public function it_verifies_otp_and_deactivates_company()
    {
        $company = Company::factory()->create(['status' => 'active']);
        $request = DeactivationRequest::factory()->create([
            'user_type' => 'company',
            'user_id' => $company->id,
            'status' => 'approved'
        ]);
        $otp = OtpNotification::factory()->create([
            'user_type' => 'company',
            'user_id' => $company->id,
            'otp_code' => '123456',
            'status' => 'sent',
            'expires_at' => now()->addMinutes(10)
        ]);

        $result = $this->deactivationService->verifyOTPAndDeactivate(
            $otp->id,
            '123456',
            '127.0.0.1',
            'Test Agent'
        );

        $this->assertInstanceOf(DeactivationRequest::class, $result);

        // Check company is deactivated
        $company->refresh();
        $this->assertEquals('inactive', $company->status);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'user_type' => 'company',
            'user_id' => $company->id,
            'action' => 'company_deactivated',
        ]);
    }

    /** @test */
    public function it_fails_verification_with_wrong_otp()
    {
        $otp = OtpNotification::factory()->create([
            'otp_code' => '123456',
            'status' => 'sent',
            'expires_at' => now()->addMinutes(10)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid or expired OTP');

        $this->deactivationService->verifyOTPAndDeactivate(
            $otp->id,
            '654321',
            '127.0.0.1',
            'Test Agent'
        );
    }

    /** @test */
    public function it_sends_otp_challenge_for_monitoring()
    {
        $otp = $this->deactivationService->sendOTPChallenge('driver', 1, 'suspicious_activity');

        $this->assertInstanceOf(OtpNotification::class, $otp);
        $this->assertEquals('security_challenge', $otp->type);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'user_type' => 'driver',
            'user_id' => 1,
            'action' => 'otp_challenge_sent',
            'metadata->reason' => 'suspicious_activity',
        ]);
    }

    /** @test */
    public function it_returns_correct_deactivation_stats()
    {
        // Create test data
        DriverNormalized::factory()->count(3)->create(['is_current' => false]);
        DriverNormalized::factory()->count(5)->create(['is_current' => true]);
        Company::factory()->count(2)->create(['status' => 'inactive']);
        Company::factory()->count(4)->create(['status' => 'active']);

        DeactivationRequest::factory()->count(7)->create(['status' => 'pending']);
        DeactivationRequest::factory()->count(3)->create([
            'status' => 'approved',
            'approved_at' => today()
        ]);

        $stats = $this->deactivationService->getDeactivationStats();

        $this->assertEquals(7, $stats['pending_requests']);
        $this->assertEquals(3, $stats['approved_today']);
        $this->assertEquals(3, $stats['total_deactivated_drivers']);
        $this->assertEquals(2, $stats['total_deactivated_companies']);
    }
}
