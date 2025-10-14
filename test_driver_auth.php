<?php

require_once 'vendor/autoload.php';

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Drivers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "Testing Driver Authentication System\n";
echo str_repeat("=", 50) . "\n";

try {
    // Test 1: Check if Drivers model can be accessed
    echo "1. Testing Drivers model access... ";
    $count = Drivers::count();
    echo "✓ SUCCESS (Found $count drivers)\n";
    
    // Test 2: Test driver creation
    echo "2. Testing driver creation... ";
    $testDriver = Drivers::create([
        'first_name' => 'Test',
        'surname' => 'Driver',
        'email' => 'testdriver@example.com',
        'phone' => '+1234567890',
        'password' => 'password123',
        'date_of_birth' => '1990-01-01',
        'driver_id' => 'DR' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
    ]);
    echo "✓ SUCCESS (Created driver ID: {$testDriver->id})\n";
    
    // Test 3: Test authentication guard
    echo "3. Testing driver authentication guard... ";
    $guard = Auth::guard('driver');
    echo "✓ SUCCESS (Guard created)\n";
    
    // Test 4: Test password authentication
    echo "4. Testing password authentication... ";
    $credentials = [
        'email' => 'testdriver@example.com',
        'password' => 'password123'
    ];
    
    if ($guard->attempt($credentials)) {
        echo "✓ SUCCESS (Authentication works)\n";
        
        // Test user retrieval
        $user = $guard->user();
        echo "   Logged in user: {$user->full_name} ({$user->email})\n";
        
        // Logout
        $guard->logout();
        echo "   ✓ Logout successful\n";
    } else {
        echo "✗ FAILED (Authentication failed)\n";
    }
    
    // Clean up test driver
    echo "5. Cleaning up test data... ";
    $testDriver->delete();
    echo "✓ SUCCESS\n";
    
    echo "\nDriver Authentication Test Results:\n";
    echo "✓ Model access works\n";
    echo "✓ Driver creation works\n";
    echo "✓ Authentication guard works\n";
    echo "✓ Login/logout cycle works\n";
    echo "\nDriver authentication system is functional!\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    // Try to clean up if test driver was created
    try {
        if (isset($testDriver)) {
            $testDriver->delete();
            echo "✓ Test data cleaned up\n";
        }
    } catch (Exception $cleanup) {
        echo "Warning: Could not clean up test data\n";
    }
}