<?php
echo "Testing Driver System Updates\n";
echo "=============================\n\n";

// Test 1: Check if files exist
echo "1. Checking updated files...\n";

$files = [
    'app/Models/Driver.php',
    'app/Http/Controllers/Admin/DriverController.php', 
    'resources/views/admin/drivers/create.blade.php',
    'resources/views/admin/drivers/edit.blade.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} exists\n";
    } else {
        echo "❌ {$file} missing\n";
    }
}

echo "\n2. Testing Driver Model instantiation...\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    $driver = new App\Models\Driver();
    echo "✅ Driver model created\n";
    echo "Table: " . $driver->getTable() . "\n";
    echo "Fillable count: " . count($driver->getFillable()) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ All tests completed!\n";
?>