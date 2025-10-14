<?php

// Test script to check KYC columns and functionality
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Drivers;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    echo "=== Testing KYC Columns ===\n";
    
    // Check if columns exist in database
    $columns = DB::select("DESCRIBE drivers");
    $existingColumns = array_column($columns, 'Field');
    
    $requiredColumns = [
        'kyc_status', 'kyc_step', 'marital_status', 'state_id', 'lga_id',
        'residential_address', 'emergency_contact_name', 'emergency_contact_phone',
        'emergency_contact_relationship', 'middle_name', 'gender', 'nationality_id'
    ];
    
    echo "Checking required columns:\n";
    foreach ($requiredColumns as $column) {
        if (in_array($column, $existingColumns)) {
            echo "✓ $column exists\n";
        } else {
            echo "✗ $column MISSING\n";
        }
    }
    
    // Try to create a test update array
    $testData = [
        'middle_name' => 'Test',
        'gender' => 'Male',
        'marital_status' => 'Single',
        'nationality_id' => 1,
        'state_id' => 1,
        'lga_id' => 1,
        'residential_address' => 'Test Address',
        'emergency_contact_name' => 'Test Contact',
        'emergency_contact_phone' => '1234567890',
        'emergency_contact_relationship' => 'Friend',
        'kyc_step' => 'step_2',
        'kyc_status' => 'in_progress'
    ];
    
    echo "\nTesting fillable fields:\n";
    $driver = new Drivers();
    $fillableFields = $driver->getFillable();
    
    foreach ($testData as $field => $value) {
        if (in_array($field, $fillableFields)) {
            echo "✓ $field is fillable\n";
        } else {
            echo "✗ $field is NOT fillable\n";
        }
    }
    
    echo "\n=== Test Completed ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}