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

    echo "Current columns in drivers table:\n";
    foreach($columns as $col) {
        echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Default']}\n";
    }

    // Required columns from the standard schema
    $requiredColumns = [
        'nationality_id' => 'BIGINT UNSIGNED NULL DEFAULT 1',
        'license_class' => 'VARCHAR(255) NULL',
        'license_expiry_date' => 'DATE NULL',
        'profile_picture' => 'VARCHAR(255) NULL',
        'disability_status' => 'VARCHAR(255) NULL',
        'residence_state_id' => 'INT NULL',
        'residence_lga_id' => 'INT NULL',
        'vehicle_types' => 'JSON NULL',
        'work_regions' => 'JSON NULL',
        'special_skills' => 'TEXT NULL',
        'verification_status' => "ENUM('pending', 'verified', 'rejected', 'reviewing') DEFAULT 'pending'",
        'ocr_verification_status' => "ENUM('pending', 'passed', 'failed') DEFAULT 'pending'",
        'ocr_verification_notes' => 'TEXT NULL',
        'passport_photograph' => 'VARCHAR(255) NULL',
        'license_front_image' => 'VARCHAR(255) NULL',
        'license_back_image' => 'VARCHAR(255) NULL',
        'rejected_at' => 'TIMESTAMP NULL',
        'rejection_reason' => 'VARCHAR(255) NULL',
        'is_active' => 'BOOLEAN DEFAULT TRUE',
        'last_active_at' => 'TIMESTAMP NULL',
        'registered_at' => 'TIMESTAMP NULL',
    ];

    $existingColumns = array_column($columns, 'Field');

    $missingColumns = [];
    foreach($requiredColumns as $col => $type) {
        if (!in_array($col, $existingColumns)) {
            $missingColumns[$col] = $type;
        }
    }

    if (count($missingColumns) > 0) {
        echo "\nMissing columns:\n";
        foreach($missingColumns as $col => $type) {
            echo "- $col: $type\n";
        }

        echo "\nAdding missing columns...\n";
        foreach($missingColumns as $col => $type) {
            $sql = "ALTER TABLE drivers ADD COLUMN $col $type";
            $pdo->exec($sql);
            echo "Added $col\n";
        }
        echo "All missing columns added.\n";
    } else {
        echo "\nAll required columns are present.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
