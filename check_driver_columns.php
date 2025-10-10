<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Checking drivers table structure...\n\n";
    
    // Get table columns
    $columns = DB::select("SHOW COLUMNS FROM drivers");
    
    echo "Columns in drivers table:\n";
    echo str_repeat("=", 50) . "\n";
    
    foreach ($columns as $column) {
        $null = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $column->Default ? "DEFAULT '{$column->Default}'" : '';
        echo sprintf("%-25s %-15s %-10s %s\n", 
            $column->Field, 
            $column->Type, 
            $null,
            $default
        );
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Total columns: " . count($columns) . "\n\n";
    
    // Check specific columns we need
    $requiredColumns = [
        'driver_id', 'first_name', 'surname', 'email', 'phone', 
        'license_number', 'password', 'status', 'verification_status',
        'kyc_status', 'date_of_birth', 'gender', 'created_by'
    ];
    
    echo "Checking required columns for simple driver creation:\n";
    echo str_repeat("-", 50) . "\n";
    
    $columnNames = array_column($columns, 'Field');
    
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columnNames) ? '✅ EXISTS' : '❌ MISSING';
        echo sprintf("%-25s %s\n", $col, $exists);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}