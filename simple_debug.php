<?php

// Simple debug without Laravel bootstrap
echo "=== Simple Debug ===\n\n";

// Check .env file
if (file_exists('.env')) {
    echo "✅ .env file exists\n";
    
    $env = file_get_contents('.env');
    preg_match('/DB_DATABASE=(.*)/', $env, $matches);
    $dbName = trim($matches[1] ?? 'Not found');
    echo "Database name from .env: $dbName\n";
    
    preg_match('/DB_HOST=(.*)/', $env, $matches);
    $dbHost = trim($matches[1] ?? 'Not found');
    echo "Database host from .env: $dbHost\n";
    
} else {
    echo "❌ .env file not found\n";
}

// Try direct MySQL connection
try {
    $host = 'localhost';
    $dbname = 'drivelink';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ Direct MySQL connection successful\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    if ($stmt->rowCount() > 0) {
        echo "✅ drivers table exists\n";
        
        // Get columns
        $stmt = $pdo->query("DESCRIBE drivers");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Table has " . count($columns) . " columns\n";
        
        // Check specific step 2 fields
        $step2Fields = [
            'residential_address',
            'residence_state_id', 
            'residence_lga_id',
            'city',
            'postal_code',
            'license_class',
            'license_issue_date',
            'license_expiry_date', 
            'years_of_experience',
            'previous_company',
            'bank_id',
            'account_number',
            'account_name',
            'bvn'
        ];
        
        echo "\n=== Step 2 Field Check ===\n";
        $missing = [];
        foreach ($step2Fields as $field) {
            if (in_array($field, $columns)) {
                echo "✅ $field\n";
            } else {
                echo "❌ $field - MISSING\n";
                $missing[] = $field;
            }
        }
        
        if (count($missing) > 0) {
            echo "\nMISSING FIELDS: " . implode(', ', $missing) . "\n";
        }
        
    } else {
        echo "❌ drivers table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";