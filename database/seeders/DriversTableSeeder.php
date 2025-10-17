<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\DriverNextOfKin;
use App\Models\DriverPerformance;
use App\Models\DriverBankingDetail;
use App\Models\DriverDocument;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DriversTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $drivers = [
            [
                'driver_id' => 'DRV001',
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'surname' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+2348012345678',
                'phone_2' => '+2348023456789',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'verification_status' => 'verified',
                'is_active' => true,
                'is_available' => true,
                'kyc_status' => 'completed',
                'kyc_step' => 'completed',
                'kyc_retry_count' => 0,
                'verified_at' => now(),
                'verified_by' => null,
                'verification_notes' => 'Auto-verified for testing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_id' => 'DRV002',
                'first_name' => 'Jane',
                'middle_name' => 'Elizabeth',
                'surname' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+2348034567890',
                'phone_2' => '+2348045678901',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'verification_status' => 'verified',
                'is_active' => true,
                'is_available' => true,
                'kyc_status' => 'completed',
                'kyc_step' => 'completed',
                'kyc_retry_count' => 0,
                'verified_at' => now(),
                'verified_by' => null,
                'verification_notes' => 'Auto-verified for testing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_id' => 'DRV003',
                'first_name' => 'Michael',
                'middle_name' => 'James',
                'surname' => 'Johnson',
                'email' => 'michael.johnson@example.com',
                'phone' => '+2348056789012',
                'phone_2' => '+2348067890123',
                'password' => Hash::make('password123'),
                'status' => 'inactive',
                'verification_status' => 'pending',
                'is_active' => false,
                'is_available' => false,
                'kyc_status' => 'in_progress',
                'kyc_step' => 'step_1',
                'kyc_retry_count' => 1,
                'verification_notes' => 'Pending document verification',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($drivers as $driverData) {
            $driver = Driver::create($driverData);

            // Create transactional data for each driver
            $this->createTransactionalData($driver);
        }
    }

    private function createTransactionalData(Driver $driver)
    {
        // Personal Info (Next of Kin)
        DriverNextOfKin::create([
            'driver_id' => $driver->id,
            'name' => 'Jane ' . $driver->surname,
            'phone' => '+2348' . rand(10000000, 99999999),
            'relationship' => 'Sister',
            'is_primary' => true,
            'date_of_birth' => now()->subYears(rand(20, 50)),
            'gender' => $driver->id % 2 == 0 ? 'Female' : 'Male',
            'religion' => 'Christian',
            'blood_group' => 'O+',
            'height_meters' => rand(150, 200) / 100,
            'disability_status' => 'Fitted',
            'nationality_id' => 1,
            'nin_number' => '12345678901',
        ]);

        // Performance Data
        DriverPerformance::create([
            'driver_id' => $driver->id,
            'total_jobs_completed' => rand(10, 100),
            'average_rating' => rand(35, 50) / 10,
            'total_earnings' => rand(50000, 500000),
        ]);

        // Banking Details
        DriverBankingDetail::create([
            'driver_id' => $driver->id,
            'bank_id' => rand(1, 10),
            'account_name' => $driver->first_name . ' ' . $driver->surname,
            'account_number' => '0' . rand(100000000, 999999999),
            'is_verified' => $driver->verification_status === 'verified',
            'is_primary' => true,
        ]);

        // Documents
        $documentTypes = ['nin', 'license_front', 'license_back', 'profile_picture'];
        foreach ($documentTypes as $type) {
            DriverDocument::create([
                'driver_id' => $driver->id,
                'document_type' => $type,
                'document_path' => 'documents/' . $driver->driver_id . '/' . $type . '.jpg',
                'verification_status' => $driver->kyc_status === 'completed' ? 'approved' : 'pending',
                'verified_at' => $driver->kyc_status === 'completed' ? now() : null,
            ]);
        }
    }
}
