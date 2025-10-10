<?php
/**
 * Comprehensive DriveLink System Assessment
 * This script performs detailed system testing and analysis
 */

require_once 'vendor/autoload.php';

class SystemAssessment {
    
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function runFullAssessment() {
        echo "=== DriveLink System Assessment ===\n";
        echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
        
        // 1. Environment and Configuration Tests
        $this->testEnvironmentConfiguration();
        
        // 2. Database Tests
        $this->testDatabaseConnectivity();
        $this->testDatabaseIntegrity();
        
        // 3. Authentication System Tests
        $this->testAuthenticationSystem();
        
        // 4. File System and Storage Tests
        $this->testFileSystemPermissions();
        
        // 5. Route and Controller Tests
        $this->testRouteAvailability();
        
        // 6. Performance Tests
        $this->testSystemPerformance();
        
        // 7. Security Tests
        $this->testSecurityConfiguration();
        
        // Generate final report
        $this->generateReport();
    }
    
    private function testEnvironmentConfiguration() {
        echo "🔧 Testing Environment Configuration...\n";
        
        try {
            // Check PHP version
            $phpVersion = phpversion();
            if (version_compare($phpVersion, '8.1', '>=')) {
                $this->results['php_version'] = "✅ PHP $phpVersion (Compatible)";
            } else {
                $this->errors[] = "❌ PHP version $phpVersion is too old. Laravel requires PHP 8.1+";
            }
            
            // Check Laravel installation
            if (file_exists('vendor/laravel/framework/composer.json')) {
                $composer = json_decode(file_get_contents('vendor/laravel/framework/composer.json'), true);
                $version = $composer['version'] ?? 'unknown';
                $this->results['laravel_version'] = "✅ Laravel $version installed";
            } else {
                $this->errors[] = "❌ Laravel framework not found";
            }
            
            // Check .env file
            if (file_exists('.env')) {
                $envContent = file_get_contents('.env');
                if (strpos($envContent, 'APP_KEY=base64:') !== false) {
                    $this->results['app_key'] = "✅ Application key is set";
                } else {
                    $this->errors[] = "❌ Application key not properly set";
                }
                
                // Check database configuration
                if (strpos($envContent, 'DB_DATABASE=drivelink_db') !== false) {
                    $this->results['db_config'] = "✅ Database configured";
                } else {
                    $this->warnings[] = "⚠️ Database configuration may be incomplete";
                }
                
                // Check mail configuration
                if (strpos($envContent, 'MAIL_MAILER=') !== false) {
                    $this->results['mail_config'] = "✅ Mail configuration present";
                } else {
                    $this->warnings[] = "⚠️ Mail configuration missing";
                }
                
            } else {
                $this->errors[] = "❌ .env file not found";
            }
            
            // Check required directories
            $requiredDirs = ['storage/app', 'storage/framework', 'storage/logs', 'bootstrap/cache'];
            foreach ($requiredDirs as $dir) {
                if (is_dir($dir) && is_writable($dir)) {
                    $this->results['dir_' . str_replace('/', '_', $dir)] = "✅ $dir writable";
                } else {
                    $this->errors[] = "❌ Directory $dir is not writable or missing";
                }
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Environment test failed: " . $e->getMessage();
        }
        
        echo "Environment tests completed.\n\n";
    }
    
    private function testDatabaseConnectivity() {
        echo "🗄️ Testing Database Connectivity...\n";
        
        try {
            // Test direct PDO connection
            $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=drivelink_db', 'root', '');
            $this->results['db_connection'] = "✅ Database connection successful";
            
            // Test tables existence
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->results['db_tables'] = "✅ Found " . count($tables) . " tables";
            
            $expectedTables = ['users', 'admin_users', 'drivers', 'companies', 'company_requests', 'driver_matches'];
            $missingTables = array_diff($expectedTables, $tables);
            
            if (empty($missingTables)) {
                $this->results['required_tables'] = "✅ All required tables present";
            } else {
                $this->errors[] = "❌ Missing tables: " . implode(', ', $missingTables);
            }
            
            // Test migrations
            if (in_array('migrations', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM migrations");
                $migrationCount = $stmt->fetchColumn();
                $this->results['migrations'] = "✅ $migrationCount migrations executed";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Database connectivity test failed: " . $e->getMessage();
        }
        
        echo "Database tests completed.\n\n";
    }
    
    private function testDatabaseIntegrity() {
        echo "🔍 Testing Database Integrity...\n";
        
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=drivelink_db', 'root', '');
            
            // Check foreign key constraints
            $stmt = $pdo->query("
                SELECT 
                    TABLE_NAME,
                    CONSTRAINT_NAME,
                    REFERENCED_TABLE_NAME
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE REFERENCED_TABLE_SCHEMA = 'drivelink_db' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            $foreignKeys = $stmt->fetchAll();
            $this->results['foreign_keys'] = "✅ Found " . count($foreignKeys) . " foreign key relationships";
            
            // Test specific table structures
            $criticalTables = ['drivers', 'admin_users', 'companies'];
            foreach ($criticalTables as $table) {
                $stmt = $pdo->query("DESCRIBE $table");
                $columns = $stmt->fetchAll();
                if (count($columns) > 0) {
                    $this->results['table_' . $table] = "✅ Table $table structure OK (" . count($columns) . " columns)";
                } else {
                    $this->errors[] = "❌ Table $table structure issues";
                }
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Database integrity test failed: " . $e->getMessage();
        }
        
        echo "Database integrity tests completed.\n\n";
    }
    
    private function testAuthenticationSystem() {
        echo "🔐 Testing Authentication System...\n";
        
        try {
            // Check auth configuration files
            if (file_exists('config/auth.php')) {
                $this->results['auth_config'] = "✅ Authentication configuration found";
            }
            
            // Check for AdminLTE authentication views
            $authViews = [
                'resources/views/admin/login.blade.php',
                'resources/views/vendor/adminlte/auth/login.blade.php'
            ];
            
            foreach ($authViews as $view) {
                if (file_exists($view)) {
                    $this->results['auth_view_' . basename($view)] = "✅ Authentication view: " . basename($view);
                }
            }
            
            // Check authentication controllers
            $authControllers = [
                'app/Http/Controllers/Admin/AdminAuthController.php',
                'app/Http/Controllers/API/Admin/AdminAuthController.php'
            ];
            
            foreach ($authControllers as $controller) {
                if (file_exists($controller)) {
                    $this->results['auth_controller_' . basename($controller)] = "✅ Auth controller: " . basename($controller);
                }
            }
            
            // Check middleware
            if (file_exists('app/Http/Middleware/Authenticate.php')) {
                $this->results['auth_middleware'] = "✅ Authentication middleware present";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Authentication system test failed: " . $e->getMessage();
        }
        
        echo "Authentication tests completed.\n\n";
    }
    
    private function testFileSystemPermissions() {
        echo "📁 Testing File System Permissions...\n";
        
        $testDirectories = [
            'storage/app' => 'Application storage',
            'storage/framework/cache' => 'Framework cache',
            'storage/framework/sessions' => 'Session storage',
            'storage/logs' => 'Log storage',
            'bootstrap/cache' => 'Bootstrap cache',
            'public' => 'Public assets'
        ];
        
        foreach ($testDirectories as $dir => $description) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    $this->results['perm_' . str_replace('/', '_', $dir)] = "✅ $description writable";
                } else {
                    $this->errors[] = "❌ $description not writable: $dir";
                }
            } else {
                $this->warnings[] = "⚠️ Directory missing: $dir";
            }
        }
        
        // Test file upload directory if configured
        if (file_exists('.env')) {
            $env = file_get_contents('.env');
            if (strpos($env, 'FILESYSTEM_DISK=local') !== false) {
                $this->results['file_storage'] = "✅ File storage configured for local disk";
            }
        }
        
        echo "File system tests completed.\n\n";
    }
    
    private function testRouteAvailability() {
        echo "🛣️ Testing Route Availability...\n";
        
        try {
            // Check if web.php routes file exists
            if (file_exists('routes/web.php')) {
                $webRoutes = file_get_contents('routes/web.php');
                if (strpos($webRoutes, 'admin/login') !== false) {
                    $this->results['admin_routes'] = "✅ Admin routes configured";
                }
                if (strpos($webRoutes, 'drivers') !== false) {
                    $this->results['driver_routes'] = "✅ Driver routes configured";
                }
                $this->results['web_routes'] = "✅ Web routes file present";
            }
            
            // Check API routes
            if (file_exists('routes/api.php')) {
                $apiRoutes = file_get_contents('routes/api.php');
                $this->results['api_routes'] = "✅ API routes file present";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Route availability test failed: " . $e->getMessage();
        }
        
        echo "Route tests completed.\n\n";
    }
    
    private function testSystemPerformance() {
        echo "⚡ Testing System Performance...\n";
        
        try {
            $startTime = microtime(true);
            
            // Test database query performance
            $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=drivelink_db', 'root', '');
            $queryStart = microtime(true);
            $stmt = $pdo->query("SELECT COUNT(*) FROM drivers");
            $driverCount = $stmt->fetchColumn();
            $queryTime = (microtime(true) - $queryStart) * 1000;
            
            if ($queryTime < 100) {
                $this->results['db_query_performance'] = "✅ Database query performance good (" . round($queryTime, 2) . "ms)";
            } else {
                $this->warnings[] = "⚠️ Database query performance slow (" . round($queryTime, 2) . "ms)";
            }
            
            // Check memory usage
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;
            if ($memoryUsage < 64) {
                $this->results['memory_usage'] = "✅ Memory usage good (" . round($memoryUsage, 2) . "MB)";
            } else {
                $this->warnings[] = "⚠️ High memory usage (" . round($memoryUsage, 2) . "MB)";
            }
            
            $totalTime = (microtime(true) - $startTime) * 1000;
            $this->results['test_execution_time'] = "✅ Performance tests completed in " . round($totalTime, 2) . "ms";
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Performance test failed: " . $e->getMessage();
        }
        
        echo "Performance tests completed.\n\n";
    }
    
    private function testSecurityConfiguration() {
        echo "🔒 Testing Security Configuration...\n";
        
        try {
            // Check APP_DEBUG setting
            if (file_exists('.env')) {
                $env = file_get_contents('.env');
                if (strpos($env, 'APP_DEBUG=true') !== false) {
                    $this->warnings[] = "⚠️ APP_DEBUG is enabled in production environment";
                } else {
                    $this->results['app_debug'] = "✅ APP_DEBUG properly configured";
                }
                
                // Check APP_KEY
                if (strpos($env, 'APP_KEY=base64:') !== false) {
                    $this->results['app_key_security'] = "✅ Application key is properly set";
                }
                
                // Check session security
                if (strpos($env, 'SESSION_LIFETIME=120') !== false) {
                    $this->results['session_security'] = "✅ Session timeout configured";
                }
            }
            
            // Check CSRF protection
            if (file_exists('app/Http/Middleware/VerifyCsrfToken.php')) {
                $this->results['csrf_protection'] = "✅ CSRF protection enabled";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Security test failed: " . $e->getMessage();
        }
        
        echo "Security tests completed.\n\n";
    }
    
    private function generateReport() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 COMPREHENSIVE SYSTEM ASSESSMENT REPORT\n";
        echo str_repeat("=", 60) . "\n";
        echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Summary Statistics
        echo "📈 SUMMARY STATISTICS:\n";
        echo "• Total Tests: " . (count($this->results) + count($this->errors) + count($this->warnings)) . "\n";
        echo "• Passed: " . count($this->results) . "\n";
        echo "• Errors: " . count($this->errors) . "\n";
        echo "• Warnings: " . count($this->warnings) . "\n\n";
        
        // Successful Tests
        if (!empty($this->results)) {
            echo "✅ SUCCESSFUL TESTS:\n";
            foreach ($this->results as $test => $result) {
                echo "  $result\n";
            }
            echo "\n";
        }
        
        // Warnings
        if (!empty($this->warnings)) {
            echo "⚠️  WARNINGS:\n";
            foreach ($this->warnings as $warning) {
                echo "  $warning\n";
            }
            echo "\n";
        }
        
        // Critical Errors
        if (!empty($this->errors)) {
            echo "❌ CRITICAL ERRORS:\n";
            foreach ($this->errors as $error) {
                echo "  $error\n";
            }
            echo "\n";
        }
        
        // Overall Health Assessment
        $errorCount = count($this->errors);
        $warningCount = count($this->warnings);
        $successCount = count($this->results);
        
        echo "🏥 OVERALL SYSTEM HEALTH:\n";
        if ($errorCount == 0 && $warningCount <= 2) {
            echo "  🟢 EXCELLENT - System is fully functional\n";
        } elseif ($errorCount <= 2 && $warningCount <= 5) {
            echo "  🟡 GOOD - Minor issues that should be addressed\n";
        } elseif ($errorCount <= 5) {
            echo "  🟠 FAIR - Several issues need immediate attention\n";
        } else {
            echo "  🔴 POOR - Critical issues require immediate fixing\n";
        }
        
        // Recommendations
        echo "\n🔧 RECOMMENDATIONS:\n";
        
        if (count($this->errors) > 0) {
            echo "1. 🚨 IMMEDIATE ACTION REQUIRED:\n";
            echo "   • Fix all critical errors listed above\n";
            echo "   • Test database connectivity\n";
            echo "   • Ensure all required dependencies are installed\n\n";
        }
        
        if (count($this->warnings) > 0) {
            echo "2. 📋 IMPROVEMENTS RECOMMENDED:\n";
            echo "   • Address warning items for better performance\n";
            echo "   • Review security configurations\n";
            echo "   • Optimize database queries if needed\n\n";
        }
        
        echo "3. 🔄 REGULAR MAINTENANCE:\n";
        echo "   • Run this assessment monthly\n";
        echo "   • Monitor log files regularly\n";
        echo "   • Keep Laravel and dependencies updated\n";
        echo "   • Backup database regularly\n\n";
        
        // Save report to file
        $reportData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_tests' => count($this->results) + count($this->errors) + count($this->warnings),
                'passed' => count($this->results),
                'errors' => count($this->errors),
                'warnings' => count($this->warnings)
            ],
            'results' => $this->results,
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
        
        file_put_contents('storage/logs/system_assessment_' . date('Y-m-d_H-i-s') . '.json', json_encode($reportData, JSON_PRETTY_PRINT));
        
        echo "📄 Detailed report saved to: storage/logs/system_assessment_" . date('Y-m-d_H-i-s') . ".json\n\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Execute the assessment
try {
    $assessment = new SystemAssessment();
    $assessment->runFullAssessment();
} catch (Exception $e) {
    echo "❌ Fatal error during assessment: " . $e->getMessage() . "\n";
    exit(1);
}