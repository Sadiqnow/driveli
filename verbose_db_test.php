<?php

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "=== Verbose Database Test ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current directory: " . getcwd() . "\n\n";

try {
    echo "Step 1: Testing MySQL connection...\n";
    $pdo = new PDO("mysql:host=127.0.0.1", "root", "");
    echo "✓ MySQL connected\n";
    
    echo "Step 2: Checking databases...\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases found: " . implode(', ', $dbs) . "\n";
    
    if (in_array('drivelink_db', $dbs)) {
        echo "✓ drivelink_db exists\n";
        
        echo "Step 3: Connecting to drivelink_db...\n";
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=drivelink_db", "root", "");
        echo "✓ Connected to drivelink_db\n";
        
        echo "Step 4: Checking tables...\n";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables: " . implode(', ', $tables) . "\n";
        
        if (in_array('drivers', $tables)) {
            echo "✅ drivers table exists!\n";
            
            // Test count
            $stmt = $pdo->query("SELECT COUNT(*) FROM drivers");
            $count = $stmt->fetchColumn();
            echo "Records in drivers: {$count}\n";
        } else {
            echo "❌ drivers table missing\n";
            echo "Will create it now...\n";
            
            // Create the table directly
            $sql = "CREATE TABLE IF NOT EXISTS drivers (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                driver_id VARCHAR(255) NOT NULL UNIQUE,
                first_name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NOT NULL UNIQUE,
                email VARCHAR(255) NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                status ENUM('active', 'inactive') DEFAULT 'active',
                verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                deleted_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $pdo->exec($sql);
            echo "✓ Created basic drivers table\n";
        }
    } else {
        echo "❌ drivelink_db does not exist\n";
        echo "Creating drivelink_db...\n";
        $pdo->exec("CREATE DATABASE drivelink_db");
        echo "✓ Created drivelink_db\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\nTest completed.\n";