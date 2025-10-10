<?php

try {
    require __DIR__ . '/vendor/autoload.php';
    echo "Autoload successful\n";
    
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "App bootstrap successful\n";
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "Kernel created successfully\n";
    
    echo "Laravel version: " . app()->version() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}