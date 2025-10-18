<?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("DESCRIBE drivers");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $licenseClassExists = false;
    foreach($columns as $col) {
        if ($col['Field'] == 'license_class') {
            $licenseClassExists = true;
            echo "license_class column exists: Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
        }
    }

    if (!$licenseClassExists) {
        echo "license_class column does NOT exist in drivers table.\n";
        echo "Adding the column...\n";

        $pdo->exec("ALTER TABLE drivers ADD COLUMN license_class VARCHAR(255) NULL");
        echo "Added license_class column.\n";
    } else {
        echo "license_class column already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
