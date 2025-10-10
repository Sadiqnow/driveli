<?php
// Laravel System Audit Test Script
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

echo "🔍 Laravel System Audit Starting...\n";
echo str_repeat('=', 50) . "\n";

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$errors = [];
$warnings = [];

// 1. Test Database Connection
echo "\n📊 Testing Database Connection...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n";
    
    // Test basic query
    $result = DB::select('SELECT 1 as test');
    if ($result) {
        echo "✅ Database queries working\n";
    }
} catch (Exception $e) {
    $error = "❌ Database connection failed: " . $e->getMessage();
    echo $error . "\n";
    $errors[] = $error;
}

// 2. Check Database Tables
echo "\n🗄️ Checking Database Tables...\n";
try {
    $tables = DB::select('SHOW TABLES');
    $tableNames = array_map(function($table) {
        return array_values((array)$table)[0];
    }, $tables);
    
    echo "✅ Found " . count($tableNames) . " tables:\n";
    foreach ($tableNames as $table) {
        echo "  - {$table}\n";
    }
    
    // Check for expected core tables
    $expectedTables = [
        'users', 'admin_users', 'drivers', 'companies', 
        'company_requests', 'driver_matches', 'migrations'
    ];
    
    $missingTables = array_diff($expectedTables, $tableNames);
    if (!empty($missingTables)) {
        $warning = "⚠️ Missing expected tables: " . implode(', ', $missingTables);
        echo $warning . "\n";
        $warnings[] = $warning;
    }
    
} catch (Exception $e) {
    $error = "❌ Failed to check tables: " . $e->getMessage();
    echo $error . "\n";
    $errors[] = $error;
}

// 3. Check Routes
echo "\n🛣️ Checking Routes...\n";
try {
    // Load routes
    require 'routes/web.php';
    require 'routes/api.php';
    
    $routes = Route::getRoutes();
    $routeCount = count($routes);
    echo "✅ Found {$routeCount} registered routes\n";
    
    $routesByMethod = [];
    $brokenRoutes = [];
    
    foreach ($routes as $route) {
        $method = implode('|', $route->methods());
        $uri = $route->uri();
        $action = $route->getActionName();
        
        if (!isset($routesByMethod[$method])) {
            $routesByMethod[$method] = 0;
        }
        $routesByMethod[$method]++;
        
        // Check if controller exists
        if (strpos($action, '@') !== false) {
            [$controller, $methodName] = explode('@', $action);
            if (!class_exists($controller)) {
                $brokenRoutes[] = "Controller not found: {$controller} for route {$uri}";
            }
        }
    }
    
    echo "\n📈 Routes by method:\n";
    foreach ($routesByMethod as $method => $count) {
        echo "  {$method}: {$count}\n";
    }
    
    if (!empty($brokenRoutes)) {
        echo "\n⚠️ Broken routes found:\n";
        foreach ($brokenRoutes as $broken) {
            echo "  - {$broken}\n";
            $warnings[] = $broken;
        }
    }
    
} catch (Exception $e) {
    $error = "❌ Failed to check routes: " . $e->getMessage();
    echo $error . "\n";
    $errors[] = $error;
}

// 4. Check Controllers
echo "\n🎮 Checking Controllers...\n";
try {
    $controllerPath = 'app/Http/Controllers';
    $controllers = [];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($controllerPath)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $controllers[] = $file->getPathname();
        }
    }
    
    echo "✅ Found " . count($controllers) . " controllers:\n";
    
    $missingControllers = [];
    foreach ($controllers as $controller) {
        $relativePath = str_replace('app/', '', $controller);
        echo "  - {$relativePath}\n";
        
        // Check if file is readable
        if (!is_readable($controller)) {
            $missingControllers[] = $relativePath;
        }
    }
    
    if (!empty($missingControllers)) {
        $warning = "⚠️ Unreadable controllers: " . implode(', ', $missingControllers);
        echo $warning . "\n";
        $warnings[] = $warning;
    }
    
} catch (Exception $e) {
    $error = "❌ Failed to check controllers: " . $e->getMessage();
    echo $error . "\n";
    $errors[] = $error;
}

