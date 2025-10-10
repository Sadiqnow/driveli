<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Exception;

class SystemAudit extends Command
{
    protected $signature = 'system:audit {--log : Save results to log file}';
    protected $description = 'Audit all system functionality, detect missing dependencies, invalid tables, broken routes, and log errors';
    
    private $errors = [];
    private $warnings = [];
    private $results = [];

    public function handle()
    {
        $this->info('🔍 Starting System Audit...');
        
        $startTime = now();
        
        // Initialize audit log
        if ($this->option('log')) {
            $this->initializeAuditLog();
        }
        
        // Run all audit checks
        $this->auditDatabaseConnection();
        $this->auditDatabaseTables();
        $this->auditRoutes();
        $this->auditControllers();
        $this->auditViews();
        $this->auditDependencies();
        $this->auditEnvironment();
        
        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);
        
        // Display summary
        $this->displaySummary($duration);
        
        // Save to log if requested
        if ($this->option('log')) {
            $this->saveAuditLog();
        }
        
        return $this->hasErrors() ? Command::FAILURE : Command::SUCCESS;
    }
    
    private function auditDatabaseConnection()
    {
        $this->info("\n📊 Checking Database Connection...");
        
        try {
            DB::connection()->getPdo();
            $this->line("✅ Database connection successful");
            $this->results['database_connection'] = 'success';
            
            // Test basic query
            $result = DB::select('SELECT 1 as test');
            if ($result) {
                $this->line("✅ Database queries working");
            }
            
        } catch (Exception $e) {
            $error = "❌ Database connection failed: " . $e->getMessage();
            $this->error($error);
            $this->errors[] = $error;
            $this->results['database_connection'] = 'failed';
        }
    }
    
    private function auditDatabaseTables()
    {
        $this->info("\n🗄️ Checking Database Tables...");
        
        try {
            $tables = DB::select('SHOW TABLES');
            $tableNames = array_map(function($table) {
                return array_values((array)$table)[0];
            }, $tables);
            
            $this->line("✅ Found " . count($tableNames) . " tables:");
            foreach ($tableNames as $table) {
                $this->line("  - {$table}");
            }
            
            $this->results['tables'] = $tableNames;
            
            // Check for expected core tables
            $expectedTables = [
                'users', 'admin_users', 'drivers', 'companies', 
                'company_requests', 'driver_matches', 'migrations'
            ];
            
            $missingTables = array_diff($expectedTables, $tableNames);
            if (!empty($missingTables)) {
                $warning = "⚠️ Missing expected tables: " . implode(', ', $missingTables);
                $this->warn($warning);
                $this->warnings[] = $warning;
            }
            
        } catch (Exception $e) {
            $error = "❌ Failed to check tables: " . $e->getMessage();
            $this->error($error);
            $this->errors[] = $error;
        }
    }
    
    private function auditRoutes()
    {
        $this->info("\n🛣️ Checking Routes...");
        
        try {
            $routes = Route::getRoutes();
            $routeCount = count($routes);
            $this->line("✅ Found {$routeCount} registered routes");
            
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
                    [$controller, $method] = explode('@', $action);
                    if (!class_exists($controller)) {
                        $brokenRoutes[] = "Controller not found: {$controller} for route {$uri}";
                    }
                }
            }
            
            $this->line("\n📈 Routes by method:");
            foreach ($routesByMethod as $method => $count) {
                $this->line("  {$method}: {$count}");
            }
            
            if (!empty($brokenRoutes)) {
                $this->warn("\n⚠️ Broken routes found:");
                foreach ($brokenRoutes as $broken) {
                    $this->warn("  - {$broken}");
                    $this->warnings[] = $broken;
                }
            }
            
            $this->results['routes'] = [
                'total' => $routeCount,
                'by_method' => $routesByMethod,
                'broken' => $brokenRoutes
            ];
            
        } catch (Exception $e) {
            $error = "❌ Failed to check routes: " . $e->getMessage();
            $this->error($error);
            $this->errors[] = $error;
        }
    }
    
    private function auditControllers()
    {
        $this->info("\n🎮 Checking Controllers...");
        
        try {
            $controllerPath = app_path('Http/Controllers');
            $controllers = $this->getPhpFiles($controllerPath);
            
            $this->line("✅ Found " . count($controllers) . " controllers:");
            
            $missingControllers = [];
            foreach ($controllers as $controller) {
                $relativePath = str_replace(app_path() . '/', '', $controller);
                $this->line("  - {$relativePath}");
                
                // Check if file is readable
                if (!is_readable($controller)) {
                    $missingControllers[] = $relativePath;
                }
            }
            
            if (!empty($missingControllers)) {
                $warning = "⚠️ Unreadable controllers: " . implode(', ', $missingControllers);
                $this->warn($warning);
                $this->warnings[] = $warning;
            }
            
            $this->results['controllers'] = [
                'total' => count($controllers),
                'files' => array_map(function($path) {
                    return str_replace(app_path() . '/', '', $path);
                }, $controllers),
                'missing' => $missingControllers
            ];
            
        } catch (Exception $e) {
            $error = "❌ Failed to check controllers: " . $e->getMessage();
            $this->error($error);
            $this->errors[] = $error;
        }
    }
    
    private function auditViews()
    {
        $this->info("\n👁️ Checking Views...");
        
        try {
            $viewPath = resource_path('views');
            $views = $this->getBladeFiles($viewPath);
            
            $this->line("✅ Found " . count($views) . " view files:");
            
            $viewCategories = [];
            foreach ($views as $view) {
                $relativePath = str_replace(resource_path('views') . '/', '', $view);
                $this->line("  - {$relativePath}");
                
                // Categorize views
                $category = dirname($relativePath);
                if ($category === '.') $category = 'root';
                if (!isset($viewCategories[$category])) {
                    $viewCategories[$category] = 0;
                }
                $viewCategories[$category]++;
            }
            
            $this->line("\n📂 Views by category:");
            foreach ($viewCategories as $category => $count) {
                $this->line("  {$category}: {$count}");
            }
            
            $this->results['views'] = [
                'total' => count($views),
                'categories' => $viewCategories
            ];
            
        } catch (Exception $e) {
            $error = "❌ Failed to check views: " . $e->getMessage();
            $this->error($error);
            $this->errors[] = $error;
        }
    }
    
    private function auditDependencies()
    {
        $this->info("\n📦 Checking Dependencies...");
        
        try {
            // Check composer.json exists
            $composerPath = base_path('composer.json');
            if (!file_exists($composerPath)) {
                $error = "❌ composer.json not found";
                $this->error($error);
                $this->errors[] = $error;
                return;
            }
            
            $this->line("✅ composer.json found");
            
            // Check vendor directory
            $vendorPath = base_path('vendor');
            if (!is_dir($vendorPath)) {
                $warning = "⚠️ vendor directory not found - run 'composer install'";
                $this->warn($warning);
                $this->warnings[] = $warning;
            } else {
                $this->line("✅ vendor directory exists");
            }
            
            // Check key Laravel dependencies
            $composer = json_decode(file_get_contents($composerPath), true);
            $dependencies = $composer['require'] ?? [];
            
            $expectedDeps = ['laravel/framework', 'jeroennoten/laravel-adminlte'];
            $missingDeps = [];
            
            foreach ($expectedDeps as $dep) {
                if (!isset($dependencies[$dep])) {
                    $missingDeps[] = $dep;
                }
            }
            
            if (!empty($missingDeps)) {
                $warning = "⚠️ Missing dependencies: " . implode(', ', $missingDeps);
                $this->warn($warning);
                $this->warnings[] = $warning;
            }
            
            $this->results['dependencies'] = [
                'composer_exists' => true,
                'vendor_exists' => is_dir($vendorPath),
                'total_dependencies' => count($dependencies),
                'missing' => $missingDeps
            ];
            
        } catch (Exception $e) {
            $error = "❌ Failed to check dependencies: " . $e->getMessage();
            $this->error($error);
            $this->errors[] = $error;
        }
    }
    
    private function auditEnvironment()
    {
        $this->info("\n🌍 Checking Environment...");
        
        try {
            // Check .env file
            $envPath = base_path('.env');
            if (!file_exists($envPath)) {
                $warning = "⚠️ .env file not found";
                $this->warn($warning);
                $this->warnings[] = $warning;
            } else {
                $this->line("✅ .env file exists");
            }
            
            // Check key environment variables
            $requiredEnvVars = [
                'APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 
                'DB_DATABASE', 'DB_USERNAME'
            ];
            
            $missingEnvVars = [];
            foreach ($requiredEnvVars as $var) {
                if (!env($var)) {
                    $missingEnvVars[] = $var;
                }
            }
            
            if (!empty($missingEnvVars)) {
                $warning = "⚠️ Missing environment variables: " . implode(', ', $missingEnvVars);
                $this->warn($warning);
                $this->warnings[] = $warning;
            }
            
            // Check Laravel version
            $laravelVersion = app()->version();
            $this->line("✅ Laravel version: {$laravelVersion}");
            
            // Check PHP version
            $phpVersion = PHP_VERSION;
            $this->line("✅ PHP version: {$phpVersion}");
            
            $this->results['environment'] = [
                'env_file_exists' => file_exists($envPath),
                'laravel_version' => $laravelVersion,
                'php_version' => $phpVersion,
                'missing_env_vars' => $missingEnvVars
            ];
            
        } catch (Exception $e) {
            $error = "❌ Failed to check environment: " . $e->getMessage();
            $this->error($error);
            $this->errors[] = $error;
        }
    }
    
    private function getPhpFiles($directory)
    {
        $files = [];
        if (is_dir($directory)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }
        return $files;
    }
    
    private function getBladeFiles($directory)
    {
        $files = [];
        if (is_dir($directory)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    $files[] = $file->getPathname();
                }
            }
        }
        return $files;
    }
    
    private function displaySummary($duration)
    {
        $this->info("\n" . str_repeat('=', 50));
        $this->info("📋 AUDIT SUMMARY");
        $this->info(str_repeat('=', 50));
        
        $this->line("⏱️ Duration: {$duration} seconds");
        $this->line("📊 Errors: " . count($this->errors));
        $this->line("⚠️ Warnings: " . count($this->warnings));
        
        if (!empty($this->errors)) {
            $this->error("\n🚨 ERRORS:");
            foreach ($this->errors as $error) {
                $this->error("  - {$error}");
            }
        }
        
        if (!empty($this->warnings)) {
            $this->warn("\n⚠️ WARNINGS:");
            foreach ($this->warnings as $warning) {
                $this->warn("  - {$warning}");
            }
        }
        
        if (empty($this->errors) && empty($this->warnings)) {
            $this->info("\n🎉 All checks passed! System is healthy.");
        }
    }
    
    private function hasErrors()
    {
        return !empty($this->errors);
    }
    
    private function initializeAuditLog()
    {
        $logPath = storage_path('logs/system_audit.log');
        $logDir = dirname($logPath);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->line("📝 Audit log will be saved to: {$logPath}");
    }
    
    private function saveAuditLog()
    {
        $logPath = storage_path('logs/system_audit.log');
        
        $logData = [
            'timestamp' => now()->toISOString(),
            'results' => $this->results,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'summary' => [
                'total_errors' => count($this->errors),
                'total_warnings' => count($this->warnings),
                'status' => empty($this->errors) ? 'healthy' : 'issues_found'
            ]
        ];
        
        file_put_contents($logPath, json_encode($logData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        
        $this->info("\n💾 Audit results saved to: {$logPath}");
    }
}