<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Test DriverPerformance model
    $count = \App\Models\DriverPerformance::count();
    echo "DriverPerformance table accessible. Record count: " . $count . "\n";
    
    // Test DriverEmploymentHistory model
    $count = \App\Models\DriverEmploymentHistory::count();
    echo "DriverEmploymentHistory table accessible. Record count: " . $count . "\n";
    
    // Test DriverNextOfKin model
    $count = \App\Models\DriverNextOfKin::count();
    echo "DriverNextOfKin table accessible. Record count: " . $count . "\n";
    
    echo "All driver-related tables are accessible!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}