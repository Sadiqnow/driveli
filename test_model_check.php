<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== MODEL EXISTENCE CHECK ===\n\n";

try {
    // Test if Drivers model exists
    $driversCount = App\Models\Drivers::count();
    echo "✅ Drivers model: EXISTS (Count: {$driversCount})\n";
} catch (Exception $e) {
    echo "❌ Drivers model: ERROR - " . $e->getMessage() . "\n";
}

try {
    // Test if DriverNormalized model exists
    $normalizedCount = App\Models\DriverNormalized::count();
    echo "❌ DriverNormalized model: STILL EXISTS (Count: {$normalizedCount})\n";
    echo "   This should not happen - the model should be removed!\n";
} catch (Exception $e) {
    echo "✅ DriverNormalized model: NOT FOUND - " . $e->getMessage() . "\n";
    echo "   This is the expected behavior!\n";
}

echo "\n=== CONCLUSION ===\n";
if (class_exists('App\Models\Drivers') && !class_exists('App\Models\DriverNormalized')) {
    echo "🎉 SUCCESS: Migration completed correctly!\n";
    echo "   - Drivers model is available\n";
    echo "   - DriverNormalized model is properly removed\n";
} else {
    echo "⚠️  WARNING: Migration may be incomplete\n";
    if (!class_exists('App\Models\Drivers')) {
        echo "   - Drivers model is missing\n";
    }
    if (class_exists('App\Models\DriverNormalized')) {
        echo "   - DriverNormalized model still exists\n";
    }
}
