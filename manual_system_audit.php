<?php
echo "🔍 DRIVELINK SYSTEM AUDIT\n";
echo str_repeat('=', 50) . "\n\n";

$errors = [];
$warnings = [];
$results = [];

// 1. Test Database Connection
echo "📊 TESTING DATABASE CONNECTION...\n";
echo str_repeat('-', 30) . "\n";

try {
    // Check if .env exists
    if (!file_exists('.env')) {
        echo "❌ .env file not found\n";
        $errors[] = ".env file missing";
    } else {
        echo "✅ .env file exists\n";
        
        // Load environment variables manually
        $envContent = file_get_contents('.env');
        $envLines = explode("\n", $envContent);
        
        $envVars = [];
        foreach ($envLines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                [$key, $value] = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
        }
        
        echo "✅ Environment variables loaded\n";
        
        // Test database connection manually
        $host = $envVars['DB_HOST'] ?? 'localhost';
        $port = $envVars['DB_PORT'] ?? '3306';
        $database = $envVars['DB_DATABASE'] ?? '';
        $username = $envVars['DB_USERNAME'] ?? '';
        $password = $envVars['DB_PASSWORD'] ?? '';
        
        echo "Database: {$database} on {$host}:{$port}\n";
        
        $dsn = "mysql:host={$host};port={$port};dbname={$database}";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ Database connection successful\n";
        
        // Test basic query
        $stmt = $pdo->query('SELECT 1 as test');
        $result = $stmt->fetch();
        if ($result) {
            echo "✅ Database queries working\n";
        }
        
        $results['database_connection'] = 'success';
    }
} catch (Exception $e) {
    $error = "Database connection failed: " . $e->getMessage();
    echo "❌ {$error}\n";
    $errors[] = $error;
    $results['database_connection'] = 'failed';
}

// 2. Check Database Tables
echo "\n🗄️ CHECKING DATABASE TABLES...\n";
echo str_repeat('-', 30) . "\n";

try {
    if (isset($pdo)) {
        $stmt = $pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "✅ Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  - {$table}\n";
        }
        
        $results['tables'] = $tables;
        
        // Check for expected core tables
        $expectedTables = [
            'users', 'admin_users', 'drivers', 'companies', 
            'company_requests', 'driver_matches', 'migrations'
        ];
        
        $missingTables = array_diff($expectedTables, $tables);
        if (!empty($missingTables)) {
            $warning = "Missing expected tables: " . implode(', ', $missingTables);
            echo "⚠️ {$warning}\n";
            $warnings[] = $warning;
        } else {
            echo "✅ All expected tables found\n";
        }
    }
} catch (Exception $e) {
    $error = "Failed to check tables: " . $e->getMessage();
    echo "❌ {$error}\n";
    $errors[] = $error;
}

// 3. Check Routes File
echo "\n🛣️ CHECKING ROUTES...\n";
echo str_repeat('-', 30) . "\n";

$routeFiles = ['routes/web.php', 'routes/api.php'];
$totalRoutes = 0;

foreach ($routeFiles as $routeFile) {
    if (file_exists($routeFile)) {
        echo "✅ {$routeFile} exists\n";
        $content = file_get_contents($routeFile);
        $routeCount = substr_count($content, 'Route::');
        $totalRoutes += $routeCount;
        echo "  - Contains ~{$routeCount} route definitions\n";
    } else {
        echo "❌ {$routeFile} missing\n";
        $errors[] = "{$routeFile} missing";
    }
}

echo "📊 Total route definitions found: ~{$totalRoutes}\n";
$results['total_routes'] = $totalRoutes;

// 4. Check Controllers
echo "\n🎮 CHECKING CONTROLLERS...\n";
echo str_repeat('-', 30) . "\n";

$controllerPath = 'app/Http/Controllers';
$controllers = [];

if (is_dir($controllerPath)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($controllerPath)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $relativePath = str_replace($controllerPath . '/', '', $file->getPathname());
            $controllers[] = $relativePath;
        }
    }
    
    echo "✅ Found " . count($controllers) . " controllers:\n";
    foreach ($controllers as $controller) {
        echo "  - {$controller}\n";
    }
    
    $results['controllers'] = count($controllers);
} else {
    echo "❌ Controllers directory not found\n";
    $errors[] = "Controllers directory missing";
}

// 5. Check Views
echo "\n👁️ CHECKING VIEWS...\n";
echo str_repeat('-', 30) . "\n";

$viewPath = 'resources/views';
$views = [];

