<?php

// Simple test page to verify settings system
echo "<h1>DriveLink Settings System Test</h1>";

try {
    // Include Laravel bootstrap
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    // Create application instance
    $app->instance('request', $request);
    $kernel->bootstrap();
    
    echo "<h2>✓ Laravel Application Loaded Successfully</h2>";
    
    // Test database connection
    $pdo = DB::connection()->getPdo();
    echo "<h3>✓ Database Connected</h3>";
    
    // Check if settings table exists
    if (Schema::hasTable('settings')) {
        echo "<h3>✓ Settings Table Exists</h3>";
        
        // Count settings
        $settingsCount = App\Models\Setting::count();
        echo "<p>Total settings in database: <strong>$settingsCount</strong></p>";
        
        // Test SettingsService
        $settingsService = app(App\Services\SettingsService::class);
        echo "<h3>✓ Settings Service Available</h3>";
        
        // Test setting and getting a value
        $testKey = 'web_test_' . time();
        $settingsService->set($testKey, 'Hello from web!', 'string', 'test');
        $retrieved = $settingsService->get($testKey, null, 'test');
        
        if ($retrieved === 'Hello from web!') {
            echo "<h3>✓ Settings Storage/Retrieval Working</h3>";
            echo "<p>Test value: <code>$retrieved</code></p>";
            
            // Clean up
            App\Models\Setting::where('key', $testKey)->delete();
        } else {
            echo "<h3>✗ Settings Storage/Retrieval Failed</h3>";
        }
        
        // Test helper functions
        if (function_exists('settings')) {
            echo "<h3>✓ Helper Functions Available</h3>";
            
            // Try to get an app setting
            $appName = app_setting('app_name', 'DriveLink');
            echo "<p>App name from settings: <strong>$appName</strong></p>";
        } else {
            echo "<h3>⚠ Helper Functions Not Available</h3>";
        }
        
        // Show some sample settings
        echo "<h3>Sample Settings:</h3>";
        $generalSettings = $settingsService->getGroup('general');
        if (!empty($generalSettings)) {
            echo "<ul>";
            foreach (array_slice($generalSettings, 0, 5) as $key => $value) {
                echo "<li><strong>$key:</strong> " . json_encode($value) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p><em>No general settings found. Run 'php artisan settings:setup --seed' to create default settings.</em></p>";
        }
        
    } else {
        echo "<h3>⚠ Settings Table Not Found</h3>";
        echo "<p>Run the migration: <code>php artisan migrate</code></p>";
    }
    
    echo "<hr>";
    echo "<h2>🎉 Settings System Test Complete!</h2>";
    echo "<p><a href='/admin/superadmin/settings'>Go to Settings Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2>✗ Error Occurred</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}