// 5. Check Views
echo "\n👁️ Checking Views...\n";
try {
    $viewPath = 'resources/views';
    $views = [];
    
    if (is_dir($viewPath)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($viewPath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $views[] = $file->getPathname();
            }
        }
    }
    
    echo "✅ Found " . count($views) . " view files:\n";
    
    $viewCategories = [];
    foreach ($views as $view) {
        $relativePath = str_replace('resources/views/', '', $view);
        echo "  - {$relativePath}\n";
        
        // Categorize views
        $category = dirname($relativePath);
        if ($category === '.') $category = 'root';
        if (!isset($viewCategories[$category])) {
            $viewCategories[$category] = 0;
        }
        $viewCategories[$category]++;
    }
    
    echo "\n📂 Views by category:\n";
    foreach ($viewCategories as $category => $count) {
        echo "  {$category}: {$count}\n";
    }
    
} catch (Exception $e) {
    $error = "❌ Failed to check views: " . $e->getMessage();
    echo $error . "\n";
    $errors[] = $error;
}

// 6. Check Dependencies
echo "\n📦 Checking Dependencies...\n";
try {
    // Check composer.json exists
    $composerPath = 'composer.json';
    if (!file_exists($composerPath)) {
        $error = "❌ composer.json not found";
        echo $error . "\n";
        $errors[] = $error;
    } else {
        echo "✅ composer.json found\n";
        
        // Check vendor directory
        $vendorPath = 'vendor';
        if (!is_dir($vendorPath)) {
            $warning = "⚠️ vendor directory not found - run 'composer install'";
            echo $warning . "\n";
            $warnings[] = $warning;
        } else {
            echo "✅ vendor directory exists\n";
        }
        
        // Check key Laravel dependencies
        $composer = json_decode(file_get_contents($composerPath), true);
        $dependencies = $composer['require'] ?? [];
        
        echo "✅ Found " . count($dependencies) . " dependencies\n";
        
        $expectedDeps = ['laravel/framework', 'jeroennoten/laravel-adminlte'];
        $missingDeps = [];
        
        foreach ($expectedDeps as $dep) {
            if (!isset($dependencies[$dep])) {
                $missingDeps[] = $dep;
            }
        }
        
        if (!empty($missingDeps)) {
            $warning = "⚠️ Missing dependencies: " . implode(', ', $missingDeps);
            echo $warning . "\n";
            $warnings[] = $warning;
        }
    }
    
} catch (Exception $e) {
    $error = "❌ Failed to check dependencies: " . $e->getMessage();
    echo $error . "\n";
    $errors[] = $error;
}

// 7. Check Environment
echo "\n🌍 Checking Environment...\n";
try {
    // Check .env file
    $envPath = '.env';
    if (!file_exists($envPath)) {
        $warning = "⚠️ .env file not found";
        echo $warning . "\n";
        $warnings[] = $warning;
    } else {
        echo "✅ .env file exists\n";
    }
    
    // Check Laravel version
    $laravelVersion = $app->version();
    echo "✅ Laravel version: {$laravelVersion}\n";
    
    // Check PHP version
    $phpVersion = PHP_VERSION;
    echo "✅ PHP version: {$phpVersion}\n";
    
} catch (Exception $e) {
    $error = "❌ Failed to check environment: " . $e->getMessage();
    echo $error . "\n";
    $errors[] = $error;
}

// Summary
echo "\n" . str_repeat('=', 50) . "\n";
echo "📋 AUDIT SUMMARY\n";
echo str_repeat('=', 50) . "\n";

echo "📊 Errors: " . count($errors) . "\n";
echo "⚠️ Warnings: " . count($warnings) . "\n";

if (!empty($errors)) {
    echo "\n🚨 ERRORS:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️ WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  - {$warning}\n";
    }
}

if (empty($errors) && empty($warnings)) {
    echo "\n🎉 All checks passed! System is healthy.\n";
}

// Save to log file
$auditResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'errors' => $errors,
    'warnings' => $warnings,
    'summary' => [
        'total_errors' => count($errors),
        'total_warnings' => count($warnings),
        'status' => empty($errors) ? 'healthy' : 'issues_found'
    ]
];

$logPath = 'storage/logs/system_audit.log';
$logDir = dirname($logPath);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

file_put_contents($logPath, json_encode($auditResults, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
echo "\n💾 Audit results saved to: {$logPath}\n";

echo "\n✅ System audit completed!\n";
?>