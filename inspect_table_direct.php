<?php
// Direct inspection without Laravel bootstrap
echo "Direct Database Table Inspection\n";
echo "================================\n\n";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=drivelink_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Get table structure
    echo "ADMIN_USERS TABLE STRUCTURE:\n";
    echo "----------------------------\n";
    $result = $pdo->query("DESCRIBE admin_users");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo sprintf("%-20s | %-15s | %-5s | %-10s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Default'] ?? 'NULL'
        );
    }
    
    echo "\nColumn names only: ";
    $columnNames = array_column($columns, 'Field');
    echo implode(', ', $columnNames) . "\n\n";
    
    // Check existing data
    echo "EXISTING DATA:\n";
    echo "-------------\n";
    $data = $pdo->query("SELECT * FROM admin_users LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    echo "Records found: " . count($data) . "\n";
    
    if (count($data) > 0) {
        foreach ($data as $row) {
            echo "Row ID: " . $row['id'] . "\n";
            foreach ($row as $key => $value) {
                echo "  $key: " . (strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value) . "\n";
            }
            echo "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>