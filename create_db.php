<?php
try {
    // First connect without specifying database to create it
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    echo "MySQL connection successful!\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS drivelink_db");
    echo "Database drivelink_db created or already exists.\n";
    
    // Now connect to the specific database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
    echo "Connected to drivelink_db successfully!\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "- " . $table . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>