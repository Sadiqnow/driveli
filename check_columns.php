<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Current drivers table columns:\n";
    echo "================================\n";

    $stmt = $pdo->query('SHOW COLUMNS FROM drivers');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
    }

    echo "\nChecking for nin_number column:\n";
    echo "===============================\n";

    $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'nin_number'");
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Found nin_number column: " . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
    } else {
        echo "nin_number column NOT found in drivers table\n";
    }

    echo "\nChecking for national_id column:\n";
    echo "================================\n";

    $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'national_id'");
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Found national_id column: " . $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
    } else {
        echo "national_id column NOT found in drivers table\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
