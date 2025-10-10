<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Get table schema
    $columns = DB::select('DESCRIBE drivers');
    
    echo "Columns in 'drivers' table:\n";
    echo "==========================\n";
    
    foreach ($columns as $column) {
        echo sprintf("%-30s %-20s %-10s %-10s\n", 
            $column->Field, 
            $column->Type, 
            $column->Null, 
            $column->Default ?? 'NULL'
        );
    }
    
    echo "\nFillable columns in DriverNormalized model:\n";
    echo "==========================================\n";
    
    $driver = new App\Models\DriverNormalized();
    $fillable = $driver->getFillable();
    
    foreach ($fillable as $field) {
        echo "- $field\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}