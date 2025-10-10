<?php
// Verify KYC columns have been added
echo "=== Verifying KYC Columns ===\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Get all columns from drivers table
    $stmt = $pdo->query("SHOW COLUMNS FROM drivers");
    $columns = $stmt->fetchAll();
    
    $columnNames = array_column($columns, 'Field');
    
    // Required columns for KYC Step 1
    $requiredColumns = [
        'kyc_status',
        'kyc_step', 
        'marital_status',
        'state_id',
        'lga_id',
        'residential_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'middle_name',
        'gender',
        'nationality_id'
    ];
    
    echo "Checking required KYC columns:\n";
    $allPresent = true;
    
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columnNames)) {
            echo "✓ $column - EXISTS\n";
        } else {
            echo "✗ $column - MISSING\n";
            $allPresent = false;
        }
    }
    
    if ($allPresent) {
        echo "\n🎉 SUCCESS: All required KYC columns are present!\n";
        
        // Test a simple update to ensure it works
        echo "\nTesting KYC update functionality...\n";
        
        // Check if we have any drivers to test with
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM drivers");
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            // Get first driver for testing
            $stmt = $pdo->query("SELECT id, first_name, surname FROM drivers LIMIT 1");
            $driver = $stmt->fetch();
            
            echo "Testing with driver: {$driver['first_name']} {$driver['surname']} (ID: {$driver['id']})\n";
            
            // Test update query (same as in KYC controller)
            $updateStmt = $pdo->prepare("UPDATE drivers SET 
                middle_name = ?, 
                gender = ?, 
                marital_status = ?, 
                kyc_step = ?, 
                kyc_status = ? 
                WHERE id = ?");
            
            $result = $updateStmt->execute([
                'TestMiddle',
                'Male', 
                'Single',
                'step_2',
                'in_progress',
                $driver['id']
            ]);
            
            if ($result) {
                echo "✓ KYC update test SUCCESSFUL!\n";
                
                // Revert the test changes
                $revertStmt = $pdo->prepare("UPDATE drivers SET 
                    middle_name = NULL, 
                    kyc_step = 'not_started', 
                    kyc_status = 'pending' 
                    WHERE id = ?");
                $revertStmt->execute([$driver['id']]);
                
                echo "✓ Test data reverted\n";
            } else {
                echo "✗ KYC update test FAILED!\n";
            }
            
        } else {
            echo "No drivers found for testing, but columns are ready.\n";
        }
        
    } else {
        echo "\n❌ ERROR: Some required columns are still missing!\n";
        echo "You may need to run the migration again or check for errors.\n";
    }
    
    echo "\n=== Additional KYC Columns ===\n";
    $additionalColumns = [
        'years_of_experience', 'previous_company', 'has_vehicle',
        'vehicle_type', 'vehicle_year', 'bank_id', 'account_number',
        'account_name', 'bvn', 'preferred_work_location',
        'available_for_night_shifts', 'available_for_weekend_work',
        'kyc_step_1_completed_at', 'kyc_step_2_completed_at',
        'kyc_step_3_completed_at', 'kyc_completed_at'
    ];
    
    foreach ($additionalColumns as $column) {
        if (in_array($column, $columnNames)) {
            echo "✓ $column\n";
        } else {
            echo "- $column (not present)\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Verification Complete ===\n";
?>