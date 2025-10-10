<?php

echo "OCR Dashboard Comprehensive Test\n";
echo "================================\n\n";

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "1. AUTHENTICATION TESTS\n";
    echo "======================\n";

    // Test database connection
    try {
        $connection = DB::connection()->getPdo();
        echo "✓ Database connection: PASSED\n";
        
        // Test admin users table
        $adminCount = DB::table('admin_users')->count();
        echo "✓ Admin users table accessible: {$adminCount} users found\n";
        
        // Test admin authentication setup
        $authConfig = config('auth.guards.admin');
        if ($authConfig) {
            echo "✓ Admin authentication guard configured\n";
        } else {
            echo "✗ Admin authentication guard missing\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    }

    echo "\n2. SECURITY TESTS\n";
    echo "================\n";

    // Check middleware configuration
    $middleware = app(\App\Http\Kernel::class);
    echo "✓ HTTP Kernel loaded\n";

    // Check CSRF protection
    $csrfToken = csrf_token();
    echo "✓ CSRF token generation: " . ($csrfToken ? 'PASSED' : 'FAILED') . "\n";

    // Check route protection
    $routes = Route::getRoutes();
    $ocrRoutes = [];
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'ocr') !== false) {
            $ocrRoutes[] = $route->uri();
        }
    }
    echo "✓ OCR routes found: " . count($ocrRoutes) . " routes\n";
    foreach ($ocrRoutes as $route) {
        echo "  - " . $route . "\n";
    }

    echo "\n3. DATABASE INTEGRITY TESTS\n";
    echo "===========================\n";

    // Check drivers table
    try {
        $tables = DB::select("SHOW TABLES LIKE 'drivers'");
        if (count($tables) > 0) {
            echo "✓ drivers table exists\n";
            
            // Check OCR columns
            $columns = DB::select("DESCRIBE drivers");
            $ocrColumns = [];
            foreach ($columns as $column) {
                $field = $column->Field;
                if (strpos($field, 'ocr') !== false || 
                    strpos($field, 'nin_') !== false || 
                    strpos($field, 'frsc_') !== false) {
                    $ocrColumns[] = $field;
                }
            }
            
            if (count($ocrColumns) > 0) {
                echo "✓ OCR columns found: " . implode(', ', $ocrColumns) . "\n";
            } else {
                echo "✗ No OCR columns found in drivers table\n";
            }
            
            // Count drivers and status
            $totalDrivers = DB::table('drivers')->count();
            echo "✓ Total drivers in database: {$totalDrivers}\n";
            
            // Check OCR status distribution
            try {
                $pendingOCR = DB::table('drivers')->whereNull('ocr_verification_status')->orWhere('ocr_verification_status', 'pending')->count();
                $passedOCR = DB::table('drivers')->where('ocr_verification_status', 'passed')->count();
                $failedOCR = DB::table('drivers')->where('ocr_verification_status', 'failed')->count();
                
                echo "✓ OCR Status Distribution:\n";
                echo "  - Pending: {$pendingOCR}\n";
                echo "  - Passed: {$passedOCR}\n";
                echo "  - Failed: {$failedOCR}\n";
                
                if ($totalDrivers > 0 && ($pendingOCR + $passedOCR + $failedOCR) == 0) {
                    echo "⚠ Warning: All drivers have NULL OCR status - might need initialization\n";
                }
                
            } catch (Exception $e) {
                echo "✗ OCR status check failed: " . $e->getMessage() . "\n";
                echo "⚠ OCR columns might not exist yet\n";
            }
            
        } else {
            echo "✗ drivers table does not exist\n";
        }
    } catch (Exception $e) {
        echo "✗ Database table check failed: " . $e->getMessage() . "\n";
    }

    echo "\n4. PERFORMANCE TESTS\n";
    echo "===================\n";

    // Test query performance
    $start = microtime(true);
    try {
    $drivers = DB::table('drivers')->limit(100)->get();
        $queryTime = (microtime(true) - $start) * 1000;
        echo "✓ Driver query performance: " . number_format($queryTime, 2) . "ms for 100 records\n";
        
        if ($queryTime > 1000) {
            echo "⚠ Warning: Query is slow (>1000ms)\n";
        }
    } catch (Exception $e) {
        echo "✗ Performance test failed: " . $e->getMessage() . "\n";
    }

    // Test memory usage
    $memoryUsage = memory_get_usage(true) / 1024 / 1024;
    echo "✓ Current memory usage: " . number_format($memoryUsage, 2) . "MB\n";

    echo "\n5. OCR SERVICE TESTS\n";
    echo "===================\n";

    // Test OCR service configuration
    try {
        $ocrService = new \App\Services\OCRVerificationService();
        echo "✓ OCRVerificationService can be instantiated\n";
        
        // Check OCR API configuration
        $ocrApiKey = config('services.ocr.api_key');
        $ocrEndpoint = config('services.ocr.endpoint');
        
        echo "✓ OCR API Key: " . ($ocrApiKey ? 'Configured' : 'Not configured') . "\n";
        echo "✓ OCR Endpoint: " . ($ocrEndpoint ? $ocrEndpoint : 'Using default') . "\n";
        
    } catch (Exception $e) {
        echo "✗ OCRVerificationService instantiation failed: " . $e->getMessage() . "\n";
    }

    echo "\n6. ROUTE ACCESSIBILITY TESTS\n";
    echo "============================\n";

    // Test OCR dashboard route
    try {
        $route = Route::getRoutes()->getByName('admin.drivers.ocr-dashboard');
        if ($route) {
            echo "✓ OCR dashboard route exists: " . $route->uri() . "\n";
        } else {
            echo "✗ OCR dashboard route not found\n";
        }
        
        $ocrRoute = Route::getRoutes()->getByName('admin.drivers.ocr-verification');
        if ($ocrRoute) {
            echo "✓ OCR verification route exists: " . $ocrRoute->uri() . "\n";
        } else {
            echo "✗ OCR verification route not found\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Route accessibility test failed: " . $e->getMessage() . "\n";
    }

    echo "\n7. VIEW TEMPLATE TESTS\n";
    echo "=====================\n";

    // Check if OCR dashboard view exists
    $ocrDashboardView = resource_path('views/admin/drivers/ocr-dashboard.blade.php');
    $ocrVerificationView = resource_path('views/admin/drivers/ocr-verification.blade.php');
    
    echo "✓ OCR Dashboard view: " . (file_exists($ocrDashboardView) ? 'EXISTS' : 'MISSING') . "\n";
    echo "✓ OCR Verification view: " . (file_exists($ocrVerificationView) ? 'EXISTS' : 'MISSING') . "\n";

    echo "\n8. CONFIGURATION TESTS\n";
    echo "======================\n";

    // Check app configuration
    echo "✓ App Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF') . "\n";
    echo "✓ App Environment: " . config('app.env') . "\n";
    echo "✓ Database Connection: " . config('database.default') . "\n";
    
    // Check storage and permissions
    $storagePath = storage_path('app');
    echo "✓ Storage directory: " . (is_writable($storagePath) ? 'WRITABLE' : 'NOT WRITABLE') . "\n";

    echo "\nSUMMARY\n";
    echo "=======\n";
    echo "OCR Dashboard diagnostic test completed.\n";
    echo "Check the results above for any issues marked with ✗ or ⚠\n\n";

} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}