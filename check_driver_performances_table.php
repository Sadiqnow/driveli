<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Checking if driver_performances table exists...\n";

    $tables = DB::select("SHOW TABLES LIKE 'driver_performances'");
    if (count($tables) > 0) {
        echo "✅ driver_performances table EXISTS\n";

        // Show table structure
        $columns = DB::select("SHOW COLUMNS FROM driver_performances");
        echo "Columns:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field}: {$column->Type}\n";
        }
    } else {
        echo "❌ driver_performances table DOES NOT EXIST\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
