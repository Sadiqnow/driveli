<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
    echo "Database connection successful!\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "- " . $table . "\n";
    }
    
    // Check specifically for role_user table
    if (in_array('role_user', $tables)) {
        echo "\n✓ role_user table exists\n";
    } else {
        echo "\n✗ role_user table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
?>