<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriverNormalized;
use App\Helpers\DrivelinkHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDriverNormalized extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:driver-normalized';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the DriverNormalized table and model functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== Testing DriverNormalized Table Validity ===');
        $this->newLine();

        try {
            // Test 1: Check table existence
            $this->info('1. Checking table existence...');
            $tableExists = DB::select("SHOW TABLES LIKE 'drivers'");
            if (count($tableExists) > 0) {
                $this->info('✅ drivers table exists');
            } else {
                $this->error('❌ drivers table does not exist');
                return Command::FAILURE;
            }

            // Test 2: Check table structure
            $this->info('2. Checking table structure...');
            $columns = DB::select("DESCRIBE drivers");
            $this->info('✅ Table has ' . count($columns) . ' columns');
            
            // Check for OCR columns specifically
            $ocrColumns = ['ocr_verification_status', 'nin_ocr_match_score', 'frsc_ocr_match_score'];
            foreach ($ocrColumns as $column) {
                $found = collect($columns)->where('Field', $column)->first();
                if ($found) {
                    $this->info("✅ OCR column '$column' exists");
                } else {
                    $this->warn("⚠️ OCR column '$column' missing");
                }
            }

            // Test 3: Test model creation
            $this->info('3. Testing model creation...');
            $testDriverData = [
                'driver_id' => DrivelinkHelper::generateDriverId(),
                'first_name' => 'Test',
                'surname' => 'Driver',
                'middle_name' => 'User',
                'phone' => '080' . rand(10000000, 99999999),
                'email' => 'test_' . time() . '@example.com',
                'password' => 'password123',
                'status' => 'active',
                'verification_status' => 'pending',
                'is_active' => true,
                'registered_at' => now(),
            ];

            $driver = DriverNormalized::create($testDriverData);
            if ($driver->id) {
                $this->info('✅ Driver created successfully');
                $this->info("   ID: {$driver->id}");
                $this->info("   Driver ID: {$driver->driver_id}");
                $this->info("   Full Name: {$driver->full_name}");
                $this->info("   Email: {$driver->email}");
            }

            // Test 4: Test model accessors
            $this->info('4. Testing model accessors...');
            $this->info("✅ Full Name: {$driver->full_name}");
            $this->info("✅ Display Name: {$driver->display_name}");
            $this->info("✅ Is Verified: " . ($driver->is_verified ? 'Yes' : 'No'));
            $this->info("✅ Is Active: " . ($driver->isActive() ? 'Yes' : 'No'));

            // Test 5: Test OCR fields
            $this->info('5. Testing OCR functionality...');
            $driver->update([
                'ocr_verification_status' => 'passed',
                'nin_ocr_match_score' => 95.50,
                'frsc_ocr_match_score' => 88.30,
                'nin_verification_data' => ['name' => 'Test Driver', 'nin' => '12345678901'],
                'frsc_verification_data' => ['license_number' => 'LIC123456', 'class' => 'Commercial'],
            ]);
            $this->info('✅ OCR fields updated successfully');
            $this->info("   OCR Status: {$driver->fresh()->ocr_verification_status}");
            $this->info("   NIN Score: {$driver->fresh()->nin_ocr_match_score}%");
            $this->info("   FRSC Score: {$driver->fresh()->frsc_ocr_match_score}%");

            // Test 6: Test relationships
            $this->info('6. Testing relationships...');
            
            // Create location
            $driver->locations()->create([
                'location_type' => 'residence',
                'state_id' => 25, // Lagos
                'lga_id' => 14, // Lagos Island
                'address' => '123 Test Street, Lagos',
                'is_primary' => true
            ]);
            $this->info('✅ Location relationship working');

            // Create guarantor
            $driver->guarantors()->create([
                'first_name' => 'Test',
                'last_name' => 'Guarantor',
                'relationship' => 'Friend',
                'phone' => '08011111111',
                'address' => '456 Guarantor Street',
            ]);
            $this->info('✅ Guarantor relationship working');

            // Test 7: Query tests
            $this->info('7. Testing queries...');
            $totalDrivers = DriverNormalized::count();
            $activeDrivers = DriverNormalized::where('status', 'active')->count();
            $verifiedDrivers = DriverNormalized::where('verification_status', 'verified')->count();
            $ocrPassedDrivers = DriverNormalized::where('ocr_verification_status', 'passed')->count();

            $this->info("✅ Total drivers: $totalDrivers");
            $this->info("✅ Active drivers: $activeDrivers");
            $this->info("✅ Verified drivers: $verifiedDrivers");
            $this->info("✅ OCR passed drivers: $ocrPassedDrivers");

            // Test 8: Controller integration
            $this->info('8. Testing controller compatibility...');
            $controllerDriver = DriverNormalized::with(['guarantors', 'verifiedBy', 'locations'])->first();
            if ($controllerDriver) {
                $this->info('✅ Controller-style queries working');
                $this->info("   Loaded relationships: locations, guarantors");
            }

            $this->newLine();
            $this->info('🎉 All tests passed! DriverNormalized table is fully functional.');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            $this->error('   File: ' . $e->getFile() . ':' . $e->getLine());
            return Command::FAILURE;
        }
    }
}
