<?php

require_once 'vendor/autoload.php';

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = [
    'driver_matches',
    'company_requests', 
    'commissions',
    'admin_users',
    'companies',
    'drivers'
];

echo "Table verification results:\n";
echo str_repeat("=", 40) . "\n";

foreach ($tables as $table) {
    try {
        $exists = Schema::hasTable($table);
        echo sprintf("%-20s: %s\n", $table, $exists ? '✓ EXISTS' : '✗ MISSING');
    } catch (Exception $e) {
        echo sprintf("%-20s: ERROR - %s\n", $table, $e->getMessage());
    }
}

echo "\nTesting DriverMatch model...\n";
try {
    $count = App\Models\DriverMatch::count();
    echo "DriverMatch table accessible: ✓ (Record count: $count)\n";
} catch (Exception $e) {
    echo "DriverMatch table error: ✗ " . $e->getMessage() . "\n";
}