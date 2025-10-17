<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

class DatabaseTriggerCreator
{
    private $triggers = [
        // Trigger to auto-create transactional records when a driver is created
        'trg_after_driver_insert' => [
            'table' => 'drivers',
            'timing' => 'AFTER',
            'event' => 'INSERT',
            'body' => "
                BEGIN
                    -- Create personal info record
                    INSERT INTO driver_next_of_kin (driver_id, name, phone, relationship, is_primary, created_at, updated_at)
                    VALUES (NEW.id, CONCAT('Next of Kin for ', NEW.first_name), '+2340000000000', 'Brother', 1, NOW(), NOW());

                    -- Create performance record
                    INSERT INTO driver_performance (driver_id, total_jobs_completed, average_rating, total_ratings, total_earnings, created_at, updated_at)
                    VALUES (NEW.id, 0, 0.00, 0, 0.00, NOW(), NOW());

                    -- Create default banking detail
                    INSERT INTO driver_banking_details (driver_id, account_number, account_name, bank_id, is_primary, is_verified, created_at, updated_at)
                    VALUES (NEW.id, '0000000000', CONCAT(NEW.first_name, ' ', NEW.surname), 1, 1, 0, NOW(), NOW());

                    -- Create default documents
                    INSERT INTO driver_documents (driver_id, document_type, document_path, verification_status, created_at, updated_at)
                    VALUES
                        (NEW.id, 'profile_picture', CONCAT('documents/', NEW.driver_id, '/profile.jpg'), 'pending', NOW(), NOW()),
                        (NEW.id, 'nin', CONCAT('documents/', NEW.driver_id, '/nin.jpg'), 'pending', NOW(), NOW()),
                        (NEW.id, 'license_front', CONCAT('documents/', NEW.driver_id, '/license_front.jpg'), 'pending', NOW(), NOW()),
                        (NEW.id, 'license_back', CONCAT('documents/', NEW.driver_id, '/license_back.jpg'), 'pending', NOW(), NOW());

                    -- Create category requirements
                    INSERT INTO driver_category_requirements (driver_id, category, country_id, required_licenses, required_certifications, required_documents, background_check_requirements, minimum_experience_years, vehicle_requirements, is_active, created_at, updated_at)
                    VALUES (NEW.id, 'standard', 1, '[\"drivers_license\"]', '[]', '[\"nin\",\"drivers_license\"]', '[]', 1, '[\"car\"]', 1, NOW(), NOW());
                END
            "
        ],

        // Trigger to soft-delete related records when driver is soft-deleted
        'trg_after_driver_update_soft_delete' => [
            'table' => 'drivers',
            'timing' => 'AFTER',
            'event' => 'UPDATE',
            'body' => "
                BEGIN
                    IF NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL THEN
                        -- Soft delete related records
                        UPDATE driver_next_of_kin SET deleted_at = NOW() WHERE driver_id = NEW.id AND deleted_at IS NULL;
                        UPDATE driver_performance SET deleted_at = NOW() WHERE driver_id = NEW.id AND deleted_at IS NULL;
                        UPDATE driver_banking_details SET deleted_at = NOW() WHERE driver_id = NEW.id AND deleted_at IS NULL;
                        UPDATE driver_documents SET deleted_at = NOW() WHERE driver_id = NEW.id AND deleted_at IS NULL;
                        UPDATE driver_matches SET deleted_at = NOW() WHERE driver_id = NEW.id AND deleted_at IS NULL;
                        UPDATE driver_category_requirements SET deleted_at = NOW() WHERE driver_id = NEW.id AND deleted_at IS NULL;
                    END IF;

                    IF NEW.deleted_at IS NULL AND OLD.deleted_at IS NOT NULL THEN
                        -- Restore related records
                        UPDATE driver_next_of_kin SET deleted_at = NULL WHERE driver_id = NEW.id;
                        UPDATE driver_performance SET deleted_at = NULL WHERE driver_id = NEW.id;
                        UPDATE driver_banking_details SET deleted_at = NULL WHERE driver_id = NEW.id;
                        UPDATE driver_documents SET deleted_at = NULL WHERE driver_id = NEW.id;
                        UPDATE driver_matches SET deleted_at = NULL WHERE driver_id = NEW.id;
                        UPDATE driver_category_requirements SET deleted_at = NULL WHERE driver_id = NEW.id;
                    END IF;
                END
            "
        ]
    ];

    public function createTriggers()
    {
        echo "⚙️  CREATING DATABASE TRIGGERS\n";
        echo "=============================\n\n";

        foreach ($this->triggers as $triggerName => $config) {
            echo "Creating trigger: {$triggerName}\n";

            try {
                // Drop trigger if exists
                DB::statement("DROP TRIGGER IF EXISTS {$triggerName}");

                // Create the trigger
                $sql = "CREATE TRIGGER {$triggerName} {$config['timing']} {$config['event']} ON {$config['table']} FOR EACH ROW {$config['body']}";

                DB::statement($sql);
                echo "   ✅ Created trigger: {$triggerName}\n";

            } catch (\Exception $e) {
                echo "   ❌ Failed to create {$triggerName}: {$e->getMessage()}\n";
            }
            echo "\n";
        }

        echo "✅ DATABASE TRIGGERS CREATED\n";
    }

