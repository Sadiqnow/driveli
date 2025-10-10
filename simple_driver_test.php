<?php
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "Testing Driver Model...\n";
    
    // Test model instantiation
    $driver = new App\Models\Driver();
    echo "Driver model created successfully\n";
    
    // Test table name
    echo "Table name: " . $driver->getTable() . "\n";
    
    // Test fillable fields
    echo "Fillable fields: " . implode(', ', $driver->getFillable()) . "\n";
    
    // Test some accessors
    $driver->first_name = 'John';
    $driver->middle_name = 'Michael';
    $driver->surname = 'Doe';
    echo "Full name: " . $driver->full_name . "\n";
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
?>