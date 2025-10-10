<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "Testing Auth Configuration...\n\n";

    // Test auth config
    $authConfig = config('auth');
    
    echo "1. Checking Guards:\n";
    $guards = $authConfig['guards'];
    foreach ($guards as $guard => $config) {
        echo "   ✓ Guard '{$guard}' - Driver: {$config['driver']}, Provider: {$config['provider']}\n";
    }
    
    echo "\n2. Checking Providers:\n";
    $providers = $authConfig['providers'];
    foreach ($providers as $provider => $config) {
        echo "   ✓ Provider '{$provider}' - Driver: {$config['driver']}, Model: {$config['model']}\n";
    }
    
    echo "\n3. Checking Password Configs:\n";
    $passwords = $authConfig['passwords'];
    foreach ($passwords as $password => $config) {
        echo "   ✓ Password '{$password}' - Provider: {$config['provider']}, Table: {$config['table']}\n";
    }
    
    // Test if admin guard exists
    echo "\n4. Testing Admin Guard:\n";
    if (isset($guards['admin'])) {
        echo "   ✅ Admin guard is DEFINED\n";
        echo "   ✅ Admin provider: {$guards['admin']['provider']}\n";
        
        // Test if provider exists
        if (isset($providers[$guards['admin']['provider']])) {
            echo "   ✅ Admin provider '{$guards['admin']['provider']}' exists\n";
            echo "   ✅ Admin model: {$providers[$guards['admin']['provider']]['model']}\n";
        } else {
            echo "   ❌ Admin provider '{$guards['admin']['provider']}' NOT FOUND\n";
        }
    } else {
        echo "   ❌ Admin guard is NOT DEFINED\n";
    }
    
    // Test if models exist
    echo "\n5. Testing Models:\n";
    if (class_exists('App\Models\AdminUser')) {
        echo "   ✅ AdminUser model exists\n";
    } else {
        echo "   ❌ AdminUser model does not exist\n";
    }
    
    if (class_exists('App\Models\Driver')) {
        echo "   ✅ Driver model exists\n";
    } else {
        echo "   ❌ Driver model does not exist\n";
    }
    
    echo "\n✅ Auth configuration test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}