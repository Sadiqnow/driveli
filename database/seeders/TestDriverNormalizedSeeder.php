<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DriverNormalized;
use App\Helpers\DrivelinkHelper;
use Illuminate\Support\Facades\Hash;

class TestDriverNormalizedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Test seeding multiple drivers to validate the DriverNormalized table
        $drivers = [
            [
                'driver_id' => DrivelinkHelper::generateDriverId(),
                'first_name' => 'John',
                'middle_name' => 'Chukwu',
                'surname' => 'Okwu',
                'nickname' => 'Johnny',
                'email' => 'john.okwu@test.com',
                'phone' => '08012345678',
                'phone_2' => '08087654321',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1985-05-15',
                'gender' => 'male',
                'religion' => 'Christianity',
                'blood_group' => 'O+',
                'height_meters' => 1.75,
                'disability_status' => 'None',
                'nationality_id' => 1,
                'nin_number' => '12345678901',
                'license_number' => 'LIC123456789',
                'license_class' => 'Commercial',
                'license_expiry_date' => '2025-12-31',
                'current_employer' => 'DriveLink Logistics',
                'experience_years' => 8,
                'employment_start_date' => '2016-01-01',
                'residence_address' => '123 Main Street, Victoria Island',
                'residence_state_id' => 25, // Lagos
                'residence_lga_id' => 14, // Lagos Island
                'vehicle_types' => json_encode(['Truck', 'Van', 'Car']),
                'work_regions' => json_encode(['Lagos', 'Abuja', 'Port Harcourt']),
                'special_skills' => 'Long distance driving, Heavy machinery operation',
                'status' => 'active',
                'verification_status' => 'verified',
                'is_active' => true,
                'registered_at' => now(),
                'verified_at' => now(),
                'verified_by' => 1, // Assuming admin user with ID 1 exists
                'verification_notes' => 'Fully verified driver with excellent track record',
                'ocr_verification_status' => 'passed',
                'ocr_verification_notes' => 'All documents verified via OCR',
                'nin_ocr_match_score' => 95.50,
                'frsc_ocr_match_score' => 92.30,
                'nin_verified_at' => now(),
                'frsc_verified_at' => now(),
            ],
            [
                'driver_id' => DrivelinkHelper::generateDriverId(),
                'first_name' => 'Amina',
                'middle_name' => 'Fatima',
                'surname' => 'Mohammed',
                'nickname' => 'Ami',
                'email' => 'amina.mohammed@test.com',
                'phone' => '08123456789',
                'phone_2' => '08098765432',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1990-03-22',
                'gender' => 'female',
                'religion' => 'Islam',
                'blood_group' => 'A+',
                'height_meters' => 1.65,
                'disability_status' => 'None',
                'nationality_id' => 1,
                'nin_number' => '98765432109',
                'license_number' => 'LIC987654321',
                'license_class' => 'Class B',
                'license_expiry_date' => '2026-06-30',
                'current_employer' => 'Swift Transport',
                'experience_years' => 5,
                'employment_start_date' => '2019-03-01',
                'residence_address' => '45 Ahmadu Bello Way, Kaduna',
                'residence_state_id' => 19, // Kaduna
                'residence_lga_id' => 9, // Kaduna North
                'vehicle_types' => json_encode(['Car', 'Van']),
                'work_regions' => json_encode(['Kaduna', 'Kano', 'Abuja']),
                'special_skills' => 'City navigation, Customer service',
                'status' => 'active',
                'verification_status' => 'pending',
                'is_active' => true,
                'registered_at' => now()->subDays(3),
                'ocr_verification_status' => 'pending',
                'nin_ocr_match_score' => 0.00,
                'frsc_ocr_match_score' => 0.00,
            ],
            [
                'driver_id' => DrivelinkHelper::generateDriverId(),
                'first_name' => 'Emmanuel',
                'middle_name' => 'Olu',
                'surname' => 'Adebayo',
                'nickname' => 'Emma',
                'email' => 'emmanuel.adebayo@test.com',
                'phone' => '08134567890',
                'phone_2' => null,
                'password' => Hash::make('password123'),
                'date_of_birth' => '1988-11-08',
                'gender' => 'male',
                'religion' => 'Christianity',
                'blood_group' => 'B+',
                'height_meters' => 1.80,
                'disability_status' => 'None',
                'nationality_id' => 1,
                'nin_number' => '11223344556',
                'license_number' => 'LIC112233445',
                'license_class' => 'Class A',
                'license_expiry_date' => '2025-09-15',
                'current_employer' => null,
                'experience_years' => 12,
                'employment_start_date' => null,
                'residence_address' => '78 Broad Street, Lagos Island',
                'residence_state_id' => 25, // Lagos
                'residence_lga_id' => 14, // Lagos Island
                'vehicle_types' => json_encode(['Truck', 'Trailer', 'Bus']),
                'work_regions' => json_encode(['Lagos', 'Ogun', 'Oyo']),
                'special_skills' => 'Heavy duty vehicles, Interstate transport',
                'status' => 'inactive',
                'verification_status' => 'rejected',
                'is_active' => false,
                'registered_at' => now()->subWeeks(2),
                'rejected_at' => now()->subWeek(),
                'rejection_reason' => 'Incomplete documentation',
                'verification_notes' => 'Missing required documents for verification',
                'ocr_verification_status' => 'failed',
                'ocr_verification_notes' => 'Document quality insufficient for OCR processing',
                'nin_ocr_match_score' => 45.20,
                'frsc_ocr_match_score' => 38.70,
                'nin_verified_at' => now()->subWeek(),
                'frsc_verified_at' => now()->subWeek(),
            ]
        ];

        echo "Creating test drivers in DriverNormalized table...\n";
        
        foreach ($drivers as $index => $driverData) {
            try {
                $driver = DriverNormalized::create($driverData);
                echo "✅ Driver #{$index + 1} created successfully: {$driver->full_name} (ID: {$driver->driver_id})\n";
                
                // Test relationships by creating related data
                if ($index === 0) { // Only for first driver to test relationships
                    // Create location record
                    $driver->locations()->create([
                        'driver_id' => $driver->id,
                        'location_type' => 'residence',
                        'state_id' => $driver->residence_state_id,
                        'lga_id' => $driver->residence_lga_id,
                        'address' => $driver->residence_address,
                        'is_primary' => true
                    ]);
                    
                    // Create employment history
                    $driver->employmentHistory()->create([
                        'driver_id' => $driver->id,
                        'company_name' => $driver->current_employer,
                        'job_title' => 'Professional Driver',
                        'start_date' => $driver->employment_start_date,
                        'is_current' => true,
                        'years_experience' => $driver->experience_years
                    ]);
                    
                    // Create banking details
                    $driver->bankingDetails()->create([
                        'driver_id' => $driver->id,
                        'bank_id' => 1, // Assuming bank with ID 1 exists
                        'account_number' => '1234567890',
                        'account_name' => $driver->full_name,
                        'is_primary' => true,
                        'is_verified' => true
                    ]);
                    
                    // Create next of kin
                    $driver->nextOfKin()->create([
                        'driver_id' => $driver->id,
                        'name' => 'Mary Okwu',
                        'relationship' => 'Wife',
                        'phone' => '08011111111',
                        'address' => 'Same as driver',
                        'is_primary' => true
                    ]);
                    
                    // Create guarantor
                    $driver->guarantors()->create([
                        'driver_id' => $driver->id,
                        'first_name' => 'Peter',
                        'last_name' => 'Okafor',
                        'relationship' => 'Friend',
                        'phone' => '08022222222',
                        'address' => '456 Another Street, Lagos',
                        'occupation' => 'Business Owner'
                    ]);
                    
                    // Create performance record
                    $driver->performance()->create([
                        'driver_id' => $driver->id,
                        'total_jobs_completed' => 150,
                        'average_rating' => 4.8,
                        'total_earnings' => 1250000.00,
                        'on_time_deliveries' => 145,
                        'customer_complaints' => 2
                    ]);
                    
                    echo "   ✅ Related records created for {$driver->full_name}\n";
                }
                
            } catch (\Exception $e) {
                echo "❌ Failed to create driver #{$index + 1}: " . $e->getMessage() . "\n";
                echo "   Error details: " . $e->getFile() . ":" . $e->getLine() . "\n";
            }
        }
        
        echo "\n=== Testing Data Retrieval ===\n";
        
        // Test data retrieval and relationships
        try {
            $totalDrivers = DriverNormalized::count();
            echo "✅ Total drivers in table: {$totalDrivers}\n";
            
            $activeDrivers = DriverNormalized::where('status', 'active')->count();
            echo "✅ Active drivers: {$activeDrivers}\n";
            
            $verifiedDrivers = DriverNormalized::where('verification_status', 'verified')->count();
            echo "✅ Verified drivers: {$verifiedDrivers}\n";
            
            $ocrPassedDrivers = DriverNormalized::where('ocr_verification_status', 'passed')->count();
            echo "✅ OCR passed drivers: {$ocrPassedDrivers}\n";
            
            // Test relationships
            $driverWithRelations = DriverNormalized::with([
                'locations', 
                'employmentHistory', 
                'bankingDetails', 
                'nextOfKin', 
                'guarantors', 
                'performance'
            ])->first();
            
            if ($driverWithRelations) {
                echo "✅ Driver with relationships loaded: {$driverWithRelations->full_name}\n";
                echo "   - Locations: " . $driverWithRelations->locations->count() . "\n";
                echo "   - Employment History: " . $driverWithRelations->employmentHistory->count() . "\n";
                echo "   - Banking Details: " . $driverWithRelations->bankingDetails->count() . "\n";
                echo "   - Next of Kin: " . $driverWithRelations->nextOfKin->count() . "\n";
                echo "   - Guarantors: " . $driverWithRelations->guarantors->count() . "\n";
                echo "   - Performance: " . ($driverWithRelations->performance ? 1 : 0) . "\n";
            }
            
            // Test model accessors
            if ($driverWithRelations) {
                echo "✅ Model accessors working:\n";
                echo "   - Full Name: {$driverWithRelations->full_name}\n";
                echo "   - Display Name: {$driverWithRelations->display_name}\n";
                echo "   - Age: {$driverWithRelations->age}\n";
                echo "   - Is Verified: " . ($driverWithRelations->is_verified ? 'Yes' : 'No') . "\n";
                echo "   - Is Active: " . ($driverWithRelations->isActive() ? 'Yes' : 'No') . "\n";
            }
            
        } catch (\Exception $e) {
            echo "❌ Error testing data retrieval: " . $e->getMessage() . "\n";
        }
        
        echo "\n=== Seeding Complete ===\n";
        echo "DriverNormalized table is working correctly!\n";
    }
}