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

class FinalValidationReport
{
    public function generateReport()
    {
        echo "📋 FINAL DRIVER RELATIONSHIP VALIDATION REPORT\n";
        echo "==============================================\n\n";

        $this->generateSummary();
        $this->checkDataIntegrity();
        $this->checkConstraintsAndTriggers();
        $this->generateRecommendations();

        echo "\n✅ REPORT GENERATION COMPLETED\n";
    }

    private function generateSummary()
    {
        echo "📊 SUMMARY STATISTICS\n";
        echo "===================\n";

        $totalDrivers = Driver::count();
        $activeDrivers = Driver::where('is_active', true)->count();
        $verifiedDrivers = Driver::where('verification_status', 'verified')->count();

        echo "Total Drivers: {$totalDrivers}\n";
        echo "Active Drivers: {$activeDrivers}\n";
        echo "Verified Drivers: {$verifiedDrivers}\n\n";

        $this->showTableCounts();
    }

    private function showTableCounts()
    {
        $tables = [
            'drivers' => Driver::class,
            'driver_next_of_kin' => DriverNextOfKin::class,
            'driver_performance' => DriverPerformance::class,
            'driver_banking_details' => DriverBankingDetail::class,
            'driver_documents' => DriverDocument::class,
            'driver_matches' => DriverMatch::class,
            'driver_category_requirements' => DriverCategoryRequirement::class,
        ];

        echo "Table Record Counts:\n";
        foreach ($tables as $tableName => $modelClass) {
            $count = $modelClass::count();
            echo "   - {$tableName}: {$count} records\n";
        }
        echo "\n";
    }

    private function checkDataIntegrity()
    {
        echo "🔍 DATA INTEGRITY CHECK\n";
        echo "=====================\n";

        $this->checkDriverLinkage();
        $this->checkOrphanRecords();
        $this->checkForeignKeyIntegrity();
        $this->checkDataConsistency();
    }

    private function checkDriverLinkage()
    {
        echo "Driver Linkage Analysis:\n";

        $drivers = Driver::all();
        $fullyLinked = 0;
        $missingData = [];

        foreach ($drivers as $driver) {
            $missing = [];

            if (!DriverNextOfKin::where('driver_id', $driver->id)->exists()) {
                $missing[] = 'next_of_kin';
            }
            if (!DriverPerformance::where('driver_id', $driver->id)->exists()) {
                $missing[] = 'performance';
            }
            if (!DriverBankingDetail::where('driver_id', $driver->id)->exists()) {
                $missing[] = 'banking';
            }
            if (!DriverDocument::where('driver_id', $driver->id)->exists()) {
                $missing[] = 'documents';
            }
            if (!DriverCategoryRequirement::where('driver_id', $driver->id)->exists()) {
                $missing[] = 'requirements';
            }
            // Note: driver_matches is optional as it's transactional

            if (empty($missing)) {
                $fullyLinked++;
            } else {
                $missingData[] = [
                    'driver_id' => $driver->driver_id,
                    'name' => $driver->full_name,
                    'missing' => $missing
                ];
            }
        }

        echo "   ✅ Fully linked drivers: {$fullyLinked}\n";
        echo "   ⚠️  Drivers with missing data: " . count($missingData) . "\n";

        if (!empty($missingData)) {
            foreach ($missingData as $data) {
                echo "      - {$data['driver_id']} ({$data['name']}): Missing " . implode(', ', $data['missing']) . "\n";
            }
        }
        echo "\n";
    }

    private function checkOrphanRecords()
    {
        echo "Orphan Records Check:\n";

        $orphanChecks = [
            'driver_next_of_kin' => DriverNextOfKin::class,
            'driver_performance' => DriverPerformance::class,
            'driver_banking_details' => DriverBankingDetail::class,
            'driver_documents' => DriverDocument::class,
            'driver_matches' => DriverMatch::class,
            'driver_category_requirements' => DriverCategoryRequirement::class,
        ];

        $totalOrphans = 0;

        foreach ($orphanChecks as $tableName => $modelClass) {
            $orphans = $modelClass::leftJoin('drivers', $tableName . '.driver_id', '=', 'drivers.id')
                ->whereNull('drivers.id')
                ->count();

            if ($orphans > 0) {
                echo "   ❌ {$tableName}: {$orphans} orphan records\n";
                $totalOrphans += $orphans;
            } else {
                echo "   ✅ {$tableName}: No orphan records\n";
            }
        }

        echo "   Total orphan records: {$totalOrphans}\n\n";
    }