    public function verifyTriggers()
    {
        echo "🔍 VERIFYING TRIGGERS\n";
        echo "===================\n\n";

        try {
            $databaseName = config('database.connections.mysql.database');
            $triggers = DB::select("
                SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_TIMING
                FROM INFORMATION_SCHEMA.TRIGGERS
                WHERE TRIGGER_SCHEMA = ?
                ORDER BY EVENT_OBJECT_TABLE, TRIGGER_NAME
            ", [$databaseName]);

            echo "Existing triggers:\n";
            foreach ($triggers as $trigger) {
                echo "   - {$trigger->TRIGGER_NAME} ({$trigger->ACTION_TIMING} {$trigger->EVENT_MANIPULATION} on {$trigger->EVENT_OBJECT_TABLE})\n";
            }

            $expectedTriggers = array_keys($this->triggers);
            $existingTriggerNames = array_column($triggers, 'TRIGGER_NAME');

            echo "\nTrigger verification:\n";
            foreach ($expectedTriggers as $trigger) {
                if (in_array($trigger, $existingTriggerNames)) {
                    echo "   ✅ {$trigger}\n";
                } else {
                    echo "   ❌ {$trigger} (missing)\n";
                }
            }

        } catch (\Exception $e) {
            echo "   ⚠️  Could not verify triggers: {$e->getMessage()}\n";
        }
    }

    public function testTriggers()
    {
        echo "🧪 TESTING TRIGGERS\n";
        echo "=================\n\n";

        try {
            // Test INSERT trigger
            echo "Testing INSERT trigger...\n";
            $testDriver = \App\Models\Driver::create([
                'driver_id' => 'DRV_TRIGGER_TEST_' . time(),
                'first_name' => 'Trigger',
                'middle_name' => 'Test',
                'surname' => 'Driver',
                'email' => 'trigger.test.' . time() . '@example.com',
                'phone' => '+2348111111111',
                'password' => bcrypt('password123'),
                'status' => 'active',
                'verification_status' => 'pending',
                'is_active' => true,
                'is_available' => true,
            ]);

            // Check if transactional records were created
            $hasPersonalInfo = \App\Models\DriverNextOfKin::where('driver_id', $testDriver->id)->exists();
            $hasPerformance = \App\Models\DriverPerformance::where('driver_id', $testDriver->id)->exists();
            $hasBanking = \App\Models\DriverBankingDetail::where('driver_id', $testDriver->id)->exists();
            $hasDocuments = \App\Models\DriverDocument::where('driver_id', $testDriver->id)->count() >= 4;
            $hasRequirements = \App\Models\DriverCategoryRequirement::where('driver_id', $testDriver->id)->exists();

            if ($hasPersonalInfo && $hasPerformance && $hasBanking && $hasDocuments && $hasRequirements) {
                echo "   ✅ INSERT trigger working correctly\n";
            } else {
                echo "   ❌ INSERT trigger failed - missing records\n";
            }

            // Test UPDATE (soft delete) trigger
            echo "Testing UPDATE (soft delete) trigger...\n";
            $testDriver->delete(); // Soft delete

            $softDeletedPersonal = \App\Models\DriverNextOfKin::where('driver_id', $testDriver->id)->whereNotNull('deleted_at')->exists();
            $softDeletedPerformance = \App\Models\DriverPerformance::where('driver_id', $testDriver->id)->whereNotNull('deleted_at')->exists();

            if ($softDeletedPersonal && $softDeletedPerformance) {
                echo "   ✅ UPDATE (soft delete) trigger working correctly\n";
            } else {
                echo "   ❌ UPDATE (soft delete) trigger failed\n";
            }

            // Test UPDATE (restore) trigger
            echo "Testing UPDATE (restore) trigger...\n";
            $testDriver->restore(); // Restore

            $restoredPersonal = \App\Models\DriverNextOfKin::where('driver_id', $testDriver->id)->whereNull('deleted_at')->exists();
            $restoredPerformance = \App\Models\DriverPerformance::where('driver_id', $testDriver->id)->whereNull('deleted_at')->exists();

            if ($restoredPersonal && $restoredPerformance) {
                echo "   ✅ UPDATE (restore) trigger working correctly\n";
            } else {
                echo "   ❌ UPDATE (restore) trigger failed\n";
            }

            // Cleanup
            $testDriver->forceDelete();
            \App\Models\DriverNextOfKin::where('driver_id', $testDriver->id)->delete();
            \App\Models\DriverPerformance::where('driver_id', $testDriver->id)->delete();
            \App\Models\DriverBankingDetail::where('driver_id', $testDriver->id)->delete();
            \App\Models\DriverDocument::where('driver_id', $testDriver->id)->delete();
            \App\Models\DriverCategoryRequirement::where('driver_id', $testDriver->id)->delete();

            echo "   ✅ Test cleanup completed\n";

        } catch (\Exception $e) {
            echo "   ❌ Trigger testing failed: {$e->getMessage()}\n";
        }
    }
}

// Run the trigger creation and testing
$creator = new DatabaseTriggerCreator();
$creator->createTriggers();
$creator->verifyTriggers();
$creator->testTriggers();
