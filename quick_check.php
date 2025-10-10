<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting debug...\n";
flush();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=drivelink", 'root', '');
    echo "Connected to database\n";
    
    $stmt = $pdo->query("DESCRIBE drivers");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($result) . " columns:\n";
    foreach ($result as $column) {
        echo "- " . $column['Field'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>