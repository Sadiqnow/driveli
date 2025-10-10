<?php
// Check what fields exist in drivers for KYC Step 2
try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== KYC Step 2 Fields Analysis ===\n\n";
    
    // Get all columns from drivers table
    $stmt = $pdo->query("SHOW COLUMNS FROM drivers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    // Fields that KYC Step 2 controller tries to update
    $step2Fields = [
        'years_of_experience',
        'previous_company', 
        'license_expiry_date',
        'has_vehicle',
        'vehicle_type',
        'vehicle_year',
        'bank_id',
        'account_number',
        'account_name',
        'bvn',
        'preferred_work_location',
        'available_for_night_shifts',
        'available_for_weekend_work'
    ];
    
    echo "Checking KYC Step 2 fields in database:\n";
    $missingFields = [];
    foreach ($step2Fields as $field) {
        if (in_array($field, $columnNames)) {
            echo "✓ $field - EXISTS\n";
        } else {
            echo "✗ $field - MISSING\n";
            $missingFields[] = $field;
        }
    }
    
    // Check for alternative field names that might exist
    echo "\n=== Checking Alternative Field Names ===\n";
    $alternativeChecks = [
        'experience_years' => 'years_of_experience',
        'license_expiry_date' => 'license_expiry_date',
        'bvn_number' => 'bvn',
        'experience_level' => 'years_of_experience'
    ];
    
    foreach ($alternativeChecks as $actualField => $expectedField) {
        if (in_array($actualField, $columnNames)) {
            echo "→ Found alternative: '$actualField' (expected: '$expectedField')\n";
        }
    }
    
    // Show all fields that contain relevant keywords
    echo "\n=== Related Fields Found ===\n";
    $keywords = ['experience', 'license', 'vehicle', 'bank', 'account', 'bvn', 'work', 'shift'];
    foreach ($keywords as $keyword) {
        $matches = array_filter($columnNames, function($col) use ($keyword) {
            return stripos($col, $keyword) !== false;
        });
        if (!empty($matches)) {
            echo "$keyword: " . implode(', ', $matches) . "\n";
        }
    }
    
    echo "\n=== Recommendations ===\n";
    if (!empty($missingFields)) {
        echo "Missing fields need to be added:\n";
        foreach ($missingFields as $field) {
            echo "- $field\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>