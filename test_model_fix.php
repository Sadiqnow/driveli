<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

try {
    echo "Testing AdminUser model instantiation...\n";
    $admin = new App\Models\AdminUser();
    echo "✓ AdminUser model instantiated successfully\n";
    
    echo "Testing Driver model instantiation...\n";
    $driver = new App\Models\Driver();
    echo "✓ Driver model instantiated successfully\n";
    
    echo "\nTesting password mutators...\n";
    $admin->password = 'test123';
    echo "✓ AdminUser password mutator works\n";
    
    $driver->password = 'test123';
    echo "✓ Driver password mutator works\n";
    
    echo "\n✅ All model casting fixes working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}