    private function checkForeignKeyIntegrity()
    {
        echo "Foreign Key Integrity:\n";

        try {
            $databaseName = config('database.connections.mysql.database');
            $fkConstraints = DB::select("
                SELECT TABLE_NAME, CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME LIKE 'driver_%'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                ORDER BY TABLE_NAME
            ", [$databaseName]);

            echo "   Active Foreign Key Constraints:\n";
            foreach ($fkConstraints as $constraint) {
                echo "      - {$constraint->TABLE_NAME}: {$constraint->CONSTRAINT_NAME}\n";
            }

            $expectedTables = ['driver_next_of_kin', 'driver_performance', 'driver_banking_details', 'driver_documents', 'driver_matches', 'driver_category_requirements'];
            $constrainedTables = array_unique(array_column($fkConstraints, 'TABLE_NAME'));

            $missingConstraints = array_diff($expectedTables, $constrainedTables);
            if (!empty($missingConstraints)) {
                echo "   ⚠️  Missing constraints for: " . implode(', ', $missingConstraints) . "\n";
            } else {
                echo "   ✅ All transactional tables have foreign key constraints\n";
            }

        } catch (\Exception $e) {
            echo "   ❌ Could not check foreign key constraints: {$e->getMessage()}\n";
        }
        echo "\n";
    }

    private function checkDataConsistency()
    {
        echo "Data Consistency Checks:\n";

        // Check for drivers with multiple primary banking details
        $multiplePrimaryBanking = DriverBankingDetail::select('driver_id')
            ->where('is_primary', true)
            ->groupBy('driver_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($multiplePrimaryBanking > 0) {
            echo "   ❌ Multiple primary banking details: {$multiplePrimaryBanking} drivers\n";
        } else {
            echo "   ✅ No drivers with multiple primary banking details\n";
        }

        // Check for drivers with multiple primary next of kin
        $multiplePrimaryKin = DriverNextOfKin::select('driver_id')
            ->where('is_primary', true)
            ->groupBy('driver_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($multiplePrimaryKin > 0) {
            echo "   ❌ Multiple primary next of kin: {$multiplePrimaryKin} drivers\n";
        } else {
            echo "   ✅ No drivers with multiple primary next of kin\n";
        }

        // Check for documents with invalid verification status
        $invalidDocStatus = DriverDocument::whereNotIn('verification_status', ['pending', 'approved', 'rejected'])->count();
        if ($invalidDocStatus > 0) {
            echo "   ❌ Invalid document verification status: {$invalidDocStatus} documents\n";
        } else {
            echo "   ✅ All documents have valid verification status\n";
        }

        echo "\n";
    }

    private function checkConstraintsAndTriggers()
    {
        echo "🔧 CONSTRAINTS AND TRIGGERS\n";
        echo "=========================\n";

        try {
            $databaseName = config('database.connections.mysql.database');
            $triggers = DB::select("
                SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_TIMING
                FROM INFORMATION_SCHEMA.TRIGGERS
                WHERE TRIGGER_SCHEMA = ?
                ORDER BY EVENT_OBJECT_TABLE, TRIGGER_NAME
            ", [$databaseName]);

            echo "Active Database Triggers:\n";
            foreach ($triggers as $trigger) {
                echo "   ✅ {$trigger->TRIGGER_NAME} ({$trigger->ACTION_TIMING} {$trigger->EVENT_MANIPULATION} on {$trigger->EVENT_OBJECT_TABLE})\n";
            }

            if (empty($triggers)) {
                echo "   ⚠️  No database triggers found\n";
            }

        } catch (\Exception $e) {
            echo "   ❌ Could not check triggers: {$e->getMessage()}\n";
        }
        echo "\n";
    }

    private function generateRecommendations()
    {
        echo "💡 RECOMMENDATIONS\n";
        echo "================\n";

        $recommendations = [];

        // Check if all drivers have complete data
        $incompleteDrivers = Driver::whereDoesntHave('personalInfo')
            ->orWhereDoesntHave('performance')
            ->orWhereDoesntHave('bankingDetails')
            ->orWhereDoesntHave('documents')
            ->count();

        if ($incompleteDrivers > 0) {
            $recommendations[] = "Complete missing transactional data for {$incompleteDrivers} drivers";
        }

        // Check for orphan records
        $orphanCount = 0;
        $tables = [DriverNextOfKin::class, DriverPerformance::class, DriverBankingDetail::class, DriverDocument::class, DriverMatch::class, DriverCategoryRequirement::class];
        foreach ($tables as $modelClass) {
            $orphans = $modelClass::leftJoin('drivers', $modelClass::getTableName() . '.driver_id', '=', 'drivers.id')
                ->whereNull('drivers.id')
                ->count();
            $orphanCount += $orphans;
        }

        if ($orphanCount > 0) {
            $recommendations[] = "Clean up {$orphanCount} orphan records across transactional tables";
        }

        // Check for triggers
        try {
            $triggerCount = DB::select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = ?", [config('database.connections.mysql.database')])[0]->count;
            if ($triggerCount == 0) {
                $recommendations[] = "Implement database triggers for automatic transactional data management";
            }
        } catch (\Exception $e) {
            $recommendations[] = "Verify database trigger implementation";
        }

        // General recommendations
        $recommendations[] = "Set up automated integrity monitoring and alerts";
        $recommendations[] = "Implement application-level validation for data consistency";
        $recommendations[] = "Create regular backup and recovery procedures";
        $recommendations[] = "Document data relationships and business rules";

        if (empty($recommendations)) {
            echo "✅ All systems operational - no immediate action required\n";
        } else {
            echo "Recommended Actions:\n";
            foreach ($recommendations as $i => $rec) {
                echo "   " . ($i + 1) . ". {$rec}\n";
            }
        }

        echo "\n";
    }
}

// Generate the final report
$report = new FinalValidationReport();
$report->generateReport();
