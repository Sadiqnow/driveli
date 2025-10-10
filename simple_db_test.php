<?php

echo "=== Simple Database Connection Test ===\n";

$host = '127.0.0.1';
$username = 'root';
$password = '';

try {
    // Test basic MySQL connection
    echo "1. Testing basic MySQL connection...\n";
    $pdo = new PDO("mysql:host={$host}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ MySQL connection successful\n\n";
    
    // Show databases
    echo "2. Listing databases...\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($databases as $db) {
        echo "   - {$db}\n";
    }
    echo "\n";
    
    // Check if drivelink_db exists
    echo "3. Checking drivelink_db...\n";
    if (in_array('drivelink_db', $databases)) {
        echo "✓ drivelink_db database exists\n";
        
        // Connect to drivelink_db
        $pdo = new PDO("mysql:host={$host};dbname=drivelink_db", $username, $password);
        echo "✓ Connected to drivelink_db\n";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✓ Found " . count($tables) . " tables\n";
        
        if (in_array('drivers', $tables)) {
            echo "✓ drivers table exists!\n";
        } else {
            echo "❌ drivers table NOT found\n";
            echo "Available tables: " . implode(', ', array_slice($tables, 0, 10)) . "\n";
        }
        
         if (in_array('admin_users', $tables)) {
            echo "✓ admin_users table exists!\n";
        } else {
            echo "❌ admin_users table NOT found\n";
            echo "Available tables: " . implode(', ', array_slice($tables, 0, 10)) . "\n";
        }

    } else {
        echo "❌ drivelink_db database NOT found\n";
        echo "Available databases: " . implode(', ', $databases) . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "This usually means:\n";
    echo "- MySQL/XAMPP is not running\n";
    echo "- Wrong database credentials\n";
    echo "- Database doesn't exist\n";
}

echo "\nTest completed.\n";