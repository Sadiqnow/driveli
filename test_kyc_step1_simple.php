<?php
// Simple test to reproduce KYC Step 1 error
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Initialize Laravel environment
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DriverNormalized;
use Illuminate\Support\Facades\DB;

try {
    echo "Testing KYC Step 1 Update...\n\n";
    
    // Test 1: Check if table exists
    echo "1. Checking if drivers table exists...\n";
    $tableExists = DB::select("SHOW TABLES LIKE 'drivers'");
    if (empty($tableExists)) {
        echo "ERROR: drivers table does not exist!\n";
        exit(1);
    }
    echo "✓ Table exists\n\n";
    
    // Test 2: Check specific columns
    echo "2. Checking specific columns...\n";
    $columns = DB::select("SHOW COLUMNS FROM drivers");
    $columnNames = array_column($columns, 'Field');
    
    $requiredColumns = ['kyc_status', 'kyc_step', 'marital_status'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columnNames)) {
            echo "✓ Column '$col' exists\n";
        } else {
            echo "✗ Column '$col' MISSING!\n";
        }
    }
    
    // Test 3: Try the exact update from KYC controller
    echo "\n3. Testing the actual update query...\n";
    
    // Find a driver to test with (or create a test one)
    $driver = DriverNormalized::first();
    if (!$driver) {
        echo "No drivers found to test with.\n";
        exit(0);
    }
    
    echo "Found driver: " . $driver->first_name . " " . $driver->surname . " (ID: " . $driver->id . ")\n";
    
    // Try the exact update from the KYC controller
    $updateData = [
        'middle_name' => 'TestMiddle',
        'gender' => 'Male',
        'marital_status' => 'Single',
        'nationality_id' => 1,
        'state_id' => 1,
        'lga_id' => 1,
        'residential_address' => 'Test Address 123',
        'emergency_contact_name' => 'Test Contact',
        'emergency_contact_phone' => '1234567890',
        'emergency_contact_relationship' => 'Friend',
        'kyc_step' => 'step_2',
        'kyc_status' => 'in_progress',
    ];
    
    echo "Attempting update...\n";
    $result = $driver->update($updateData);
    
    if ($result) {
        echo "✓ Update successful!\n";
    } else {
        echo "✗ Update failed!\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
    
    // Check if it's a column error
    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        echo "\nThis appears to be a missing column error.\n";
        echo "Let's check what columns actually exist:\n\n";
        
        try {
            $columns = DB::select("SHOW COLUMNS FROM drivers");
            foreach ($columns as $col) {
                echo "- " . $col->Field . " (" . $col->Type . ")\n";
            }
        } catch (Exception $e2) {
            echo "Could not retrieve column list: " . $e2->getMessage() . "\n";
        }
    }
}