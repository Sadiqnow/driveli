<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AdminUser;
use App\Models\DriverNormalized as Driver;
use App\Constants\DrivelinkConstants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_rate_limiting_on_admin_login()
    {
        $admin = AdminUser::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password')
        ]);

        // Attempt multiple failed logins
        for ($i = 0; $i < DrivelinkConstants::AUTH_RATE_LIMIT_ATTEMPTS + 1; $i++) {
            $response = $this->post('/admin/login', [
                'email' => 'admin@test.com',
                'password' => 'wrong-password'
            ]);
        }

        // Should be rate limited on final attempt
        $response->assertStatus(429);
    }

    public function test_sql_injection_protection_in_driver_search()
    {
        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin');

    $maliciousInput = "'; DROP TABLE drivers; --";
        
        $response = $this->get('/admin/drivers?search=' . urlencode($maliciousInput));
        
        // Should not cause SQL error and return normal response
        $response->assertStatus(200);
        
        // Verify table still exists
    $this->assertDatabaseHas('drivers', []);
    }

    public function test_sensitive_data_encryption()
    {
        $driver = Driver::create([
            'driver_id' => 'DRV202500001',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@test.com',
            'phone' => '08012345678',
            'nin_number' => '12345678901',
            'status' => DrivelinkConstants::DRIVER_STATUS_PENDING,
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_PENDING,
        ]);

        // Check that sensitive data is encrypted in database
    $rawDriver = \DB::table('drivers')->where('id', $driver->id)->first();
        
        // Phone and NIN should be encrypted (not equal to original values)
        $this->assertNotEquals('08012345678', $rawDriver->phone);
        $this->assertNotEquals('12345678901', $rawDriver->nin_number);
        
        // But should decrypt properly when accessed through model
        $this->assertEquals('08012345678', $driver->phone);
        $this->assertEquals('12345678901', $driver->nin_number);
    }

    public function test_api_authentication_required()
    {
        $response = $this->get('/api/admin/profile');
        $response->assertStatus(401);

        $response = $this->get('/api/driver/profile');
        $response->assertStatus(401);
    }

    public function test_csrf_protection_on_forms()
    {
        $admin = AdminUser::factory()->create();
        
        // Attempt POST without CSRF token
        $response = $this->post('/admin/drivers', [
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@test.com',
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    public function test_admin_role_authorization()
    {
        $viewerAdmin = AdminUser::factory()->create([
            'role' => DrivelinkConstants::ADMIN_ROLE_VIEWER
        ]);

        $this->actingAs($viewerAdmin, 'admin');

        // Viewer should not be able to create drivers
        $response = $this->post('/admin/drivers', [
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@test.com',
        ]);

        $response->assertStatus(403);
    }

    public function test_password_security_requirements()
    {
        $response = $this->post('/api/driver/register', [
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@test.com',
            'phone' => '08012345678',
            'password' => '123', // Weak password
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_file_upload_security()
    {
        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin');

        $driver = Driver::factory()->create();

        // Attempt to upload malicious file
        $maliciousFile = \Illuminate\Http\Testing\File::create('malicious.php', 100);

        $response = $this->put("/admin/drivers/{$driver->id}/documents", [
            'nin_document' => $maliciousFile,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nin_document');
    }

    public function test_session_fixation_protection()
    {
        $admin = AdminUser::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password')
        ]);

        $oldSessionId = session()->getId();

        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password'
        ]);

        $newSessionId = session()->getId();

        // Session ID should change after login
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    public function test_xss_protection_in_output()
    {
        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin');

        $driver = Driver::create([
            'driver_id' => 'DRV202500001',
            'first_name' => '<script>alert("xss")</script>',
            'surname' => 'Doe',
            'email' => 'john@test.com',
            'status' => DrivelinkConstants::DRIVER_STATUS_PENDING,
        ]);

        $response = $this->get("/admin/drivers/{$driver->id}");
        
        // Should not contain unescaped script tags
        $response->assertDontSee('<script>alert("xss")</script>', false);
        // Should contain escaped version
        $response->assertSee('&lt;script&gt;alert("xss")&lt;/script&gt;', false);
    }

    public function test_brute_force_protection_logging()
    {
        \Log::spy();

        // Attempt multiple failed logins
        for ($i = 0; $i < 6; $i++) {
            $this->post('/admin/login', [
                'email' => 'nonexistent@test.com',
                'password' => 'wrong-password'
            ]);
        }

        // Should log brute force attempt
        \Log::shouldHaveReceived('alert')
            ->with('Potential brute force attack detected', \Mockery::type('array'));
    }
}