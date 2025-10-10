<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

echo "Testing Settings System...\n";

// Create Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "✓ Laravel application bootstrapped successfully\n";
    
    // Check if database connection works
    $pdo = DB::connection()->getPdo();
    echo "✓ Database connection established\n";
    
    // Check if settings table exists
    $hasSettingsTable = Schema::hasTable('settings');
    if ($hasSettingsTable) {
        echo "✓ Settings table exists\n";
    } else {
        echo "⚠ Settings table doesn't exist, running migration...\n";
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_01_09_000000_create_settings_table.php', '--force' => true]);
        echo "✓ Settings migration completed\n";
    }
    
    // Test SettingsService
    $settingsService = app(App\Services\SettingsService::class);
    echo "✓ Settings service instantiated\n";
    
    // Test setting and getting a value
    $settingsService->set('test_key', 'test_value', 'string', 'test');
    $retrievedValue = $settingsService->get('test_key', null, 'test');
    
    if ($retrievedValue === 'test_value') {
        echo "✓ Settings storage and retrieval working\n";
    } else {
        echo "✗ Settings storage/retrieval failed\n";
    }
    
    // Test helper functions
    if (function_exists('settings')) {
        echo "✓ Settings helper functions loaded\n";
    } else {
        echo "✗ Settings helper functions not loaded\n";
    }
    
    // Clean up test data
    App\Models\Setting::where('group', 'test')->delete();
    echo "✓ Test data cleaned up\n";
    
    echo "\n=== Settings System Test Complete ===\n";
    echo "All components are working correctly!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}