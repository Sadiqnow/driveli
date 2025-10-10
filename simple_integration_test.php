<?php
echo "Simple Integration Test\n";
echo "======================\n\n";

// Test 1: Check if Laravel is working
try {
    require_once 'vendor/autoload.php';
    echo "✓ Vendor autoload successful\n";
    
    $app = require_once 'bootstrap/app.php';
    echo "✓ Laravel app bootstrap successful\n";
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "✓ Laravel kernel bootstrap successful\n";
    
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check database connection
try {
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 3: Check email configuration
echo "\nEmail Configuration:\n";
echo "- Mail driver: " . config('mail.default') . "\n";
echo "- Mail host: " . config('mail.mailers.smtp.host') . "\n";
echo "- From address: " . config('mail.from.address') . "\n";
echo "- From name: " . config('mail.from.name') . "\n";

// Test 4: Check if notification service can be instantiated
try {
    $notificationService = new \App\Services\NotificationService();
    echo "✓ NotificationService instantiated successfully\n";
} catch (Exception $e) {
    echo "❌ NotificationService failed: " . $e->getMessage() . "\n";
}

// Test 5: Check if storage is writable
$storageTest = storage_path('app/test_write.txt');
try {
    file_put_contents($storageTest, 'test');
    unlink($storageTest);
    echo "✓ Storage directory is writable\n";
} catch (Exception $e) {
    echo "❌ Storage directory not writable: " . $e->getMessage() . "\n";
}

echo "\nBasic integration test complete!\n";