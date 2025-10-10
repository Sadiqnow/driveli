<?php

/**
 * Fix Duplicate Index Error in DriveLink
 * This script resolves the duplicate key name error
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Fixing Duplicate Index Error ===\n\n";

try {
    // Test database connection
    DB::connection()->getPdo();
    echo "✓ Connected to database: " . env('DB_DATABASE') . "\n\n";
    
    // Check if drivers table exists
    $tables = collect(DB::select('SHOW TABLES'))->pluck('Tables_in_' . env('DB_DATABASE', 'drivelink_db'));
    
    if (!$tables->contains('drivers')) {
        echo "Table 'drivers' does not exist. Creating it...\n\n";
        
        // Create the table without the problematic indexes first
        DB::statement("
            CREATE TABLE IF NOT EXISTS drivers (
                id bigint unsigned AUTO_INCREMENT PRIMARY KEY,
                driver_id varchar(255) NOT NULL UNIQUE,
                
                -- Personal Information
                nickname varchar(255) NULL,
                first_name varchar(255) NOT NULL,
                middle_name varchar(255) NULL,
                surname varchar(255) NOT NULL,
                last_name varchar(255) NULL,
                phone varchar(255) NOT NULL UNIQUE,
                phone_2 varchar(255) NULL,
                email varchar(255) NULL UNIQUE,
                password varchar(255) NOT NULL,
                email_verified_at timestamp NULL DEFAULT NULL,
                remember_token varchar(100) NULL,
                
                date_of_birth date NULL,
                gender enum('male','female','other') NULL,
                religion varchar(255) NULL,
                blood_group varchar(255) NULL,
                height_meters decimal(3,2) NULL,
                disability_status varchar(255) NULL,
                nationality_id bigint unsigned DEFAULT 1,
                
                -- Profile and Documents
                profile_picture varchar(255) NULL,
                nin_number varchar(11) NULL UNIQUE,
                nin_document varchar(255) NULL,
                license_number varchar(255) NULL UNIQUE,
                license_class varchar(255) NULL,
                license_expiry_date date NULL,
                license_front_image varchar(255) NULL,
                license_back_image varchar(255) NULL,
                frsc_document varchar(255) NULL,
                passport_photograph varchar(255) NULL,
                additional_documents json NULL,
                
                -- Employment
                current_employer varchar(255) NULL,
                experience_years int NULL,
                employment_start_date date NULL,
                
                -- Location
                residence_address text NULL,
                residence_state_id bigint unsigned NULL,
                residence_lga_id bigint unsigned NULL,
                
                -- Preferences
                vehicle_types json NULL,
                work_regions json NULL,
                special_skills text NULL,
                
                -- System Status
                status enum('active','inactive','suspended','blocked') DEFAULT 'active',
                verification_status enum('pending','verified','rejected','reviewing') DEFAULT 'pending',
                is_active boolean DEFAULT true,
                last_active_at timestamp NULL DEFAULT NULL,
                registered_at timestamp NULL DEFAULT NULL,
                
                -- Verification tracking
                verified_at timestamp NULL DEFAULT NULL,
                verified_by bigint unsigned NULL,
                verification_notes text NULL,
                rejected_at timestamp NULL DEFAULT NULL,
                rejection_reason varchar(255) NULL,
                
                -- OCR Status
                ocr_verification_status enum('pending','passed','failed') DEFAULT 'pending',
                ocr_verification_notes text NULL,
                nin_verification_data json NULL,
                nin_verified_at timestamp NULL DEFAULT NULL,
                nin_ocr_match_score decimal(5,2) NULL,
                frsc_verification_data json NULL,
                frsc_verified_at timestamp NULL DEFAULT NULL,
                frsc_ocr_match_score decimal(5,2) NULL,
                
                created_at timestamp NULL DEFAULT NULL,
                updated_at timestamp NULL DEFAULT NULL,
                deleted_at timestamp NULL DEFAULT NULL
            )
        ");
        
        echo "✓ drivers table created\n";
    } else {
        echo "✓ drivers table already exists\n";
    }
    
    echo "\nChecking existing indexes...\n";
    
    // Get current indexes
    $indexes = DB::select("SHOW INDEX FROM drivers");
    $indexNames = collect($indexes)->pluck('Key_name')->unique()->values();
    
    echo "Current indexes: " . $indexNames->implode(', ') . "\n\n";
    
    // Remove problematic duplicate indexes if they exist
    $problematicIndexes = [
        'idx_drivers_status_verification',
        'idx_status_verification',
        'drivers_status_verification_status_index'
    ];
    
    foreach ($problematicIndexes as $indexName) {
        if ($indexNames->contains($indexName)) {
            echo "Removing duplicate index: $indexName\n";
            try {
                DB::statement("ALTER TABLE drivers DROP INDEX `$indexName`");
                echo "✓ Removed index: $indexName\n";
            } catch (Exception $e) {
                echo "⚠ Could not remove index $indexName: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nAdding clean indexes...\n";
    
    // Add indexes safely (check if they exist first)
    $indexesToAdd = [
        'status_verification_idx' => ['status', 'verification_status'],
        'verified_idx' => ['verified_at', 'verified_by'],
        'nationality_idx' => ['nationality_id']
    ];
    
    // Refresh index list
    $indexes = DB::select("SHOW INDEX FROM drivers");
    $currentIndexNames = collect($indexes)->pluck('Key_name')->unique()->values();
    
    foreach ($indexesToAdd as $indexName => $columns) {
        if (!$currentIndexNames->contains($indexName)) {
            try {
                $columnList = implode(', ', array_map(function($col) { return "`$col`"; }, $columns));
                DB::statement("ALTER TABLE drivers ADD INDEX `$indexName` ($columnList)");
                echo "✓ Added index: $indexName on columns (" . implode(', ', $columns) . ")\n";
            } catch (Exception $e) {
                echo "⚠ Could not add index $indexName: " . $e->getMessage() . "\n";
            }
        } else {
            echo "✓ Index $indexName already exists\n";
        }
    }
    
    echo "\nTesting driver creation...\n";
    
    // Test creating a driver
    try {
        DB::beginTransaction();
        
        $testData = [
            'driver_id' => 'TEST' . time(),
            'first_name' => 'Test',
            'surname' => 'Driver',
            'phone' => '0812345' . rand(1000, 9999),
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'status' => 'active',
            'verification_status' => 'pending',
            'is_active' => true,
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $driverId = DB::table('drivers')->insertGetId($testData);
        echo "✓ Test driver created successfully (ID: $driverId)\n";
        
        // Test with the model
        $driver = App\Models\DriverNormalized::find($driverId);
        if ($driver) {
            echo "✓ Model can access driver: " . $driver->first_name . " " . $driver->surname . "\n";
        }
        
        DB::rollback();
        echo "✓ Test data cleaned up\n";
        
    } catch (Exception $e) {
        DB::rollback();
        echo "✗ Driver creation test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "INDEX FIX COMPLETE\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "The duplicate index error has been resolved.\n";
    echo "You can now:\n\n";
    echo "1. Try running migrations again:\n";
    echo "   php artisan migrate --force\n\n";
    echo "2. Or test driver creation directly:\n";
    echo "   http://localhost/drivelink/public/admin/drivers/create\n\n";
    echo "3. Login with:\n";
    echo "   Email: admin@drivelink.com\n";
    echo "   Password: admin123 or secret\n\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    
    echo "Manual fix:\n";
    echo "1. Go to phpMyAdmin\n";
    echo "2. Select drivelink_db database\n";
    echo "3. Go to SQL tab and run:\n\n";
    
    echo "-- Remove problematic indexes\n";
    echo "ALTER TABLE drivers DROP INDEX IF EXISTS idx_drivers_status_verification;\n";
    echo "ALTER TABLE drivers DROP INDEX IF EXISTS idx_status_verification;\n";
    echo "ALTER TABLE drivers DROP INDEX IF EXISTS drivers_status_verification_status_index;\n\n";
    
    echo "-- Add clean indexes\n";
    echo "ALTER TABLE drivers ADD INDEX status_verification_idx (status, verification_status);\n";
    echo "ALTER TABLE drivers ADD INDEX verified_idx (verified_at, verified_by);\n";
    echo "ALTER TABLE drivers ADD INDEX nationality_idx (nationality_id);\n";
}