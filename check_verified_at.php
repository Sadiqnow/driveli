<?php

require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking drivers table columns:\n";
    $stmt = $pdo->query('DESCRIBE drivers');
    $columns = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
        echo "- " . $row['Field'] . "\n";
    }

    echo "\nChecking if 'verified_at' column exists: ";
    if (in_array('verified_at', $columns)) {
        echo "YES\n";
    } else {
        echo "NO\n";
    }

    echo "\nChecking if 'verification_status' column exists: ";
    if (in_array('verification_status', $columns)) {
        echo "YES\n";
    } else {
        echo "NO\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
