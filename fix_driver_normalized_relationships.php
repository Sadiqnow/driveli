<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DriveLink Normalized Driver Relationships Fix ===\n\n";

try {
    DB::connection()->getPdo();
    echo "✓ Database connected: " . env('DB_DATABASE') . "\n\n";
    
    // Check tables existence
    $tables = collect(DB::select('SHOW TABLES'))->pluck('Tables_in_' . env('DB_DATABASE', 'drivelink_db'));
    
    $requiredTables = [
        'drivers',
        'guarantors', 
        'driver_locations',
        'driver_employment_history',
        'driver_next_of_kin',
        'driver_banking_details',
        'driver_preferences',
        'admin_users'
    ];
    
    echo "=== TABLE EXISTENCE CHECK ===\n";
    $missingTables = [];
    foreach ($requiredTables as $table) {
        if ($tables->contains($table)) {
            $count = DB::table($table)->count();
            echo "✓ $table ($count records)\n";
        } else {
            echo "✗ $table MISSING\n";
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo "\n⚠ Missing tables detected. You may need to run migrations.\n";
        echo "Run: php artisan migrate --force\n\n";
    }
    
    // Focus on drivers table
    if ($tables->contains('drivers')) {
        echo "\n=== DRIVERS_NORMALIZED FOREIGN KEY RELATIONSHIPS ===\n";
        
        // Check if the Guarantor model relationship works
        if ($tables->contains('guarantors')) {
            echo "Testing Guarantor -> DriverNormalized relationship...\n";
            try {
                // Check if guarantors table references driver_id correctly
                $guarantorColumns = collect(DB::select('DESCRIBE guarantors'))->pluck('Field');
                if ($guarantorColumns->contains('driver_id')) {
                    echo "✓ guarantors.driver_id column exists\n";
                    
                    // Test the relationship
                    $guarantorCount = DB::table('guarantors')->count();
                    echo "  Guarantors count: $guarantorCount\n";
                    
                    if ($guarantorCount > 0) {
                        // Check if any guarantors reference non-existent drivers
                        $orphanGuarantors = DB::select("
                            SELECT g.driver_id, COUNT(*) as count 
                            FROM guarantors g 
                            LEFT JOIN drivers d ON g.driver_id = d.driver_id 
                            WHERE d.driver_id IS NULL 
                            GROUP BY g.driver_id
                        ");
                        
                        if (count($orphanGuarantors) > 0) {
                            echo "  ⚠ Found orphan guarantors (referencing non-existent drivers):\n";
                            foreach ($orphanGuarantors as $orphan) {
                                echo "    Driver ID: {$orphan->driver_id} ({$orphan->count} guarantors)\n";
                            }
                        } else {
                            echo "  ✓ All guarantors reference valid drivers\n";
                        }
                    }
                } else {
                    echo "✗ guarantors.driver_id column missing\n";
                }
                
            } catch (Exception $e) {
                echo "✗ Guarantor relationship test failed: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n=== TESTING DRIVERNORMALIZED CREATION WITH RELATIONSHIPS ===\n";
        try {
            DB::beginTransaction();
            
            // Create a test driver
            $uniqueId = 'DR' . time();
            $testDriver = App\Models\DriverNormalized::create([
                'driver_id' => $uniqueId,
                'first_name' => 'Test',
                'surname' => 'Driver',
                'phone' => '0812345' . rand(1000, 9999),
                'password' => 'testpassword123',
                'status' => 'active',
                'verification_status' => 'pending',
                'is_active' => true,
                'registered_at' => now(),
            ]);
            
            echo "✓ Test driver created: {$testDriver->driver_id}\n";
            
            // Test adding a guarantor if the table exists
            if ($tables->contains('guarantors')) {
                echo "Testing guarantor creation...\n";
                $guarantor = App\Models\Guarantor::create([
                    'driver_id' => $testDriver->driver_id,  // Use driver_id, not id
                    'first_name' => 'Test',
                    'last_name' => 'Guarantor',
                    'relationship' => 'Friend',
                    'phone' => '0812345' . rand(1000, 9999),
                    'address' => 'Test Address',
                ]);
                echo "✓ Test guarantor created: {$guarantor->id}\n";
                
                // Test the relationship
                $driverGuarantors = $testDriver->guarantors()->count();
                echo "✓ Driver has {$driverGuarantors} guarantor(s) via relationship\n";
            }
            
            DB::rollback();
            echo "✓ Test data rolled back\n";
            
        } catch (Exception $e) {
            DB::rollback();
            echo "✗ Driver creation with relationships FAILED: " . $e->getMessage() . "\n";
            echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            
            // Specific error diagnosis
            if (strpos($e->getMessage(), 'driver_id') !== false) {
                echo "\n  DIAGNOSIS: The issue is likely with driver_id foreign key relationships.\n";
                echo "  The guarantors table might be expecting 'id' instead of 'driver_id'\n";
                echo "  Or the relationship definition is incorrect.\n";
            }
        }
        
        echo "\n=== CHECKING DRIVERNORMALIZED MODEL RELATIONSHIPS ===\n";
        try {
            $driver = new App\Models\DriverNormalized();
            $relationships = [
                'guarantors' => 'App\\Models\\Guarantor',
                'locations' => 'App\\Models\\DriverLocation',
                'employmentHistory' => 'App\\Models\\DriverEmploymentHistory',
                'nextOfKin' => 'App\\Models\\DriverNextOfKin',
                'bankingDetails' => 'App\\Models\\DriverBankingDetail',
                'preferences' => 'App\\Models\\DriverPreference',
            ];
            
            foreach ($relationships as $relation => $model) {
                if (method_exists($driver, $relation)) {
                    echo "✓ {$relation} relationship method exists\n";
                } else {
                    echo "✗ {$relation} relationship method missing\n";
                }
            }
            
        } catch (Exception $e) {
            echo "✗ Model relationship check failed: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "✗ drivers table does not exist!\n";
    }
    
    echo "\n=== DRIVER CREATION ISSUE DIAGNOSIS ===\n";
    echo "Common issues preventing driver creation:\n\n";
    
    echo "1. FOREIGN KEY CONSTRAINTS:\n";
    echo "   - Check if related tables (guarantors, driver_locations, etc.) exist\n";
    echo "   - Ensure foreign keys reference the correct column (driver_id vs id)\n\n";
    
    echo "2. RELATIONSHIP CONFIGURATION:\n";
    echo "   - DriverNormalized relationships should use 'driver_id' as foreign key\n";
    echo "   - Related models should reference DriverNormalized correctly\n\n";
    
    echo "3. VALIDATION ISSUES:\n";
    echo "   - Check unique constraints on phone, email, nin_number\n";
    echo "   - Ensure required fields are properly filled\n\n";
    
    echo "4. FILE UPLOAD ISSUES:\n";
    echo "   - Check storage/app/public/driver_documents directory exists\n";
    echo "   - Verify file upload permissions\n\n";
    
    echo "=== QUICK FIXES ===\n";
    echo "1. Ensure XAMPP MySQL is running\n";
    echo "2. Run: php artisan migrate --force\n";
    echo "3. Run: php artisan storage:link\n";
    echo "4. Check Laravel logs: storage/logs/laravel.log\n";
    echo "5. Try creating with minimal data first\n";
    
} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}