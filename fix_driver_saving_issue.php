<?php

/**
 * Fix Driver Saving Issue Script
 * Identifies and fixes problems preventing drivers from being saved to database
 */

echo "=== Driver Saving Issue Fix ===" . PHP_EOL;
echo "Date: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "Analyzing and fixing driver saving issues..." . PHP_EOL . PHP_EOL;

try {
    // Initialize Laravel
    require_once __DIR__ . '/bootstrap/app.php';
    $app = app();
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "1. Laravel bootstrap: OK" . PHP_EOL;
    
    // Check database connection
    $pdo = app('db')->connection()->getPdo();
    echo "2. Database connection: OK" . PHP_EOL;
    
    // Check table existence and structure
    $tableExists = app('db')->connection()->select("SHOW TABLES LIKE 'drivers'");
    
    if (empty($tableExists)) {
        echo "3. ✗ drivers table does not exist!" . PHP_EOL;
        echo "   Creating drivers table..." . PHP_EOL;
        
        // Create the table with proper structure
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS `drivers` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `driver_id` varchar(255) NOT NULL UNIQUE,
            `nickname` varchar(100) DEFAULT NULL,
            `first_name` varchar(255) NOT NULL,
            `middle_name` varchar(255) DEFAULT NULL,
            `surname` varchar(255) NOT NULL,
            `last_name` varchar(255) DEFAULT NULL,
            `phone` varchar(20) NOT NULL UNIQUE,
            `phone_2` varchar(20) DEFAULT NULL,
            `email` varchar(255) NOT NULL UNIQUE,
            `password` varchar(255) DEFAULT NULL,
            `date_of_birth` date DEFAULT NULL,
            `gender` enum('Male','Female','Other','male','female','other') DEFAULT NULL,
            `religion` varchar(100) DEFAULT NULL,
            `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
            `height_meters` decimal(3,2) DEFAULT NULL,
            `disability_status` varchar(255) DEFAULT NULL,
            `nationality_id` int(11) DEFAULT NULL,
            `state_of_origin` int(11) DEFAULT NULL,
            `lga_of_origin` int(11) DEFAULT NULL,
            `address_of_origin` text DEFAULT NULL,
            `profile_picture` varchar(500) DEFAULT NULL,
            `profile_photo` varchar(500) DEFAULT NULL,
            `nin_number` varchar(11) DEFAULT NULL,
            `nin_document` varchar(500) DEFAULT NULL,
            `license_number` varchar(50) DEFAULT NULL,
            `license_class` varchar(50) DEFAULT NULL,
            `license_expiry_date` date DEFAULT NULL,
            `frsc_document` varchar(500) DEFAULT NULL,
            `license_front_image` varchar(500) DEFAULT NULL,
            `license_back_image` varchar(500) DEFAULT NULL,
            `passport_photograph` varchar(500) DEFAULT NULL,
            `additional_documents` json DEFAULT NULL,
            `current_employer` varchar(255) DEFAULT NULL,
            `experience_years` int(11) DEFAULT NULL,
            `employment_start_date` date DEFAULT NULL,
            `residence_address` text DEFAULT NULL,
            `residence_state_id` int(11) DEFAULT NULL,
            `residence_lga_id` int(11) DEFAULT NULL,
            `vehicle_types` json DEFAULT NULL,
            `work_regions` json DEFAULT NULL,
            `special_skills` text DEFAULT NULL,
            `status` enum('active','inactive','suspended','blocked') DEFAULT 'active',
            `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
            `is_active` tinyint(1) DEFAULT 1,
            `registered_at` timestamp NULL DEFAULT NULL,
            `verification_notes` text DEFAULT NULL,
            `ocr_verification_status` enum('pending','passed','failed') DEFAULT 'pending',
            `ocr_verification_notes` text DEFAULT NULL,
            `nin_verification_data` json DEFAULT NULL,
            `nin_verified_at` timestamp NULL DEFAULT NULL,
            `nin_ocr_match_score` decimal(5,2) DEFAULT NULL,
            `frsc_verification_data` json DEFAULT NULL,
            `frsc_verified_at` timestamp NULL DEFAULT NULL,
            `frsc_ocr_match_score` decimal(5,2) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_driver_id` (`driver_id`),
            KEY `idx_email` (`email`),
            KEY `idx_phone` (`phone`),
            KEY `idx_status` (`status`),
            KEY `idx_verification_status` (`verification_status`),
            KEY `idx_ocr_status` (`ocr_verification_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        app('db')->connection()->unprepared($createTableSQL);
        echo "   ✓ drivers table created" . PHP_EOL;
    } else {
        echo "3. ✓ drivers table exists" . PHP_EOL;
    }
    
    // Test basic driver creation
    echo PHP_EOL . "4. Testing driver creation..." . PHP_EOL;
    
    // Check for required lookup tables
    $nationalityTable = app('db')->connection()->select("SHOW TABLES LIKE 'nationalities'");
    if (empty($nationalityTable)) {
        echo "   Creating nationalities table..." . PHP_EOL;
        app('db')->connection()->unprepared("
            CREATE TABLE IF NOT EXISTS `nationalities` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `code` varchar(3) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Insert default nationalities
        app('db')->connection()->unprepared("
            INSERT INTO `nationalities` (`id`, `name`, `code`) VALUES 
            (1, 'Nigerian', 'NG'),
            (2, 'American', 'US'),
            (3, 'British', 'GB'),
            (4, 'Canadian', 'CA'),
            (5, 'South African', 'ZA'),
            (6, 'Ghanaian', 'GH'),
            (7, 'Cameroonian', 'CM'),
            (8, 'Other', 'XX')
            ON DUPLICATE KEY UPDATE name=VALUES(name);
        ");
        echo "   ✓ Nationalities table created and populated" . PHP_EOL;
    }
    
    $statesTable = app('db')->connection()->select("SHOW TABLES LIKE 'states'");
    if (empty($statesTable)) {
        echo "   Creating states table..." . PHP_EOL;
        app('db')->connection()->unprepared("
            CREATE TABLE IF NOT EXISTS `states` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `code` varchar(3) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Insert some Nigerian states
        app('db')->connection()->unprepared("
            INSERT INTO `states` (`id`, `name`, `code`) VALUES 
            (1, 'Lagos', 'LG'),
            (2, 'Abuja FCT', 'FC'),
            (3, 'Kano', 'KN'),
            (4, 'Rivers', 'RV'),
            (5, 'Oyo', 'OY'),
            (6, 'Kaduna', 'KD'),
            (7, 'Ogun', 'OG'),
            (8, 'Cross River', 'CR')
            ON DUPLICATE KEY UPDATE name=VALUES(name);
        ");
        echo "   ✓ States table created and populated" . PHP_EOL;
    }
    
    // Test driver creation with minimal required data
    try {
        $testData = [
            'driver_id' => 'DRV' . time(),
            'first_name' => 'Test',
            'surname' => 'Driver',
            'email' => 'test' . time() . '@test.com',
            'phone' => '0801234' . substr(time(), -4),
            'nationality_id' => 1, // Nigerian
            'nin_number' => '12345678901',
            'license_number' => 'LIC' . time(),
            'license_class' => 'B',
            'license_expiry_date' => date('Y-m-d', strtotime('+2 years')),
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'status' => 'active',
            'verification_status' => 'pending',
            'is_active' => true,
            'registered_at' => now(),
        ];
        
        echo "   Creating test driver with minimal data..." . PHP_EOL;
        $driver = App\Models\Driver::create($testData);
        
        if ($driver && $driver->id) {
            echo "   ✓ Driver created successfully with ID: " . $driver->id . PHP_EOL;
            echo "   - Driver ID: " . $driver->driver_id . PHP_EOL;
            echo "   - Name: " . $driver->first_name . ' ' . $driver->surname . PHP_EOL;
            echo "   - Email: " . $driver->email . PHP_EOL;
            
            // Verify in database
            $savedDriver = App\Models\Driver::find($driver->id);
            if ($savedDriver) {
                echo "   ✓ Driver verified in database" . PHP_EOL;
                
                // Clean up
                $savedDriver->forceDelete();
                echo "   ✓ Test data cleaned up" . PHP_EOL;
            }
        }
        
    } catch (Exception $e) {
        echo "   ✗ Driver creation failed: " . $e->getMessage() . PHP_EOL;
        echo "   Stack trace:" . PHP_EOL;
        echo "   " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
        
        // Check for specific validation issues
        if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
            echo "   SQL Error detected. Checking table structure..." . PHP_EOL;
            
            $columns = app('db')->connection()->select("DESCRIBE drivers");
            echo "   Current table columns:" . PHP_EOL;
            foreach ($columns as $column) {
                echo "   - " . $column->Field . " (" . $column->Type . ")" . PHP_EOL;
            }
        }
    }
    
    // Check current driver count
    $count = App\Models\Driver::count();
    echo PHP_EOL . "5. Current drivers in database: $count" . PHP_EOL;
    
    // Provide diagnostic information
    echo PHP_EOL . "=== DIAGNOSTIC INFORMATION ===" . PHP_EOL;
    
    // Check model fillable fields
    $driver = new App\Models\Driver();
    $fillable = $driver->getFillable();
    echo "Fillable fields in Driver model: " . count($fillable) . PHP_EOL;
    
    $requiredFillable = ['first_name', 'surname', 'email', 'phone', 'driver_id'];
    $missingFillable = array_diff($requiredFillable, $fillable);
    if (empty($missingFillable)) {
        echo "✓ All required fields are fillable" . PHP_EOL;
    } else {
        echo "✗ Missing fillable fields: " . implode(', ', $missingFillable) . PHP_EOL;
    }
    
    // Check table permissions
    try {
        app('db')->connection()->select("SELECT * FROM drivers LIMIT 1");
        echo "✓ Table read permission: OK" . PHP_EOL;
    } catch (Exception $e) {
        echo "✗ Table read permission: FAILED - " . $e->getMessage() . PHP_EOL;
    }
    
    try {
        app('db')->connection()->insert("INSERT INTO drivers (driver_id, first_name, surname, email, phone) VALUES (?, ?, ?, ?, ?)", 
            ['TEST_PERM', 'Test', 'Permission', 'test_perm@test.com', '08000000000']);
        app('db')->connection()->delete("DELETE FROM drivers WHERE driver_id = 'TEST_PERM'");
        echo "✓ Table write permission: OK" . PHP_EOL;
    } catch (Exception $e) {
        echo "✗ Table write permission: FAILED - " . $e->getMessage() . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}

echo PHP_EOL . "=== RECOMMENDATIONS ===" . PHP_EOL;
echo "1. Ensure the drivers table exists with proper structure" . PHP_EOL;
echo "2. Verify all required lookup tables (nationalities, states) exist" . PHP_EOL;
echo "3. Check that validation rules match database constraints" . PHP_EOL;
echo "4. Ensure all required fields are included in form submission" . PHP_EOL;
echo "5. Check file upload directory permissions if files are being uploaded" . PHP_EOL;