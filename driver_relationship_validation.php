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

class DriverRelationshipValidator
{
    private $results = [
        'drivers_with_full_linkage' => [],
        'drivers_missing_data' => [],
        'orphan_records' => [],
        'inconsistent_data' => [],
        'foreign_key_violations' => [],
        'crud_sync_issues' => [],
        'summary' => []
    ];

    private $tables = [
        'driver_next_of_kin' => DriverNextOfKin::class,
        'driver_performance' => DriverPerformance::class,
        'driver_banking_details' => DriverBankingDetail::class,
        'driver_documents' => DriverDocument::class,
        'driver_matches' => DriverMatch::class,
        'driver_category_requirements' => DriverCategoryRequirement::class,
    ];

    public function runFullValidation()
    {
        echo "🚀 STARTING DRIVER RELATIONSHIP VALIDATION\n";
        echo "==========================================\n\n";

        $this->checkDriverMappings();
        $this->checkOrphanRecords();
        $this->checkForeignKeyIntegrity();
        $this->testCRUDSynchronization();
        $this->generateSummary();

        $this->displayResults();
        $this->proposeFixes();

        echo "\n✅ VALIDATION COMPLETED\n";
    }

    private function checkDriverMappings()
    {
        echo "1️⃣ CHECKING DRIVER MAPPINGS\n";
        echo "-------------------------\n";

        $drivers = Driver::all();
        $totalDrivers = $drivers->count();

        echo "Total drivers: {$totalDrivers}\n\n";

        foreach ($drivers as $driver) {
            $missingTables = [];
            $hasAllData = true;

            foreach ($this->tables as $tableName => $modelClass) {
                $count = $modelClass::where('driver_id', $driver->id)->count();

                if ($count === 0) {
                    $missingTables[] = $tableName;
                    $hasAllData = false;
                }
            }

            if ($hasAllData) {
                $this->results['drivers_with_full_linkage'][] = [
                    'driver_id' => $driver->driver_id,
                    'name' => $driver->full_name,
                    'id' => $driver->id
                ];
            } else {
                $this->results['drivers_missing_data'][] = [
                    'driver_id' => $driver->driver_id,
                    'name' => $driver->full_name,
                    'id' => $driver->id,
                    'missing_tables' => $missingTables
                ];
            }
        }

        echo "Drivers with full linkage: " . count($this->results['drivers_with_full_linkage']) . "\n";
        echo "Drivers missing data: " . count($this->results['drivers_missing_data']) . "\n\n";
    }

    private function checkOrphanRecords()
    {
        echo "2️⃣ CHECKING ORPHAN RECORDS\n";
        echo "-----------------------\n";

        foreach ($this->tables as $tableName => $modelClass) {
            $orphans = $modelClass::leftJoin('drivers', $tableName . '.driver_id', '=', 'drivers.id')
                ->whereNull('drivers.id')
                ->select($tableName . '.*')
                ->get();

            if ($orphans->count() > 0) {
                $this->results['orphan_records'][$tableName] = $orphans->toArray();
                echo "⚠️  {$tableName}: {$orphans->count()} orphan records found\n";
            } else {
                echo "✅ {$tableName}: No orphan records\n";
            }
        }
        echo "\n";
    }

