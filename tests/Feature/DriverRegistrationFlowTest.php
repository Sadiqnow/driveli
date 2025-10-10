<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Drivers;
use App\Models\OtpVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DriverRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->startSession();
        // Override the default JSON accept header for web routes
        $this->withHeaders([
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ]);
    }

    public function test_complete_driver_registration_flow()
    {
        // Step 1: Register basic information (Step 1 of multi-step registration)
        $response = $this->post('/driver/register/step1', [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => 'on',
            'gender' => 'Male',
            'religion' => 'Christian',
            'blood_group' => 'O+',
            'height_meters' => '1.75',
            'disability_status' => 'None',
        ]);

        $response->assertRedirect(route('driver.register.step2'));

        // Check that registration data is stored in session
        $this->assertTrue(session()->has('driver_registration'));

        $registrationData = session('driver_registration');
        $this->assertEquals('john.doe@example.com', $registrationData['email']);
        $this->assertEquals('08012345678', $registrationData['phone']);
        $this->assertEquals(1, $registrationData['step']);

        // Step 2: Submit OTP verification (Step 2 of registration)
        $response = $this->post('/driver/register/step2', [
            'verification_type' => 'sms',
            'otp' => '123456', // Demo OTP
        ]);

        $response->assertRedirect(route('driver.register.step3'));

        // Check that session data is updated
        $registrationData = session('driver_registration');
        $this->assertTrue($registrationData['otp_verified']);
        $this->assertEquals(2, $registrationData['step']);

        // Step 3: Submit facial capture (Step 3 of registration)
        Storage::fake('public');

        // Create a fake image file for facial capture
        $facialImage = UploadedFile::fake()->create('facial.jpg', 1024, 'image/jpeg');

        $response = $this->post('/driver/register/step3', [
            'facial_image' => $facialImage,
        ]);

        $response->assertRedirect(route('driver.register.step4'));

        // Check that session data is updated with facial image
        $registrationData = session('driver_registration');
        $this->assertEquals(3, $registrationData['step']);
        $this->assertStringStartsWith('facial_', $registrationData['facial_image']);



        // Step 4: Complete registration with document uploads (Step 4 of registration)
        $licenseScan = UploadedFile::fake()->create('license.jpg', 1024, 'image/jpeg');
        $nationalId = UploadedFile::fake()->create('national_id.jpg', 1024, 'image/jpeg');
        $passportPhoto = UploadedFile::fake()->create('passport.jpg', 1024, 'image/jpeg');

        $response = $this->post('/driver/register/step4', [
            'license_scan' => $licenseScan,
            'national_id' => $nationalId,
            'passport_photo' => $passportPhoto,
            'terms' => '1',
            'data_accuracy' => '1',
        ]);

        $response->assertRedirect(route('driver.dashboard'));

        // Check that session data is cleared
        $this->assertFalse(session()->has('driver_registration'));

        // Check that driver is created with all data
        $this->assertDatabaseHas('drivers', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'surname' => 'Doe',
            'license_number' => 'DL1234567890',
            'gender' => 'Male',
            'religion' => 'Christian',
            'blood_group' => 'O+',
            'height_meters' => '1.75',
            'disability_status' => 'None',
        ]);

        $createdDriver = Drivers::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($createdDriver);
        $this->assertEquals('08012345678', $createdDriver->phone); // Model decrypts automatically
        $this->assertNotNull($createdDriver->phone_verified_at);
        $this->assertNotNull($createdDriver->email_verified_at);
        $this->assertStringStartsWith('DR', $createdDriver->driver_id);

        // Check that user is logged in
        $this->assertAuthenticated('driver');
        $this->assertEquals($createdDriver->id, auth('driver')->id());
    }

    public function test_step3_facial_capture_validation()
    {
        // Setup: Complete steps 1 and 2 first
        $this->post('/driver/register/step1', [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => 'on',
        ]);

        $this->post('/driver/register/step2', [
            'verification_type' => 'sms',
            'otp' => '123456',
        ]);

        // Test: Invalid file type
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->post('/driver/register/step3', [
            'facial_image' => $invalidFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('facial_image');

        // Test: File too large
        $largeFile = UploadedFile::fake()->create('large.jpg', 3000, 'image/jpeg');

        $response = $this->post('/driver/register/step3', [
            'facial_image' => $largeFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('facial_image');

        // Test: Valid file
        $validFile = UploadedFile::fake()->create('facial.jpg', 1024, 'image/jpeg');

        $response = $this->post('/driver/register/step3', [
            'facial_image' => $validFile,
        ]);

        $response->assertRedirect(route('driver.register.step4'));
    }

    public function test_step4_document_upload_validation()
    {
        // Setup: Complete steps 1, 2, and 3 first
        $this->post('/driver/register/step1', [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => 'on',
        ]);

        $this->post('/driver/register/step2', [
            'verification_type' => 'sms',
            'otp' => '123456',
        ]);

        $facialImage = UploadedFile::fake()->create('facial.jpg', 1024, 'image/jpeg');
        $this->post('/driver/register/step3', [
            'facial_image' => $facialImage,
        ]);

        // Test: Missing required documents
        $response = $this->withSession(['driver_registration' => [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'gender' => null,
            'religion' => null,
            'blood_group' => null,
            'height_meters' => null,
            'disability_status' => 'None',
            'step' => 3,
            'otp_verified' => true,
            'facial_image' => 'facial_123456.jpg',
            'created_at' => now(),
        ]])->post('/driver/register/step4', [
            'terms' => '1',
            'data_accuracy' => '1',
        ]);

        $response->assertRedirect();
        $response->assertInvalid(['license_scan', 'national_id', 'passport_photo']);

        // Test: Invalid file types
        $pdfFile = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->withSession(['driver_registration' => [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'gender' => null,
            'religion' => null,
            'blood_group' => null,
            'height_meters' => null,
            'disability_status' => 'None',
            'step' => 3,
            'otp_verified' => true,
            'facial_image' => 'facial_123456.jpg',
            'created_at' => now(),
        ]])->post('/driver/register/step4', [
            'license_scan' => $pdfFile,
            'national_id' => $pdfFile,
            'passport_photo' => $pdfFile,
            'terms' => '1',
            'data_accuracy' => '1',
        ]);

        $response->assertRedirect();
        $response->assertInvalid(['passport_photo']);

        // Test: Files too large
        $largeFile = UploadedFile::fake()->create('large.jpg', 3000, 'image/jpeg');

        $response = $this->withSession(['driver_registration' => [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'gender' => null,
            'religion' => null,
            'blood_group' => null,
            'height_meters' => null,
            'disability_status' => 'None',
            'step' => 3,
            'otp_verified' => true,
            'facial_image' => 'facial_123456.jpg',
            'created_at' => now(),
        ]])->post('/driver/register/step4', [
            'license_scan' => $largeFile,
            'national_id' => $largeFile,
            'passport_photo' => $largeFile,
            'terms' => '1',
            'data_accuracy' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['license_scan', 'national_id', 'passport_photo']);

        // Test: Missing terms acceptance
        $validFile = UploadedFile::fake()->create('document.jpg', 1024, 'image/jpeg');

        $response = $this->withSession(['driver_registration' => [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'gender' => null,
            'religion' => null,
            'blood_group' => null,
            'height_meters' => null,
            'disability_status' => 'None',
            'step' => 3,
            'otp_verified' => true,
            'facial_image' => 'facial_123456.jpg',
            'created_at' => now(),
        ]])->post('/driver/register/step4', [
            'license_scan' => $validFile,
            'national_id' => $validFile,
            'passport_photo' => $validFile,
            'data_accuracy' => '1',
        ]);

        $response->assertRedirect();
        $response->assertInvalid(['terms']);

        // Test: Valid submission
        $validFile = UploadedFile::fake()->create('document.jpg', 1024, 'image/jpeg');
        $passportFile = UploadedFile::fake()->create('passport.jpg', 1024, 'image/jpeg');

        $response = $this->withSession(['driver_registration' => [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'gender' => null,
            'religion' => null,
            'blood_group' => null,
            'height_meters' => null,
            'disability_status' => 'None',
            'step' => 3,
            'otp_verified' => true,
            'facial_image' => 'facial_123456.jpg',
            'created_at' => now(),
        ]])->post('/driver/register/step4', [
            'license_scan' => $validFile,
            'national_id' => $validFile,
            'passport_photo' => $passportFile,
            'terms' => '1',
            'data_accuracy' => '1',
        ]);

        $response->assertRedirect(route('driver.dashboard'));
    }

    public function test_registration_step_access_control()
    {
        // Test: Cannot access step 2 without completing step 1
        $response = $this->get('/driver/register/step2');
        $response->assertRedirect(route('driver.register'));

        // Test: Cannot access step 3 without completing step 2
        $response = $this->get('/driver/register/step3');
        $response->assertRedirect(route('driver.register'));

        // Test: Cannot access step 4 without completing step 3
        $response = $this->get('/driver/register/step4');
        $response->assertRedirect(route('driver.register'));

        // Test: Cannot submit step 2 without step 1 data
        $response = $this->post('/driver/register/step2', [
            'verification_type' => 'sms',
            'otp' => '123456',
        ]);
        $response->assertRedirect(route('driver.register'));

        // Test: Cannot submit step 3 without step 2 completion
        $facialImage = UploadedFile::fake()->create('facial.jpg', 1024, 'image/jpeg');
        $response = $this->post('/driver/register/step3', [
            'facial_image' => $facialImage,
        ]);
        $response->assertRedirect(route('driver.register'));

        // Test: Cannot submit step 4 without step 3 completion
        $validFile = UploadedFile::fake()->create('document.jpg', 512, 'image/jpeg');
        $response = $this->post('/driver/register/step4', [
            'license_scan' => $validFile,
            'national_id' => $validFile,
            'passport_photo' => $validFile,
            'terms' => '1',
            'data_accuracy' => '1',
        ]);
        $response->assertRedirect(route('driver.register'));
    }

    public function test_otp_verification_types()
    {
        // Setup: Complete step 1
        $this->post('/driver/register/step1', [
            'license_number' => 'DL1234567890',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => 'on',
        ]);

        // Test: SMS OTP verification
        $response = $this->post('/driver/register/step2', [
            'verification_type' => 'sms',
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('driver.register.step3'));

        // Reset session for email test
        session()->forget('driver_registration');

        // Setup: Complete step 1 again
        $this->post('/driver/register/step1', [
            'license_number' => 'DL1234567891',
            'date_of_birth' => '1990-01-01',
            'first_name' => 'Jane',
            'surname' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '08012345679',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => 'on',
        ]);

        // Test: Email OTP verification
        $response = $this->post('/driver/register/step2', [
            'verification_type' => 'email',
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('driver.register.step3'));
    }

    // Additional tests for KYC onboarding and admin approval can be added here
}
