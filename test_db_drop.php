<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!\n";
    
    // Check if drivers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    if ($stmt->rowCount() > 0) {
        echo "Drivers table exists. Dropping it...\n";
        $pdo->exec("DROP TABLE drivers");
        echo "Drivers table dropped successfully!\n";
    } else {
        echo "Drivers table does not exist.\n";
    }
    
    // Show all remaining tables
    echo "\nRemaining tables:\n";
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch()) {
        echo "- " . $row[0] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>