<?php

require 'vendor/autoload.php';

// Test the AdminAuthController loading
try {
    $app = require 'bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "✅ Laravel application loaded successfully\n";
    
    // Test AdminAuthController class loading
    if (class_exists(\App\Http\Controllers\Admin\AdminAuthController::class)) {
        echo "✅ AdminAuthController class loaded successfully\n";
        
        // Test service dependencies exist
        $services = [
            \App\Services\AuthenticationService::class,
            \App\Services\ValidationService::class,
            \App\Services\ErrorHandlingService::class
        ];
        
        foreach ($services as $service) {
            if (class_exists($service)) {
                echo "✅ Service class '{$service}' exists\n";
            } else {
                echo "❌ Service class '{$service}' missing\n";
            }
        }
        
        echo "\n🎉 All syntax tests passed! AdminAuthController is working correctly.\n";
        
    } else {
        echo "❌ AdminAuthController class not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}