    private function checkForeignKeyIntegrity()
    {
        echo "3️⃣ CHECKING FOREIGN KEY INTEGRITY\n";
        echo "-------------------------------\n";

        // Check if foreign keys are properly defined
        $fkIssues = [];

        foreach ($this->tables as $tableName => $modelClass) {
            try {
                $invalidRecords = $modelClass::whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('drivers')
                          ->whereRaw('drivers.id = ' . $this->getTableName($query->getModel()) . '.driver_id');
                })->count();

                if ($invalidRecords > 0) {
                    $fkIssues[$tableName] = $invalidRecords;
                    echo "⚠️  {$tableName}: {$invalidRecords} records with invalid driver_id\n";
                } else {
                    echo "✅ {$tableName}: All records have valid driver_id\n";
                }
            } catch (\Exception $e) {
                echo "❌ Error checking {$tableName}: {$e->getMessage()}\n";
            }
        }

        $this->results['foreign_key_violations'] = $fkIssues;
        echo "\n";
    }

    private function testCRUDSynchronization()
    {
        echo "4️⃣ TESTING CRUD SYNCHRONIZATION\n";
        echo "-----------------------------\n";

        // Test Create synchronization
        echo "Testing CREATE synchronization...\n";
        $testDriver = $this->createTestDriver();
        if ($testDriver) {
            $this->verifyTransactionalDataCreation($testDriver);
            $this->testUpdateSynchronization($testDriver);
            $this->testDeleteSynchronization($testDriver);
        }

        echo "\n";
    }

    private function createTestDriver()
    {
        try {
            $driver = Driver::create([
                'driver_id' => 'DRV_TEST_' . time(),
                'first_name' => 'Test',
                'middle_name' => 'Validation',
                'surname' => 'Driver',
                'email' => 'test.validation.' . time() . '@example.com',
                'phone' => '+2348000000000',
                'password' => bcrypt('password123'),
                'status' => 'active',
                'verification_status' => 'pending',
                'is_active' => true,
                'is_available' => true,
                'kyc_status' => 'pending',
            ]);

            echo "✅ Test driver created: {$driver->driver_id}\n";
            return $driver;
        } catch (\Exception $e) {
            echo "❌ Failed to create test driver: {$e->getMessage()}\n";
            return null;
        }
    }

    private function verifyTransactionalDataCreation($driver)
    {
        $issues = [];

        // Test creating personal info
        try {
            DriverNextOfKin::create([
                'driver_id' => $driver->id,
                'name' => 'Test Next of Kin',
                'phone' => '+2348111111111',
                'relationship' => 'Brother',
                'is_primary' => true,
            ]);
            echo "✅ Personal info created\n";
        } catch (\Exception $e) {
            $issues[] = "Personal info creation failed: {$e->getMessage()}";
        }

        // Test creating performance data
        try {
            DriverPerformance::create([
                'driver_id' => $driver->id,
                'total_jobs_completed' => 0,
                'average_rating' => 0,
                'total_earnings' => 0,
            ]);
            echo "✅ Performance data created\n";
        } catch (\Exception $e) {
            $issues[] = "Performance creation failed: {$e->getMessage()}";
        }

        // Test creating banking details
        try {
            DriverBankingDetail::create([
                'driver_id' => $driver->id,
                'account_number' => '1234567890',
                'account_name' => 'Test Account',
                'bank_id' => 1,
                'is_primary' => true,
                'is_verified' => false,
            ]);
            echo "✅ Banking details created\n";
        } catch (\Exception $e) {
            $issues[] = "Banking details creation failed: {$e->getMessage()}";
        }

        if (!empty($issues)) {
            $this->results['crud_sync_issues']['create'] = $issues;
        }
    }

    private function testUpdateSynchronization($driver)
    {
        echo "Testing UPDATE synchronization...\n";

        try {
            // Update driver and check if related data is accessible
            $driver->update(['first_name' => 'Updated Test']);

            $personalInfo = $driver->personalInfo;
            $performance = $driver->performance;
            $bankingDetails = $driver->bankingDetails;

            if ($personalInfo && $performance && $bankingDetails->count() > 0) {
                echo "✅ Update synchronization working\n";
            } else {
                $this->results['crud_sync_issues']['update'] = ['Related data not accessible after driver update'];
            }
        } catch (\Exception $e) {
            $this->results['crud_sync_issues']['update'] = ["Update sync failed: {$e->getMessage()}"];
        }
    }

    private function testDeleteSynchronization($driver)
    {
        echo "Testing DELETE synchronization...\n";

        try {
            // Check soft delete behavior
            $driver->delete(); // Soft delete

            // Check if related data still exists
            $personalInfoCount = DriverNextOfKin::where('driver_id', $driver->id)->count();
            $performanceCount = DriverPerformance::where('driver_id', $driver->id)->count();
            $bankingCount = DriverBankingDetail::where('driver_id', $driver->id)->count();

            if ($personalInfoCount > 0 || $performanceCount > 0 || $bankingCount > 0) {
                echo "✅ Related data preserved after soft delete\n";
            } else {
                $this->results['crud_sync_issues']['delete'] = ['Related data deleted with driver'];
            }

            // Hard delete for cleanup
            $driver->forceDelete();
            DriverNextOfKin::where('driver_id', $driver->id)->delete();
            DriverPerformance::where('driver_id', $driver->id)->delete();
            DriverBankingDetail::where('driver_id', $driver->id)->delete();

            echo "✅ Test data cleaned up\n";
        } catch (\Exception $e) {
            $this->results['crud_sync_issues']['delete'] = ["Delete sync failed: {$e->getMessage()}"];
        }
    }

    private function generateSummary()
    {
        $this->results['summary'] = [
            'total_drivers' => Driver::count(),
            'drivers_with_full_linkage' => count($this->results['drivers_with_full_linkage']),
            'drivers_missing_data' => count($this->results['drivers_missing_data']),
            'tables_with_orphans' => count($this->results['orphan_records']),
            'tables_with_fk_violations' => count($this->results['foreign_key_violations']),
            'crud_sync_issues' => count($this->results['crud_sync_issues']),
        ];
    }

    private function displayResults()
    {
        echo "📊 VALIDATION RESULTS SUMMARY\n";
        echo "============================\n\n";

        echo "✅ Drivers with full relational linkage: {$this->results['summary']['drivers_with_full_linkage']}\n";
        echo "⚠️  Drivers missing linked data: {$this->results['summary']['drivers_missing_data']}\n";
        echo "🧩 Tables with inconsistent data: {$this->results['summary']['tables_with_orphans']}\n";
        echo "🔗 Foreign key violations: {$this->results['summary']['tables_with_fk_violations']}\n";
        echo "🔄 CRUD sync issues: {$this->results['summary']['crud_sync_issues']}\n\n";

        if (!empty($this->results['drivers_missing_data'])) {
            echo "⚠️  DRIVERS MISSING DATA:\n";
            foreach ($this->results['drivers_missing_data'] as $driver) {
                echo "   - {$driver['driver_id']} ({$driver['name']}): Missing " . implode(', ', $driver['missing_tables']) . "\n";
            }
            echo "\n";
        }

        if (!empty($this->results['orphan_records'])) {
            echo "🧩 ORPHAN RECORDS:\n";
            foreach ($this->results['orphan_records'] as $table => $records) {
                echo "   - {$table}: " . count($records) . " orphan records\n";
            }
            echo "\n";
        }
    }

    private function proposeFixes()
    {
        echo "🔧 PROPOSED FIXES\n";
        echo "================\n\n";

        if (!empty($this->results['drivers_missing_data'])) {
            echo "1. MISSING TRANSACTIONAL DATA FIXES:\n";
            echo "   - Run data migration script to create missing transactional records\n";
            echo "   - Execute: php artisan db:seed --class=DriversTransactionalDataSeeder\n\n";
        }

        if (!empty($this->results['orphan_records'])) {
            echo "2. ORPHAN RECORDS FIXES:\n";
            echo "   - Delete orphan records or assign them to appropriate drivers\n";
            echo "   - Run cleanup script: php artisan driver:cleanup-orphans\n\n";
        }

        if (!empty($this->results['foreign_key_violations'])) {
            echo "3. FOREIGN KEY INTEGRITY FIXES:\n";
            echo "   - Add foreign key constraints to database schema\n";
            echo "   - Update invalid driver_id references\n\n";
        }

        if (!empty($this->results['crud_sync_issues'])) {
            echo "4. CRUD SYNCHRONIZATION FIXES:\n";
            foreach ($this->results['crud_sync_issues'] as $operation => $issues) {
                echo "   - {$operation} operation issues: " . implode(', ', $issues) . "\n";
            }
            echo "   - Review and update model event listeners\n";
            echo "   - Implement database triggers for cascade operations\n\n";
        }

        echo "5. PREVENTIVE MEASURES:\n";
        echo "   - Add database constraints and triggers\n";
        echo "   - Implement validation in application layer\n";
        echo "   - Create automated integrity checks\n";
        echo "   - Set up monitoring alerts for data inconsistencies\n\n";
    }

    private function getTableName($model)
    {
        return $model->getTable();
    }
}

// Run the validation
$validator = new DriverRelationshipValidator();
$validator->runFullValidation();