if (is_dir($viewPath)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewPath)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $relativePath = str_replace($viewPath . '/', '', $file->getPathname());
            $views[] = $relativePath;
        }
    }
    
    echo "✅ Found " . count($views) . " view files:\n";
    
    // Categorize views
    $viewCategories = [];
    foreach ($views as $view) {
        $category = dirname($view);
        if ($category === '.') $category = 'root';
        if (!isset($viewCategories[$category])) {
            $viewCategories[$category] = 0;
        }
        $viewCategories[$category]++;
    }
    
    echo "📂 Views by category:\n";
    foreach ($viewCategories as $category => $count) {
        echo "  {$category}: {$count}\n";
    }
    
    $results['views'] = count($views);
} else {
    echo "❌ Views directory not found\n";
    $errors[] = "Views directory missing";
}

// 6. Check Dependencies
echo "\n📦 CHECKING DEPENDENCIES...\n";
echo str_repeat('-', 30) . "\n";

if (file_exists('composer.json')) {
    echo "✅ composer.json found\n";
    
    $composer = json_decode(file_get_contents('composer.json'), true);
    $dependencies = $composer['require'] ?? [];
    
    echo "✅ Found " . count($dependencies) . " dependencies\n";
    
    // Check vendor directory
    if (is_dir('vendor')) {
        echo "✅ vendor directory exists\n";
    } else {
        $warning = "vendor directory not found - run 'composer install'";
        echo "⚠️ {$warning}\n";
        $warnings[] = $warning;
    }
    
    // Check key dependencies
    $keyDeps = ['laravel/framework', 'jeroennoten/laravel-adminlte'];
    foreach ($keyDeps as $dep) {
        if (isset($dependencies[$dep])) {
            echo "✅ {$dep} found\n";
        } else {
            $warning = "{$dep} not found in dependencies";
            echo "⚠️ {$warning}\n";
            $warnings[] = $warning;
        }
    }
} else {
    echo "❌ composer.json not found\n";
    $errors[] = "composer.json missing";
}

// 7. Check Laravel Installation
echo "\n🚀 CHECKING LARAVEL INSTALLATION...\n";
echo str_repeat('-', 30) . "\n";

if (file_exists('artisan')) {
    echo "✅ artisan file exists\n";
} else {
    echo "❌ artisan file missing\n";
    $errors[] = "artisan file missing";
}

if (file_exists('bootstrap/app.php')) {
    echo "✅ bootstrap/app.php exists\n";
} else {
    echo "❌ bootstrap/app.php missing\n";
    $errors[] = "bootstrap/app.php missing";
}

if (is_dir('storage')) {
    echo "✅ storage directory exists\n";
    
    // Check storage subdirectories
    $storageDirs = ['app', 'framework', 'logs'];
    foreach ($storageDirs as $dir) {
        if (is_dir("storage/{$dir}")) {
            echo "✅ storage/{$dir} exists\n";
        } else {
            $warning = "storage/{$dir} missing";
            echo "⚠️ {$warning}\n";
            $warnings[] = $warning;
        }
    }
} else {
    echo "❌ storage directory missing\n";
    $errors[] = "storage directory missing";
}

// Summary
echo "\n" . str_repeat('=', 50) . "\n";
echo "📋 AUDIT SUMMARY\n";
echo str_repeat('=', 50) . "\n";

echo "📊 Total Errors: " . count($errors) . "\n";
echo "⚠️ Total Warnings: " . count($warnings) . "\n";

if (!empty($errors)) {
    echo "\n🚨 ERRORS:\n";
    foreach ($errors as $i => $error) {
        echo ($i + 1) . ". {$error}\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️ WARNINGS:\n";
    foreach ($warnings as $i => $warning) {
        echo ($i + 1) . ". {$warning}\n";
    }
}

if (empty($errors) && empty($warnings)) {
    echo "\n🎉 All checks passed! System is healthy.\n";
} elseif (empty($errors)) {
    echo "\n✅ No critical errors found. System is functional with minor issues.\n";
} else {
    echo "\n❌ Critical errors found. System needs attention.\n";
}

// Save results to log
$auditResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results,
    'errors' => $errors,
    'warnings' => $warnings,
    'summary' => [
        'total_errors' => count($errors),
        'total_warnings' => count($warnings),
        'status' => empty($errors) ? (empty($warnings) ? 'healthy' : 'minor_issues') : 'critical_issues'
    ]
];

// Ensure logs directory exists
if (!is_dir('storage/logs')) {
    mkdir('storage/logs', 0755, true);
}

$logPath = 'storage/logs/system_audit.log';
file_put_contents($logPath, json_encode($auditResults, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

echo "\n💾 Audit results saved to: {$logPath}\n";
echo "✅ System audit completed!\n";
?>