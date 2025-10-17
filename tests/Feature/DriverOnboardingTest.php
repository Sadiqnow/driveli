<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Driver;
use App\Services\DriverOnboardingProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DriverOnboardingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $progressService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for authentication
        $this->admin = AdminUser::factory()->create();
        $this->actingAs($this->admin, 'admin');

        $this->progressService = app(DriverOnboardingProgressService::class);
    }

    /** @test */
    public function it_can_start_driver_onboarding()
    {
        $driverData = [
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+2348012345678'
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.start'), $driverData);

        $response->assertRedirect();

        $this->assertDatabaseHas('drivers', [
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+2348012345678',
            'status' => 'onboarding'
        ]);

        $driver = Driver::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($driver);
        $this->assertStringStartsWith('DRV-', $driver->driver_id);
    }

    /** @test */
    public function it_can_process_personal_info_step()
    {
        $driver = Driver::factory()->create(['status' => 'onboarding']);

        $personalData = [
            'first_name' => 'Jane',
            'middle_name' => 'Marie',
            'surname' => 'Smith',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'marital_status' => 'single',
            'nationality' => 'Nigerian',
            'state_of_origin' => 'Lagos',
            'address' => '123 Main Street, Lagos'
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.step.process', [
            'driver' => $driver->id,
            'step' => 'personal_info'
        ]), $personalData);

        $response->assertRedirect();

        $driver->refresh();
        $this->assertEquals('Jane', $driver->first_name);
        $this->assertEquals('Marie', $driver->middle_name);
        $this->assertEquals('Smith', $driver->surname);

        $this->assertDatabaseHas('driver_next_of_kin', [
            'driver_id' => $driver->id,
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'marital_status' => 'single'
        ]);
    }

    /** @test */
    public function it_can_process_contact_info_step()
    {
        $driver = Driver::factory()->create(['status' => 'onboarding']);

        $contactData = [
            'phone' => '+2348098765432',
            'phone_2' => '+2347012345678',
            'emergency_contact_name' => 'Mary Johnson',
            'emergency_contact_phone' => '+2348034567890',
            'emergency_contact_relationship' => 'sister',
            'address' => '456 Oak Avenue, Ikeja',
            'city' => 'Ikeja',
            'state' => 'Lagos State',
            'postal_code' => '100001'
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.step.process', [
            'driver' => $driver->id,
            'step' => 'contact_info'
        ]), $contactData);

        $response->assertRedirect();

        $driver->refresh();
        $this->assertEquals('+2348098765432', $driver->phone);
        $this->assertEquals('+2347012345678', $driver->phone_2);

        $this->assertDatabaseHas('driver_next_of_kin', [
            'driver_id' => $driver->id,
            'name' => 'Mary Johnson',
            'phone' => '+2348034567890',
            'relationship' => 'sister',
            'address' => '456 Oak Avenue, Ikeja',
            'city' => 'Ikeja',
            'state' => 'Lagos State'
        ]);
    }

    /** @test */
    public function it_can_process_documents_step()
    {
        Storage::fake('public');

        $driver = Driver::factory()->create(['status' => 'onboarding']);

        $documentData = [
            'profile_picture' => UploadedFile::fake()->image('profile.jpg'),
            'id_document' => UploadedFile::fake()->create('id_card.pdf', 1000),
            'drivers_license' => UploadedFile::fake()->create('license.pdf', 1500),
            'id_type' => 'national_id',
            'id_number' => '12345678901',
            'license_number' => 'DRV-LIC-001',
            'license_expiry' => now()->addYears(2)->format('Y-m-d')
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.step.process', [
            'driver' => $driver->id,
            'step' => 'documents'
        ]), $documentData);

        $response->assertRedirect();

        // Check documents were created
        $this->assertDatabaseHas('driver_documents', [
            'driver_id' => $driver->id,
            'document_type' => 'profile_picture'
        ]);

        $this->assertDatabaseHas('driver_documents', [
            'driver_id' => $driver->id,
            'document_type' => 'id_card'
        ]);

        $this->assertDatabaseHas('driver_documents', [
            'driver_id' => $driver->id,
            'document_type' => 'drivers_license'
        ]);
    }

    /** @test */
    public function it_can_process_banking_step()
    {
        $driver = Driver::factory()->create(['status' => 'onboarding']);

        $bankingData = [
            'account_name' => 'John Doe',
            'account_number' => '0123456789',
            'bank_name' => 'First Bank',
            'bank_code' => '011',
            'account_type' => 'savings',
            'branch_name' => 'Lagos Main Branch',
            'is_primary' => '1'
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.step.process', [
            'driver' => $driver->id,
            'step' => 'banking'
        ]), $bankingData);

        $response->assertRedirect();

        $this->assertDatabaseHas('driver_banking_details', [
            'driver_id' => $driver->id,
            'account_name' => 'John Doe',
            'account_number' => '0123456789',
            'bank_name' => 'First Bank',
            'bank_code' => '011',
            'account_type' => 'savings',
            'is_primary' => true
        ]);
    }

    /** @test */
    public function it_can_process_professional_step()
    {
        $driver = Driver::factory()->create(['status' => 'onboarding']);

        $professionalData = [
            'license_number' => 'DRV-LIC-2024-001',
            'license_expiry' => now()->addYears(3)->format('Y-m-d'),
            'years_of_experience' => '5-10',
            'vehicle_type' => 'sedan',
            'preferred_areas' => ['lagos_island', 'ikeja'],
            'work_schedule' => 'full_time',
            'has_guarantor' => '1',
            'guarantor_name' => 'David Wilson',
            'guarantor_phone' => '+2348056789012',
            'guarantor_relationship' => 'employer'
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.step.process', [
            'driver' => $driver->id,
            'step' => 'professional'
        ]), $professionalData);

        $response->assertRedirect();

        $this->assertDatabaseHas('driver_performance', [
            'driver_id' => $driver->id,
            'license_number' => 'DRV-LIC-2024-001',
            'years_of_experience' => '5-10',
            'vehicle_type' => 'sedan'
        ]);
    }

    /** @test */
    public function it_can_process_verification_step()
    {
        $driver = Driver::factory()->create(['status' => 'onboarding']);

        $verificationData = [
            'email_verification_code' => '123456',
            'phone_verification_code' => '789012',
            'accept_terms' => '1',
            'accept_data_processing' => '1'
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.step.process', [
            'driver' => $driver->id,
            'step' => 'verification'
        ]), $verificationData);

        $response->assertRedirect();

        $driver->refresh();
        $this->assertNotNull($driver->email_verified_at);
        $this->assertNotNull($driver->phone_verified_at);
    }

    /** @test */
    public function it_completes_onboarding_after_all_steps()
    {
        $driver = Driver::factory()->create(['status' => 'onboarding']);

        // Complete all required steps
        $this->completeAllOnboardingSteps($driver);

        // Check final status
        $driver->refresh();
        $this->assertEquals('pending_review', $driver->status);
        $this->assertEquals('submitted', $driver->kyc_status);
        $this->assertEquals(100, $driver->profile_completion_percentage);
    }

    /** @test */
    public function it_calculates_progress_correctly()
    {
        $driver = Driver::factory()->create(['status' => 'onboarding']);

        // Initially should be 0% or low
        $progress = $this->progressService->calculateProgress($driver);
        $this->assertGreaterThanOrEqual(0, $progress);

        // Complete personal info
        $driver->update(['first_name' => 'Test', 'surname' => 'User']);
        $driver->personalInfo()->create([
            'date_of_birth' => '1990-01-01',
            'gender' => 'male'
        ]);

        $progress = $this->progressService->calculateProgress($driver);
        $this->assertGreaterThan(0, $progress);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $driver = Driver::factory()->create(['status' => 'onboarding']);

        // Try to submit personal info without required fields
        $response = $this->post(route('admin.superadmin.drivers.onboarding.step.process', [
            'driver' => $driver->id,
            'step' => 'personal_info'
        ]), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['first_name', 'surname', 'date_of_birth', 'gender']);
    }

    /** @test */
    public function admin_can_approve_driver_application()
    {
        $driver = Driver::factory()->create([
            'status' => 'pending_review',
            'verification_status' => 'pending'
        ]);

        $reviewData = [
            'decision' => 'approve',
            'notes' => 'Application approved after review'
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.review.process', $driver), $reviewData);

        $response->assertRedirect();

        $driver->refresh();
        $this->assertEquals('verified', $driver->verification_status);
        $this->assertEquals('active', $driver->status);
        $this->assertTrue($driver->is_active);
        $this->assertTrue($driver->is_available);
        $this->assertNotNull($driver->verified_at);
        $this->assertEquals($this->admin->id, $driver->verified_by);
    }

    /** @test */
    public function admin_can_reject_driver_application()
    {
        $driver = Driver::factory()->create([
            'status' => 'pending_review',
            'verification_status' => 'pending'
        ]);

        $reviewData = [
            'decision' => 'reject',
            'notes' => 'Application rejected due to incomplete documents'
        ];

        $response = $this->post(route('admin.superadmin.drivers.onboarding.review.process', $driver), $reviewData);

        $response->assertRedirect();

        $driver->refresh();
        $this->assertEquals('rejected', $driver->verification_status);
        $this->assertEquals('rejected', $driver->status);
        $this->assertEquals('Application rejected due to incomplete documents', $driver->rejection_reason);
    }

    /**
     * Helper method to complete all onboarding steps for testing
     */
    private function completeAllOnboardingSteps(Driver $driver)
    {
        // Personal Info
        $driver->update(['first_name' => 'Test', 'surname' => 'User']);
        $driver->personalInfo()->create([
            'date_of_birth' => '1990-01-01',
            'gender' => 'male'
        ]);

        // Contact Info
        $driver->update(['phone' => '+2348012345678']);
        $driver->personalInfo()->update([
            'name' => 'Emergency Contact',
            'phone' => '+2348098765432',
            'relationship' => 'friend'
        ]);

        // Documents
        $driver->documents()->create([
            'document_type' => 'profile_picture',
            'document_path' => 'test/path/profile.jpg',
            'verification_status' => 'pending'
        ]);
        $driver->documents()->create([
            'document_type' => 'id_card',
            'document_path' => 'test/path/id.pdf',
            'verification_status' => 'pending'
        ]);
        $driver->documents()->create([
            'document_type' => 'drivers_license',
            'document_path' => 'test/path/license.pdf',
            'verification_status' => 'pending'
        ]);

        // Banking
        $driver->bankingDetails()->create([
            'account_number' => '0123456789',
            'account_name' => 'Test User',
            'bank_name' => 'Test Bank',
            'is_primary' => true
        ]);

        // Professional
        $driver->performance()->create([
            'license_number' => 'TEST-LIC-001',
            'license_expiry_date' => now()->addYears(2),
            'years_of_experience' => '1-2',
            'vehicle_type' => 'sedan'
        ]);

        // Verification
        $driver->update([
            'email_verified_at' => now(),
            'phone_verified_at' => now()
        ]);
    }
}
