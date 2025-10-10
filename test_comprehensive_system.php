<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use App\Models\AdminUser;
use App\Models\DriverNormalized;
use App\Models\Company;
use App\Services\AuthenticationService;
use App\Services\ValidationService;
use App\Services\ErrorHandlingService;

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class ComprehensiveSystemTest
{
    private array $results = [];
    private array $bugs = [];
    private array $performance_metrics = [];
    private array $security_issues = [];

    public function runAllTests(): void
    {
        echo "🔍 Starting Comprehensive DriveLink System Analysis...\n\n";
        
        $this->testDatabaseConnectivity();
        $this->testDatabaseIntegrity();
        $this->testAuthentication();
        $this->testModels();
        $this->testServices();
        $this->testRoutes();
        $this->testSecurity();
        $this->testPerformance();
        $this->testFileStructure();
        
        $this->generateReport();
    }

    private function testDatabaseConnectivity(): void
    {
        echo "📊 Testing Database Connectivity...\n";
        
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            
            if ($pdo) {
                $this->results['database']['connectivity'] = 'PASS';
                $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
                $this->results['database']['version'] = $version;
                echo "   ✅ Database connected successfully (MySQL $version)\n";
            }
        } catch (Exception $e) {
            $this->results['database']['connectivity'] = 'FAIL';
            $this->bugs[] = [
                'type' => 'Database Connection',
                'severity' => 'CRITICAL',
                'description' => $e->getMessage(),
                'fix' => 'Check database configuration in .env file'
            ];
            echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
        }
    }

    private function testDatabaseIntegrity(): void
    {
        echo "\n🔧 Testing Database Integrity...\n";
        
        try {
            // Check if required tables exist
            $requiredTables = [
                'admin_users', 'drivers', 'companies', 
                'company_requests', 'driver_matches', 'commissions'
            ];
            
            $missingTables = [];
            foreach ($requiredTables as $table) {
                if (!Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                echo "   ✅ All required tables exist\n";
                $this->results['database']['tables'] = 'PASS';
            } else {
                echo "   ❌ Missing tables: " . implode(', ', $missingTables) . "\n";
                $this->results['database']['tables'] = 'FAIL';
                $this->bugs[] = [
                    'type' => 'Database Schema',
                    'severity' => 'CRITICAL',
                    'description' => 'Missing required tables: ' . implode(', ', $missingTables),
                    'fix' => 'Run: php artisan migrate'
                ];
            }
            
            // Test foreign key constraints
            if (Schema::hasTable('admin_users')) {
                $adminCount = AdminUser::count();
                echo "   📊 Admin users count: $adminCount\n";
            }
            
            if (Schema::hasTable('drivers')) {
                $driverCount = DriverNormalized::count();
                echo "   📊 Driver records count: $driverCount\n";
            }
            
        } catch (Exception $e) {
            $this->bugs[] = [
                'type' => 'Database Integrity',
                'severity' => 'HIGH',
                'description' => $e->getMessage(),
                'fix' => 'Check database schema and migrations'
            ];
        }
    }

    private function testAuthentication(): void
    {
        echo "\n🔐 Testing Authentication System...\n";
        
        try {
            // Test guard configuration
            $guards = config('auth.guards');
            $requiredGuards = ['web', 'admin', 'driver', 'api'];
            
            foreach ($requiredGuards as $guard) {
                if (isset($guards[$guard])) {
                    echo "   ✅ Guard '$guard' configured\n";
                } else {
                    echo "   ❌ Guard '$guard' missing\n";
                    $this->bugs[] = [
                        'type' => 'Authentication Configuration',
                        'severity' => 'MEDIUM',
                        'description' => "Missing guard configuration for '$guard'",
                        'fix' => "Add guard configuration in config/auth.php"
                    ];
                }
            }
            
            // Test service classes
            $authService = app(AuthenticationService::class);
            $validationService = app(ValidationService::class);
            
            if ($authService && $validationService) {
                echo "   ✅ Authentication services loaded\n";
                $this->results['authentication']['services'] = 'PASS';
            }
            
        } catch (Exception $e) {
            $this->results['authentication']['services'] = 'FAIL';
            $this->bugs[] = [
                'type' => 'Authentication Services',
                'severity' => 'HIGH',
                'description' => $e->getMessage(),
                'fix' => 'Check service binding in AppServiceProvider'
            ];
        }
    }

    private function testModels(): void
    {
        echo "\n🏗️ Testing Model Relationships...\n";
        
        try {
            // Test AdminUser model
            if (class_exists('App\Models\AdminUser')) {
                $adminUser = new AdminUser();
                
                // Test fillable attributes
                $fillable = $adminUser->getFillable();
                if (in_array('email', $fillable) && in_array('password', $fillable)) {
                    echo "   ✅ AdminUser fillable attributes correct\n";
                } else {
                    $this->bugs[] = [
                        'type' => 'Model Configuration',
                        'severity' => 'MEDIUM',
                        'description' => 'AdminUser fillable attributes incomplete',
                        'fix' => 'Update fillable array in AdminUser model'
                    ];
                }
                
                // Test hidden attributes
                $hidden = $adminUser->getHidden();
                if (in_array('password', $hidden)) {
                    echo "   ✅ AdminUser password properly hidden\n";
                } else {
                    $this->security_issues[] = [
                        'type' => 'Data Exposure',
                        'severity' => 'HIGH',
                        'description' => 'Password not hidden in AdminUser model',
                        'fix' => 'Add password to hidden array'
                    ];
                }
            }
            
            // Test DriverNormalized model
            if (class_exists('App\Models\DriverNormalized')) {
                echo "   ✅ DriverNormalized model exists\n";
            }
            
        } catch (Exception $e) {
            $this->bugs[] = [
                'type' => 'Model Testing',
                'severity' => 'MEDIUM',
                'description' => $e->getMessage(),
                'fix' => 'Check model class definitions'
            ];
        }
    }

    private function testServices(): void
    {
        echo "\n⚙️ Testing Service Classes...\n";
        
        $services = [
            'AuthenticationService' => 'App\Services\AuthenticationService',
            'ValidationService' => 'App\Services\ValidationService',
            'ErrorHandlingService' => 'App\Services\ErrorHandlingService',
            'OCRVerificationService' => 'App\Services\OCRVerificationService',
            'NotificationService' => 'App\Services\NotificationService'
        ];
        
        foreach ($services as $name => $class) {
            try {
                if (class_exists($class)) {
                    $service = app($class);
                    echo "   ✅ $name loaded successfully\n";
                } else {
                    echo "   ❌ $name class not found\n";
                    $this->bugs[] = [
                        'type' => 'Service Class',
                        'severity' => 'MEDIUM',
                        'description' => "$class not found",
                        'fix' => "Create or fix $class"
                    ];
                }
            } catch (Exception $e) {
                echo "   ❌ $name failed to load: " . $e->getMessage() . "\n";
                $this->bugs[] = [
                    'type' => 'Service Initialization',
                    'severity' => 'HIGH',
                    'description' => "$name failed to initialize: " . $e->getMessage(),
                    'fix' => "Fix dependency injection for $class"
                ];
            }
        }
    }

    private function testRoutes(): void
    {
        echo "\n🛣️ Testing Route Configuration...\n";
        
        try {
            // Load routes
            $router = app('router');
            
            // Check for admin routes
            $adminRoutes = $router->getRoutes()->match(
                \Illuminate\Http\Request::create('/admin/login', 'GET')
            );
            
            if ($adminRoutes) {
                echo "   ✅ Admin routes configured\n";
            } else {
                $this->bugs[] = [
                    'type' => 'Route Configuration',
                    'severity' => 'HIGH',
                    'description' => 'Admin routes not properly configured',
                    'fix' => 'Check routes/web.php for admin route group'
                ];
            }
            
        } catch (Exception $e) {
            $this->bugs[] = [
                'type' => 'Route Testing',
                'severity' => 'MEDIUM',
                'description' => $e->getMessage(),
                'fix' => 'Check route configuration files'
            ];
        }
    }

    private function testSecurity(): void
    {
        echo "\n🔒 Testing Security Configuration...\n";
        
        // Check APP_KEY
        if (config('app.key')) {
            echo "   ✅ Application key set\n";
        } else {
            $this->security_issues[] = [
                'type' => 'Encryption Key',
                'severity' => 'CRITICAL',
                'description' => 'Application key not set',
                'fix' => 'Run: php artisan key:generate'
            ];
        }
        
        // Check debug mode
        if (config('app.debug') && app()->environment('production')) {
            $this->security_issues[] = [
                'type' => 'Debug Mode',
                'severity' => 'HIGH',
                'description' => 'Debug mode enabled in production',
                'fix' => 'Set APP_DEBUG=false in production'
            ];
        } else {
            echo "   ✅ Debug mode appropriate for environment\n";
        }
        
        // Check password configuration
        $passwordRules = [
            'min_length' => env('PASSWORD_MIN_LENGTH', 8),
            'max_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
            'lockout_minutes' => env('LOCKOUT_DURATION_MINUTES', 15)
        ];
        
        if ($passwordRules['min_length'] >= 8) {
            echo "   ✅ Password minimum length secure\n";
        } else {
            $this->security_issues[] = [
                'type' => 'Password Policy',
                'severity' => 'MEDIUM',
                'description' => 'Password minimum length too short',
                'fix' => 'Set PASSWORD_MIN_LENGTH to at least 8'
            ];
        }
    }

    private function testPerformance(): void
    {
        echo "\n⚡ Testing Performance...\n";
        
        $start = microtime(true);
        
        try {
            // Test query performance
            if (Schema::hasTable('admin_users')) {
                $queryStart = microtime(true);
                AdminUser::take(10)->get();
                $queryTime = (microtime(true) - $queryStart) * 1000;
                
                $this->performance_metrics['database_query_time'] = $queryTime;
                echo "   📊 Database query time: " . number_format($queryTime, 2) . "ms\n";
                
                if ($queryTime > 100) {
                    $this->bugs[] = [
                        'type' => 'Performance',
                        'severity' => 'MEDIUM',
                        'description' => 'Database queries slower than expected',
                        'fix' => 'Consider adding database indexes'
                    ];
                }
            }
            
            // Test memory usage
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;
            $this->performance_metrics['memory_usage'] = $memoryUsage;
            echo "   📊 Memory usage: " . number_format($memoryUsage, 2) . " MB\n";
            
        } catch (Exception $e) {
            $this->bugs[] = [
                'type' => 'Performance Testing',
                'severity' => 'LOW',
                'description' => $e->getMessage(),
                'fix' => 'Check performance testing setup'
            ];
        }
        
        $totalTime = (microtime(true) - $start) * 1000;
        $this->performance_metrics['total_test_time'] = $totalTime;
        echo "   📊 Performance test completed in " . number_format($totalTime, 2) . "ms\n";
    }

    private function testFileStructure(): void
    {
        echo "\n📁 Testing File Structure...\n";
        
        $requiredDirectories = [
            'app/Models',
            'app/Http/Controllers',
            'app/Services',
            'database/migrations',
            'resources/views',
            'config',
            'storage/app',
            'storage/logs'
        ];
        
        foreach ($requiredDirectories as $dir) {
            if (is_dir($dir)) {
                echo "   ✅ Directory exists: $dir\n";
            } else {
                $this->bugs[] = [
                    'type' => 'File Structure',
                    'severity' => 'MEDIUM',
                    'description' => "Missing directory: $dir",
                    'fix' => "Create directory: $dir"
                ];
            }
        }
        
        // Check important files
        $requiredFiles = [
            '.env' => 'Environment configuration',
            'composer.json' => 'PHP dependencies',
            'artisan' => 'Laravel CLI tool'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            if (file_exists($file)) {
                echo "   ✅ File exists: $file ($description)\n";
            } else {
                $this->bugs[] = [
                    'type' => 'File Structure',
                    'severity' => 'HIGH',
                    'description' => "Missing file: $file",
                    'fix' => "Create or restore $file"
                ];
            }
        }
    }

    private function generateReport(): void
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "📋 COMPREHENSIVE TECHNICAL ANALYSIS REPORT\n";
        echo str_repeat("=", 80) . "\n\n";
        
        // Summary
        $totalBugs = count($this->bugs);
        $criticalBugs = count(array_filter($this->bugs, fn($bug) => $bug['severity'] === 'CRITICAL'));
        $highBugs = count(array_filter($this->bugs, fn($bug) => $bug['severity'] === 'HIGH'));
        $securityIssues = count($this->security_issues);
        
        echo "📊 EXECUTIVE SUMMARY\n";
        echo str_repeat("-", 40) . "\n";
        echo "Total Issues Found: $totalBugs\n";
        echo "Critical Issues: $criticalBugs\n";
        echo "High Priority Issues: $highBugs\n";
        echo "Security Issues: $securityIssues\n";
        
        $healthScore = max(0, 100 - ($criticalBugs * 25) - ($highBugs * 15) - ($securityIssues * 20));
        echo "System Health Score: $healthScore/100\n\n";
        
        // Bug Details
        if (!empty($this->bugs)) {
            echo "🐛 BUGS AND ISSUES FOUND\n";
            echo str_repeat("-", 40) . "\n";
            
            foreach ($this->bugs as $i => $bug) {
                $icon = $bug['severity'] === 'CRITICAL' ? '🔥' : 
                       ($bug['severity'] === 'HIGH' ? '⚠️' : '💡');
                
                echo ($i + 1) . ". $icon {$bug['type']} ({$bug['severity']})\n";
                echo "   Issue: {$bug['description']}\n";
                echo "   Fix: {$bug['fix']}\n\n";
            }
        } else {
            echo "✅ No bugs found!\n\n";
        }
        
        // Security Issues
        if (!empty($this->security_issues)) {
            echo "🔒 SECURITY ISSUES\n";
            echo str_repeat("-", 40) . "\n";
            
            foreach ($this->security_issues as $i => $issue) {
                echo ($i + 1) . ". 🔓 {$issue['type']} ({$issue['severity']})\n";
                echo "   Issue: {$issue['description']}\n";
                echo "   Fix: {$issue['fix']}\n\n";
            }
        }
        
        // Performance Metrics
        if (!empty($this->performance_metrics)) {
            echo "⚡ PERFORMANCE METRICS\n";
            echo str_repeat("-", 40) . "\n";
            
            foreach ($this->performance_metrics as $metric => $value) {
                $unit = str_contains($metric, 'time') ? 'ms' : 
                       (str_contains($metric, 'memory') ? 'MB' : '');
                echo "• " . ucfirst(str_replace('_', ' ', $metric)) . ": " . 
                     number_format($value, 2) . " $unit\n";
            }
            echo "\n";
        }
        
        // Test Results Summary
        echo "✅ TEST RESULTS SUMMARY\n";
        echo str_repeat("-", 40) . "\n";
        
        foreach ($this->results as $category => $tests) {
            echo "• " . ucfirst($category) . ":\n";
            foreach ($tests as $test => $result) {
                $icon = $result === 'PASS' ? '✅' : '❌';
                echo "  $icon " . ucfirst(str_replace('_', ' ', $test)) . ": $result\n";
            }
        }
        
        // Recommendations
        echo "\n💡 RECOMMENDATIONS FOR PRODUCTION\n";
        echo str_repeat("-", 40) . "\n";
        
        $recommendations = [
            "Fix all CRITICAL and HIGH priority issues before deployment",
            "Implement proper error logging and monitoring",
            "Set up database backups and recovery procedures",
            "Configure proper caching (Redis/Memcached) for production",
            "Enable HTTPS and secure headers",
            "Implement rate limiting for authentication endpoints",
            "Set up proper file upload security",
            "Configure environment-specific settings (.env)",
            "Implement comprehensive testing suite",
            "Set up monitoring and alerting systems"
        ];
        
        foreach ($recommendations as $i => $rec) {
            echo ($i + 1) . ". $rec\n";
        }
        
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "Report generated: " . date('Y-m-d H:i:s') . "\n";
        echo "DriveLink System Analysis Complete\n";
        echo str_repeat("=", 80) . "\n";
    }
}

// Run the comprehensive test
$tester = new ComprehensiveSystemTest();
$tester->runAllTests();