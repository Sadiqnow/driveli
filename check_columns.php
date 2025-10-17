<?php
require 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('DESCRIBE drivers');
    echo "Drivers table columns:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }

    // Check if kyc_step exists
    $stmt = $pdo->query("SHOW COLUMNS FROM drivers LIKE 'kyc_step'");
    if ($stmt->rowCount() > 0) {
        echo "\n✅ kyc_step column exists\n";
    } else {
        echo "\n❌ kyc_step column does NOT exist\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
