<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DriverNormalized;
use Illuminate\Support\Facades\DB;

class DriverKycAndApprovalTest extends TestCase
{
    public function test_kyc_onboarding_flow()
    {
        // Seed lookup tables for the test
        $this->seedLookupTables();

        // Create a driver with verified phone and email
        $driver = DriverNormalized::factory()->create([
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
            'kyc_status' => 'pending',
        ]);

    // Get lookup data for the test (query by seeded identifiers to avoid collisions)
    $nationality = \App\Models\Nationality::where('code', 'NG')->first();
    $state = \App\Models\State::where('name', 'Lagos')->first();
    $lga = \App\Models\LocalGovernment::where('state_id', $state->id)->first();
    $bank = \App\Models\Bank::where('code', '011')->first();

        // Simulate KYC form submission step 1
        $response = $this->actingAs($driver, 'driver')->post('/driver/kyc/step-1', [
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'surname' => 'Doe',
            'date_of_birth' => '1990-01-15',
            'gender' => 'male',
            'marital_status' => 'single',
            'nationality_id' => $nationality->id,
            'state_of_origin' => $state->id,
            'lga_of_origin' => $lga->id,
            'religion' => 'Christian',
            'blood_group' => 'O+',
            'height_meters' => 1.75,
            'nin_number' => '12345678901',
            'license_number' => 'DL1234567890',
            'phone_2' => '+2348012345678',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '+2348098765432',
            'emergency_contact_relationship' => 'Sister',
        ]);
        $response->assertStatus(302); // Redirect to next step or success

        // Check if KYC status updated to in_progress after step 1
        $driver->refresh();
        $this->assertEquals('in_progress', $driver->kyc_status);
        $this->assertEquals(1, $driver->kyc_step);

        // Simulate KYC form submission step 2
    $response = $this->actingAs($driver, 'driver')->post('/driver/kyc/step-2', [
            'residential_address' => '123 Main Street, Lagos',
            'residence_state_id' => $state->id,
            'residence_lga_id' => $lga->id,
            'city' => 'Lagos',
            'postal_code' => '100001',
            'license_class' => 'B',
            // Use dynamic dates so tests don't fail as real time moves forward
            'license_issue_date' => now()->subYears(3)->format('Y-m-d'),
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
            'years_of_experience' => 3,
            'previous_company' => 'ABC Transport',
            'bank_id' => $bank->id,
            'account_number' => '0123456789',
            'account_name' => 'John Michael Doe',
            'bvn' => '12345678901',
            'has_vehicle' => true,
            'vehicle_type' => 'Toyota Camry',
            'vehicle_year' => 2020,
            'preferred_work_location' => 'Lagos',
            'available_for_night_shifts' => true,
            'available_for_weekend_work' => false,
            'special_skills' => 'Defensive driving',
        ]);
    $response->assertStatus(302);
    // Fail fast with validation errors if any - makes debugging assertions clearer
    $response->assertSessionHasNoErrors();

        // Check if KYC status remains in_progress after step 2
        $driver->refresh();
        $this->assertEquals('in_progress', $driver->kyc_status);
        $this->assertEquals(2, $driver->kyc_step);
    }

    public function test_admin_approval_workflow()
    {
        // Create a driver with completed KYC
        $driver = DriverNormalized::factory()->create([
            'kyc_status' => 'completed',
            'verification_status' => 'pending',
        ]);

        // Simulate admin approving the driver
        $response = $this->actingAsAdmin()->post('/admin/drivers/' . $driver->id . '/verify');
        $response->assertStatus(200);

        $driver->refresh();
        $this->assertEquals('approved', $driver->verification_status);
    }

    public function test_otp_edge_cases()
    {
        $driver = DriverNormalized::factory()->create([
            'phone_verified_at' => null,
            'email_verified_at' => null,
        ]);

        // Test with no OTP at all - should get "No active OTP found"
        $response = $this->actingAs($driver, 'driver')->postJson('/driver/verify-otp', [
            'verification_type' => 'sms',
            'otp' => '123456',
        ]);
        $response->assertJson([
            'success' => false,
            'message' => 'No active OTP found. Please request a new one.',
        ]);

        // Simulate multiple failed attempts with valid OTP
        DB::table('otp_verifications')->insert([
            'driver_id' => $driver->id,
            'verification_type' => 'sms',
            'otp_code' => bcrypt('123456'),
            'expires_at' => now()->addMinutes(10),
            'verified_at' => null,
            'attempts' => 3,
            'last_attempt_at' => now()->subMinutes(2),
        ]);

        $response = $this->actingAs($driver, 'driver')->postJson('/driver/verify-otp', [
            'verification_type' => 'sms',
            'otp' => '123456',
        ]);
        $response->assertJson([
            'success' => false,
            'message' => 'Too many failed attempts. Please request a new OTP.',
        ]);
    }

    protected function actingAsAdmin()
    {
        // Implement admin authentication for tests
        $admin = \App\Models\AdminUser::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    protected function seedLookupTables()
    {
        // Seed banks explicitly to ensure data persists
        $this->seed(\Database\Seeders\BanksSeeder::class);

        // Seed nationalities (idempotent)
        \App\Models\Nationality::firstOrCreate(
            ['code' => 'NG'],
            ['name' => 'Nigerian', 'is_active' => true]
        );

        // Seed states and local governments using Eloquent to ensure relations
        $state = \App\Models\State::firstOrCreate(
            ['name' => 'Lagos'],
            ['is_active' => true]
        );

        \App\Models\LocalGovernment::firstOrCreate(
            ['name' => 'Ikeja', 'state_id' => $state->id],
            ['is_active' => true]
        );

        // Seed banks (additional seeding for test-specific needs)
        \App\Models\Bank::firstOrCreate(
            ['code' => '011'],
            ['name' => 'First Bank', 'is_active' => true]
        );
    }
}
