<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!\n";
    
    // Check if drivers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    if ($stmt->rowCount() > 0) {
        echo "Drivers table exists\n";
        
        // Show current columns
        echo "\nCurrent columns in drivers table:\n";
        $stmt = $pdo->query("SHOW COLUMNS FROM drivers");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "Drivers table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}