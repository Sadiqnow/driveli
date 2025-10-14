<?php

echo "Testing Driver Performance Fix\n";
echo "=============================\n\n";

try {
    // Test database connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=drivelink_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n\n";
    
    // Check if driver_performance table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'driver_performance'")->fetchAll();
    if (empty($tables)) {
        echo "❌ driver_performance table doesn't exist\n";
        echo "Creating table...\n";
        
        $createTable = "
            CREATE TABLE driver_performance (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                driver_id VARCHAR(50) NOT NULL,
                current_location_lat DECIMAL(10,8) NULL,
                current_location_lng DECIMAL(11,8) NULL,
                current_city VARCHAR(255) NULL,
                total_jobs_completed INT UNSIGNED DEFAULT 0,
                average_rating DECIMAL(3,2) DEFAULT 0.00,
                total_ratings INT UNSIGNED DEFAULT 0,
                total_earnings DECIMAL(15,2) DEFAULT 0.00,
                completion_rate DECIMAL(5,2) DEFAULT 0.00,
                last_job_completed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $pdo->exec($createTable);
        echo "✅ Created driver_performance table\n\n";
    } else {
        echo "✅ driver_performance table exists\n\n";
    }
    
    // Check table structure
    $columns = $pdo->query("DESCRIBE driver_performance")->fetchAll(PDO::FETCH_ASSOC);
    echo "Current columns:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    echo "\n";
    
    // Check if completion_rate exists
    $columnNames = array_column($columns, 'Field');
    $hasCompletionRate = in_array('completion_rate', $columnNames);
    
    if (!$hasCompletionRate) {
        echo "❌ completion_rate column missing. Adding it...\n";
        $pdo->exec("ALTER TABLE driver_performance ADD COLUMN completion_rate DECIMAL(5,2) DEFAULT 0.00");
        echo "✅ Added completion_rate column\n\n";
    } else {
        echo "✅ completion_rate column exists\n\n";
    }
    
    // Test the problematic query from Drivers model
    echo "Testing the exact query from Drivers model...\n";
    $testQuery = "SELECT id, driver_id, total_jobs_completed, average_rating, total_earnings, completion_rate FROM driver_performance LIMIT 1";
    $result = $pdo->query($testQuery);
    echo "✅ Query executed successfully!\n\n";
    
    echo "🎉 DRIVER PERFORMANCE TABLE IS NOW FIXED!\n";
    echo "The DriverNormalized->performance() relationship should work without errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    
    // Provide manual SQL if needed
    echo "Manual SQL to run in phpMyAdmin:\n\n";
    echo "-- Add completion_rate column\n";
    echo "ALTER TABLE driver_performance ADD COLUMN completion_rate DECIMAL(5,2) DEFAULT 0.00;\n\n";
    echo "-- Test the query\n";
    echo "SELECT id, driver_id, total_jobs_completed, average_rating, total_earnings, completion_rate FROM driver_performance LIMIT 1;\n";
}