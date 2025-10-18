<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if location_type column exists in driver_locations
    $stmt = $pdo->prepare("DESCRIBE driver_locations");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $locationTypeExists = false;
    foreach($columns as $col) {
        if ($col['Field'] == 'location_type') {
            $locationTypeExists = true;
            echo "location_type column exists in driver_locations: Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
        }
    }

    if (!$locationTypeExists) {
        echo "location_type column does NOT exist in driver_locations table.\n";
        echo "Adding the column...\n";

        $pdo->exec("ALTER TABLE driver_locations ADD COLUMN location_type ENUM('current', 'home', 'work', 'pickup', 'dropoff') DEFAULT 'current'");
        echo "Added location_type column to driver_locations.\n";
    } else {
        echo "location_type column already exists in driver_locations.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
