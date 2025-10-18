<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Testing NIN number linking functionality...\n";
    echo "==========================================\n";

    // Test 1: Check if columns exist
    echo "Test 1: Checking column existence\n";
    $columns = [
        'drivers.nin_number',
        'driver_documents.nin_verification_data',
        'driver_documents.nin_verified_at',
        'driver_documents.nin_ocr_match_score'
    ];

    foreach ($columns as $column) {
        list($table, $col) = explode('.', $column);
        $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE '$col'");
        if ($stmt->rowCount() > 0) {
            echo "✓ $column exists\n";
        } else {
            echo "✗ $column missing\n";
        }
    }

    // Test 2: Check if trigger exists
    echo "\nTest 2: Checking trigger existence\n";
    $stmt = $pdo->query("SHOW TRIGGERS LIKE 'sync_driver_nin_data'");
    if ($stmt->rowCount() > 0) {
        echo "✓ sync_driver_nin_data trigger exists\n";
    } else {
        echo "✗ sync_driver_nin_data trigger missing\n";
    }

    // Test 3: Test data insertion and synchronization
    echo "\nTest 3: Testing data synchronization\n";

    // Get a test driver ID
    $stmt = $pdo->query("SELECT id FROM drivers LIMIT 1");
    if ($stmt->rowCount() > 0) {
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        $driverId = $driver['id'];

        echo "Using test driver ID: $driverId\n";

        // Update nin_number
        $testNin = '12345678901';
        $pdo->prepare("UPDATE drivers SET nin_number = ? WHERE id = ?")
             ->execute([$testNin, $driverId]);

        echo "✓ Updated drivers.nin_number to $testNin\n";

        // Check if driver_documents was updated via trigger
        $stmt = $pdo->prepare("SELECT nin_verification_data FROM driver_documents WHERE driver_id = ?");
        $stmt->execute([$driverId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($doc && $doc['nin_verification_data']) {
            $ninData = json_decode($doc['nin_verification_data'], true);
            if (isset($ninData['nin_number']) && $ninData['nin_number'] == $testNin) {
                echo "✓ Trigger synchronization working: driver_documents updated\n";
            } else {
                echo "✗ Trigger synchronization failed: driver_documents not updated correctly\n";
            }
        } else {
            echo "! No driver_documents record found for this driver\n";
        }

        // Clean up test data
        $pdo->prepare("UPDATE drivers SET nin_number = NULL WHERE id = ?")
             ->execute([$driverId]);
        echo "✓ Cleaned up test data\n";

    } else {
        echo "! No drivers found for testing\n";
    }

    // Test 4: Check for any existing data inconsistencies
    echo "\nTest 4: Checking data consistency\n";
    $stmt = $pdo->query("
        SELECT COUNT(*) as inconsistencies
        FROM drivers d
        LEFT JOIN driver_documents dd ON d.id = dd.driver_id
        WHERE d.nin_number IS NOT NULL
        AND dd.driver_id IS NOT NULL
        AND JSON_EXTRACT(dd.nin_verification_data, '$.nin_number') != d.nin_number
    ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $inconsistencies = $row['inconsistencies'];

    if ($inconsistencies == 0) {
        echo "✓ No data inconsistencies found\n";
    } else {
        echo "⚠ Found $inconsistencies data inconsistencies\n";
    }

    echo "\nAll tests completed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
