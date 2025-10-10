<?php

echo "=== DRIVELINK TECHNICAL DIAGNOSIS ===\n\n";

try {
    // Test Laravel bootstrap
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel Bootstrap: SUCCESS\n";

    // Test database connection
    $pdo = DB::connection()->getPdo();
    echo "✅ Database Connection: SUCCESS\n";

    // Check critical tables
    $tables = ['drivers', 'admin_users', 'companies'];
    foreach ($tables as $table) {
        $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
        echo ($exists ? "✅" : "❌") . " Table {$table}: " . ($exists ? "EXISTS" : "MISSING") . "\n";
    }

    // Test model loading
    $driver = new App\Models\DriverNormalized();
    echo "✅ Drivers Model: LOADS\n";
    echo "   Table: " . $driver->getTable() . "\n";

    // Count records
    $driversCount = DB::table('drivers')->count();
    echo "📊 Total Drivers: {$driversCount}\n";

    // Test basic query performance
    $start = microtime(true);
    $activeDrivers = DB::table('drivers')->where('is_active', true)->limit(5)->get();
    $queryTime = (microtime(true) - $start) * 1000;
    echo "⏱️  Query Time: " . number_format($queryTime, 2) . "ms\n";

    // Check authentication configuration
    $guards = config('auth.guards');
    echo "🔐 Auth Guards: " . count($guards) . " configured\n";

    echo "\n=== BASIC SYSTEM STATUS: OPERATIONAL ===\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}