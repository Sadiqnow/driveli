<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\Driver;
use App\Models\DriverNextOfKin;
use App\Models\DriverPerformance;
use App\Models\DriverBankingDetail;
use App\Models\DriverDocument;
use App\Models\DriverMatch;
use App\Models\DriverCategoryRequirement;
use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

class MissingTransactionalDataFixer
{
    private $tables = [
        'driver_next_of_kin' => DriverNextOfKin::class,
        'driver_performance' => DriverPerformance::class,
        'driver_banking_details' => DriverBankingDetail::class,
        'driver_documents' => DriverDocument::class,
        'driver_matches' => DriverMatch::class,
        'driver_category_requirements' => DriverCategoryRequirement::class,
    ];

    public function runFixes()
    {
        echo "🔧 FIXING MISSING TRANSACTIONAL DATA\n";
        echo "===================================\n\n";

        $this->identifyMissingData();
        $this->createMissingTransactionalRecords();
        $this->verifyFixes();

        echo "\n✅ FIXES COMPLETED\n";
    }

    private function identifyMissingData()
    {
        echo "1️⃣ IDENTIFYING MISSING DATA\n";
        echo "-------------------------\n";

        $drivers = Driver::all();

        foreach ($drivers as $driver) {
            echo "Checking driver: {$driver->driver_id} ({$driver->full_name})\n";

            foreach ($this->tables as $tableName => $modelClass) {
                $exists = $modelClass::where('driver_id', $driver->id)->exists();

                if (!$exists) {
                    echo "   ⚠️  Missing: {$tableName}\n";
                } else {
                    echo "   ✅ Has: {$tableName}\n";
                }
            }
            echo "\n";
        }
    }

    private function createMissingTransactionalRecords()
    {
        echo "2️⃣ CREATING MISSING RECORDS\n";
        echo "-------------------------\n";

        $drivers = Driver::all();
        $created = 0;

        foreach ($drivers as $driver) {
            echo "Processing driver: {$driver->driver_id}\n";

            // Create missing personal info
            if (!DriverNextOfKin::where('driver_id', $driver->id)->exists()) {
                DriverNextOfKin::create([
                    'driver_id' => $driver->id,
                    'name' => $this->generateNextOfKinName($driver),
                    'phone' => $this->generatePhoneNumber(),
                    'relationship' => 'Sister',
                    'is_primary' => true,
                ]);
                echo "   ✅ Created personal info\n";
                $created++;
            }

            // Create missing performance data
            if (!DriverPerformance::where('driver_id', $driver->id)->exists()) {
                DriverPerformance::create([
                    'driver_id' => $driver->id,
                    'total_jobs_completed' => 0,
                    'average_rating' => 0.0,
                    'total_ratings' => 0,
                    'total_earnings' => 0.00,
                ]);
                echo "   ✅ Created performance data\n";
                $created++;
            }

            // Create missing banking details
            if (!DriverBankingDetail::where('driver_id', $driver->id)->exists()) {
                DriverBankingDetail::create([
                    'driver_id' => $driver->id,
                    'account_number' => $this->generateAccountNumber(),
                    'account_name' => $driver->full_name,
                    'bank_id' => 1, // Default bank
                    'is_primary' => true,
                    'is_verified' => false,
                ]);
                echo "   ✅ Created banking details\n";
                $created++;
            }

            // Create missing documents
            $requiredDocuments = ['nin', 'license_front', 'license_back', 'profile_picture'];
            foreach ($requiredDocuments as $docType) {
                if (!DriverDocument::where('driver_id', $driver->id)->where('document_type', $docType)->exists()) {
                    DriverDocument::create([
                        'driver_id' => $driver->id,
                        'document_type' => $docType,
                        'document_path' => "documents/{$driver->driver_id}/{$docType}.jpg",
                        'verification_status' => 'pending',
                    ]);
                    echo "   ✅ Created {$docType} document\n";
                    $created++;
                }
            }

            // Create missing category requirements (optional - may not be needed for all drivers)
            if (!DriverCategoryRequirement::where('driver_id', $driver->id)->exists()) {
                DriverCategoryRequirement::create([
                    'driver_id' => $driver->id,
                    'category' => 'standard',
                    'country_id' => 1, // Nigeria
                    'required_licenses' => ['drivers_license'],
                    'required_certifications' => [],
                    'required_documents' => ['nin', 'drivers_license'],
                    'background_check_requirements' => [],
                    'minimum_experience_years' => 1,
                    'vehicle_requirements' => ['car'],
                    'is_active' => true,
                ]);
                echo "   ✅ Created category requirements\n";
                $created++;
            }

            echo "\n";
        }

        echo "Total records created: {$created}\n\n";
    }

    private function verifyFixes()
    {
        echo "3️⃣ VERIFYING FIXES\n";
        echo "----------------\n";

        $drivers = Driver::all();
        $fullyLinked = 0;

        foreach ($drivers as $driver) {
            $hasAllData = true;

            foreach ($this->tables as $tableName => $modelClass) {
                if (!$modelClass::where('driver_id', $driver->id)->exists()) {
                    $hasAllData = false;
                    echo "   ❌ Still missing {$tableName} for {$driver->driver_id}\n";
                }
            }

            if ($hasAllData) {
                $fullyLinked++;
                echo "   ✅ {$driver->driver_id} now has full linkage\n";
            }
        }

        echo "\nDrivers with full relational linkage: {$fullyLinked} / " . $drivers->count() . "\n";
    }

    private function generateNextOfKinName($driver)
    {
        $names = ['Mary', 'Sarah', 'Elizabeth', 'Grace', 'Blessing', 'Peace'];
        return $names[array_rand($names)] . ' ' . $driver->surname;
    }

    private function generatePhoneNumber()
    {
        return '+234' . rand(7000000000, 9999999999);
    }

    private function generateAccountNumber()
    {
        return '0' . rand(100000000, 999999999);
    }
}

// Run the fixes
$fixer = new MissingTransactionalDataFixer();
$fixer->runFixes();
