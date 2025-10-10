<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Driver;

echo "Testing Driver Model and Database Connection...\n\n";

try {
    // Test database connection
    $count = Driver::count();
    echo "✅ Database connection successful - {$count} drivers found\n\n";
    
    // Show table structure
    echo "📋 Checking drivers table structure:\n";
    $columns = DB::select("DESCRIBE drivers");
    foreach ($columns as $col) {
        echo "  {$col->Field} ({$col->Type})\n";
    }
    
    echo "\n🧪 Testing driver creation with mapped fields:\n";
    
    // Test data creation with field mapping
    $testData = [
        'driver_id' => 'DR' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
        'first_name' => 'Test',
        'last_name' => 'Driver',
        'email' => 'test' . time() . '@example.com',
        'phone' => '080' . rand(10000000, 99999999),
        'password' => 'password123',
        'gender' => 'Male',
        'address' => 'Test Address Lagos',
        'state' => 'Lagos',
        'lga' => 'Lagos Island',
        'nin' => '12345678901',
        'status' => 'Available',
        'verification_status' => 'Pending'
    ];
    
    $driver = Driver::create($testData);
    echo "✅ Driver created successfully with ID: {$driver->id}\n";
    echo "   Driver ID: {$driver->driver_id}\n";
    echo "   Name: {$driver->first_name} {$driver->last_name}\n";
    echo "   Phone: {$driver->phone}\n\n";
    
    // Verify it was saved
    $savedDriver = Driver::where('driver_id', $driver->driver_id)->first();
    if ($savedDriver) {
        echo "✅ Driver verified in database\n";
        echo "   Saved Name: {$savedDriver->first_name} {$savedDriver->last_name}\n";
        echo "   Saved Status: {$savedDriver->status}\n";
        echo "   Saved State: {$savedDriver->state}\n";
    }
    
    // Clean up test data
    $savedDriver->delete();
    echo "\n🧹 Test data cleaned up\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>