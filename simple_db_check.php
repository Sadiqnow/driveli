<?php
echo "Testing database connection...\n";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=drivelink_db", "root", "");
    echo "✓ Database connection successful\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nTables found:\n";
    foreach ($tables as $table) {
        echo "- " . $table . "\n";
    }
    
    // Check specifically for drivers table
    $driversCheck = $pdo->query("SHOW TABLES LIKE 'drivers'");
    $driversExists = $driversCheck->fetch();
    
    if ($driversExists) {
        echo "\n✗ DRIVERS TABLE STILL EXISTS\n";
    } else {
        echo "\n✓ DRIVERS TABLE NOT FOUND - Successfully removed\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}
?>