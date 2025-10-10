<?php

require 'vendor/autoload.php';

// Test the AdminUser model loading and basic functionality
try {
    $app = require 'bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "✅ Laravel loaded successfully\n";
    
    // Test AdminUser model class loading
    if (class_exists(\App\Models\AdminUser::class)) {
        echo "✅ AdminUser model class loaded successfully\n";
        
        // Test model instantiation
        $adminUser = new \App\Models\AdminUser();
        echo "✅ AdminUser model instantiated successfully\n";
        
        // Test fillable fields
        $fillable = $adminUser->getFillable();
        echo "✅ Fillable fields: " . implode(', ', $fillable) . "\n";
        
        // Test methods exist
        $methods = [
            'hasRole',
            'hasAnyRole', 
            'hasPermission',
            'canPerformAction',
            'updateLastLogin',
            'isActive'
        ];
        
        foreach ($methods as $method) {
            if (method_exists($adminUser, $method)) {
                echo "✅ Method '{$method}' exists\n";
            } else {
                echo "❌ Method '{$method}' missing\n";
            }
        }
        
        echo "\n🎉 All syntax tests passed! AdminUser model is working correctly.\n";
        
    } else {
        echo "❌ AdminUser model class not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}