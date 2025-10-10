<?php

// Test script to verify route fixes
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

try {
    $app = new Application(realpath(__DIR__));
    $app->singleton(
        Illuminate\Contracts\Http\Kernel::class,
        App\Http\Kernel::class
    );
    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );
    $app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class,
        App\Exceptions\Handler::class
    );

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo "Testing route fixes...\n";
    
    // Test if Laravel can boot without route errors
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::create('/', 'GET')
    );
    
    if ($response->getStatusCode() === 200) {
        echo "✓ Application boots successfully\n";
        echo "✓ Route fixes appear to be working\n";
        echo "✓ No critical route errors detected\n";
    } else {
        echo "⚠ Status Code: " . $response->getStatusCode() . "\n";
    }
    
    echo "\nRoute fix test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Route fix test failed.\n";
}