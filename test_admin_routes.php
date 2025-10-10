<?php

echo "Testing Route Registration...\n";

// Simple syntax check by including the routes file
try {
    // Mock the Route facade for testing
    if (!class_exists('Route')) {
        class Route {
            public static function __callStatic($method, $args) {
                if ($method === 'prefix') {
                    return new self();
                }
                if ($method === 'name') {
                    return new self();
                }
                if ($method === 'group') {
                    if (is_callable($args[0])) {
                        $args[0]();
                    }
                    return new self();
                }
                if ($method === 'middleware') {
                    return new self();
                }
                if ($method === 'resource') {
                    echo "✓ Resource route registered: {$args[0]}\n";
                    return new self();
                }
                return new self();
            }
        }
    }
    
    // Include the routes file
    include_once 'routes/web.php';
    
    echo "✅ Routes file loaded successfully!\n";
    echo "✅ admin.drivers.* routes should be registered via Route::resource()\n";
    
} catch (Exception $e) {
    echo "❌ Error loading routes: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Fatal error loading routes: " . $e->getMessage() . "\n";
}