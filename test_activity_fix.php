<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== TESTING ACTIVITY FUNCTION FIX ===\n";

try {
    // Test the registration controller directly
    $timestamp = time();
    
    $request = Request::create('/driver/register', 'POST', [
        'drivers_license_number' => 'TEST' . $timestamp,
        'date_of_birth' => '1990-01-01',
        'first_name' => 'Test',
        'surname' => 'Driver',
        'phone' => '080' . substr($timestamp, -8),
        'email' => 'test' . $timestamp . '@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => '1',
        '_token' => 'test-token'
    ]);
    
    // Mock the session for CSRF
    $request->setLaravelSession(new \Illuminate\Session\Store('test', new \Illuminate\Session\ArraySessionHandler(60)));
    $request->session()->put('_token', 'test-token');
    
    // Test the controller method directly
    $controller = new App\Http\Controllers\Driver\DriverAuthController();
    
    echo "Testing driver registration with fixed activity logging...\n";
    
    $response = $controller->register($request);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "✅ Registration completed successfully (redirect response)\n";
        echo "✅ Activity function error resolved\n";
        
        // Check if driver was created
        $driver = App\Models\DriverNormalized::where('email', $request->email)->first();
        if ($driver) {
            echo "✅ Driver record created in database\n";
            echo "📋 Driver ID: " . $driver->driver_id . "\n";
            
            // Clean up
            $driver->forceDelete();
            echo "🧹 Test data cleaned up\n";
        }
    } else {
        echo "❌ Registration failed\n";
        echo "Response: " . substr($response->getContent(), 0, 500) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    
    if (strpos($e->getMessage(), 'activity') !== false) {
        echo "❌ Activity function still not resolved\n";
    }
}

echo "\n=== ACTIVITY FIX TEST COMPLETE ===\n";