<?php
/**
 * Security Tests for DriveLink Application
 * This script tests various security vulnerabilities and configurations
 */

require_once __DIR__ . '/vendor/autoload.php';

class SecurityTester {
    private $issues = [];
    private $passed = [];
    private $warnings = [];

    public function runAllTests() {
        echo "=== DriveLink Security Assessment ===\n\n";
        
        $this->testAuthenticationSecurity();
        $this->testSessionSecurity();
        $this->testPasswordSecurity();
        $this->testFileUploadSecurity();
        $this->testSQLInjectionVulnerabilities();
        $this->testXSSSecurity();
        $this->testCSRFProtection();
        $this->testHTTPSConfiguration();
        $this->testInputValidation();
        $this->testRateLimiting();
        
        $this->printResults();
    }

    private function testAuthenticationSecurity() {
        echo "Testing Authentication Security...\n";
        
        // Check for hardcoded credentials
        $this->checkForHardcodedCredentials();
        
        // Check authentication guards
        $this->checkAuthGuards();
        
        // Check password hashing
        $this->checkPasswordHashing();
        
        // Check multi-factor authentication
        $this->checkMFAImplementation();
        
        echo "✓ Authentication security tests completed\n\n";
    }

    private function testSessionSecurity() {
        echo "Testing Session Security...\n";
        
        // Check session configuration
        $sessionConfig = $this->getConfigValue('session');
        
        if (!$sessionConfig['secure']) {
            $this->issues[] = "Session cookies not set to secure";
        } else {
            $this->passed[] = "Session cookies properly secured";
        }
        
        if ($sessionConfig['same_site'] !== 'strict') {
            $this->warnings[] = "Session SameSite not set to strict";
        } else {
            $this->passed[] = "Session SameSite properly configured";
        }
        
        if ($sessionConfig['http_only'] !== true) {
            $this->issues[] = "Session cookies not set to HttpOnly";
        } else {
            $this->passed[] = "Session cookies HttpOnly enabled";
        }
        
        echo "✓ Session security tests completed\n\n";
    }

    private function testPasswordSecurity() {
        echo "Testing Password Security...\n";
        
        // Check password policy
        $envContent = file_get_contents(__DIR__ . '/.env.example');
        
        if (strpos($envContent, 'PASSWORD_MIN_LENGTH=12') !== false) {
            $this->passed[] = "Strong password minimum length configured (12 chars)";
        } else {
            $this->issues[] = "Weak password minimum length or not configured";
        }
        
        if (strpos($envContent, 'BCRYPT_ROUNDS=12') !== false) {
            $this->passed[] = "Strong bcrypt rounds configured";
        } else {
            $this->warnings[] = "Bcrypt rounds not explicitly configured";
        }
        
        // Check for password reset vulnerabilities
        $this->checkPasswordResetSecurity();
        
        echo "✓ Password security tests completed\n\n";
    }

    private function testFileUploadSecurity() {
        echo "Testing File Upload Security...\n";
        
        $envContent = file_get_contents(__DIR__ . '/.env.example');
        
        if (strpos($envContent, 'DOCUMENTS_ALLOWED_TYPES=jpg,jpeg,png,pdf') !== false) {
            $this->passed[] = "File upload types restricted";
        } else {
            $this->issues[] = "File upload types not restricted";
        }
        
        if (strpos($envContent, 'MAX_FILE_SIZE_MB=10') !== false) {
            $this->passed[] = "File size limits configured";
        } else {
            $this->issues[] = "File size limits not configured";
        }
        
        echo "✓ File upload security tests completed\n\n";
    }

    private function testSQLInjectionVulnerabilities() {
        echo "Testing SQL Injection Vulnerabilities...\n";
        
        $controllerFiles = glob(__DIR__ . '/app/Http/Controllers/**/*.php');
        $modelFiles = glob(__DIR__ . '/app/Models/*.php');
        
        $vulnerablePatterns = [
            'DB::raw\(' => 'Direct DB::raw usage detected',
            '\$.*\.\$.*' => 'Potential string concatenation in query',
            'whereRaw\(' => 'whereRaw usage detected',
            'orderByRaw\(' => 'orderByRaw usage detected'
        ];
        
        $vulnerabilities = 0;
        foreach (array_merge($controllerFiles, $modelFiles) as $file) {
            $content = file_get_contents($file);
            foreach ($vulnerablePatterns as $pattern => $message) {
                if (preg_match('/' . $pattern . '/', $content)) {
                    $this->warnings[] = "$message in " . basename($file);
                    $vulnerabilities++;
                }
            }
        }
        
        if ($vulnerabilities === 0) {
            $this->passed[] = "No obvious SQL injection patterns found";
        }
        
        echo "✓ SQL injection tests completed\n\n";
    }

    private function testXSSSecurity() {
        echo "Testing XSS Security...\n";
        
        // Check for XSS protection in models
        $adminUserContent = file_get_contents(__DIR__ . '/app/Models/AdminUser.php');
        
        if (strpos($adminUserContent, 'htmlspecialchars') !== false) {
            $this->passed[] = "XSS protection found in AdminUser model";
        } else {
            $this->issues[] = "No XSS protection found in AdminUser model";
        }
        
        // Check blade templates for raw output
        $bladeFiles = glob(__DIR__ . '/resources/views/**/*.blade.php');
        $rawOutputCount = 0;
        
        foreach ($bladeFiles as $file) {
            $content = file_get_contents($file);
            if (preg_match_all('/\{\!\!\s*.*?\s*\!\!\}/', $content)) {
                $rawOutputCount++;
            }
        }
        
        if ($rawOutputCount > 0) {
            $this->warnings[] = "Raw output ({!! !!}) found in $rawOutputCount blade files";
        } else {
            $this->passed[] = "No unsafe raw output found in blade templates";
        }
        
        echo "✓ XSS security tests completed\n\n";
    }

    private function testCSRFProtection() {
        echo "Testing CSRF Protection...\n";
        
        $middlewareContent = file_get_contents(__DIR__ . '/app/Http/Kernel.php');
        
        if (strpos($middlewareContent, 'VerifyCsrfToken') !== false) {
            $this->passed[] = "CSRF protection middleware configured";
        } else {
            $this->issues[] = "CSRF protection middleware not found";
        }
        
        echo "✓ CSRF protection tests completed\n\n";
    }

    private function testHTTPSConfiguration() {
        echo "Testing HTTPS Configuration...\n";
        
        $envContent = file_get_contents(__DIR__ . '/.env.example');
        
        if (strpos($envContent, 'FORCE_HTTPS=true') !== false) {
            $this->passed[] = "HTTPS enforcement configured";
        } else {
            $this->issues[] = "HTTPS enforcement not configured";
        }
        
        if (strpos($envContent, 'HSTS_ENABLED=true') !== false) {
            $this->passed[] = "HSTS configured";
        } else {
            $this->warnings[] = "HSTS not configured";
        }
        
        echo "✓ HTTPS configuration tests completed\n\n";
    }

    private function testInputValidation() {
        echo "Testing Input Validation...\n";
        
        $controllerFiles = glob(__DIR__ . '/app/Http/Controllers/**/*.php');
        $validationCount = 0;
        
        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, '$request->validate(') !== false || 
                strpos($content, 'ValidationService') !== false) {
                $validationCount++;
            }
        }
        
        if ($validationCount > 0) {
            $this->passed[] = "Input validation found in $validationCount controller files";
        } else {
            $this->issues[] = "No input validation found in controllers";
        }
        
        echo "✓ Input validation tests completed\n\n";
    }

    private function testRateLimiting() {
        echo "Testing Rate Limiting...\n";
        
        $envContent = file_get_contents(__DIR__ . '/.env.example');
        
        if (strpos($envContent, 'RATE_LIMIT_ENABLED=true') !== false) {
            $this->passed[] = "Rate limiting enabled in configuration";
        } else {
            $this->warnings[] = "Rate limiting not explicitly configured";
        }
        
        if (strpos($envContent, 'MAX_LOGIN_ATTEMPTS=3') !== false) {
            $this->passed[] = "Login attempt limits configured";
        } else {
            $this->issues[] = "Login attempt limits not configured";
        }
        
        echo "✓ Rate limiting tests completed\n\n";
    }

    private function checkForHardcodedCredentials() {
        $files = glob(__DIR__ . '/{app,config,routes}/**/*.php', GLOB_BRACE);
        $patterns = [
            'password.*=.*["\'][^"\']{6,}["\']',
            'secret.*=.*["\'][^"\']{10,}["\']',
            'api_key.*=.*["\'][^"\']{10,}["\']'
        ];
        
        $found = 0;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/i', $content)) {
                    $this->issues[] = "Potential hardcoded credential in " . basename($file);
                    $found++;
                }
            }
        }
        
        if ($found === 0) {
            $this->passed[] = "No hardcoded credentials detected";
        }
    }

    private function checkAuthGuards() {
        $authConfig = $this->getConfigValue('auth');
        
        if (isset($authConfig['guards']['admin'])) {
            $this->passed[] = "Admin authentication guard configured";
        } else {
            $this->warnings[] = "Admin authentication guard not found";
        }
        
        if (isset($authConfig['guards']['api'])) {
            $this->passed[] = "API authentication guard configured";
        } else {
            $this->warnings[] = "API authentication guard not found";
        }
    }

    private function checkPasswordHashing() {
        $adminUserContent = file_get_contents(__DIR__ . '/app/Models/AdminUser.php');
        
        if (strpos($adminUserContent, 'Hash::make') !== false) {
            $this->passed[] = "Password hashing implemented in AdminUser model";
        } else {
            $this->issues[] = "No password hashing found in AdminUser model";
        }
    }

    private function checkMFAImplementation() {
        $files = glob(__DIR__ . '/app/**/*.php', GLOB_BRACE);
        $mfaFound = false;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'otp') !== false || strpos($content, 'two_factor') !== false) {
                $mfaFound = true;
                break;
            }
        }
        
        if ($mfaFound) {
            $this->passed[] = "MFA/OTP implementation found";
        } else {
            $this->warnings[] = "No MFA/OTP implementation detected";
        }
    }

    private function checkPasswordResetSecurity() {
        $adminAuthContent = file_get_contents(__DIR__ . '/app/Http/Controllers/Admin/AdminAuthController.php');
        
        if (strpos($adminAuthContent, 'user enumeration') !== false) {
            $this->passed[] = "User enumeration protection implemented";
        } else {
            $this->warnings[] = "User enumeration protection not explicitly implemented";
        }
        
        if (strpos($adminAuthContent, 'rate limiting') !== false) {
            $this->passed[] = "Rate limiting for password reset implemented";
        } else {
            $this->warnings[] = "Rate limiting for password reset not found";
        }
    }

    private function getConfigValue($config) {
        $configFile = __DIR__ . "/config/{$config}.php";
        if (!file_exists($configFile)) {
            return [];
        }
        
        // Simple config parsing (not evaluating PHP for security)
        $content = file_get_contents($configFile);
        
        // Mock common Laravel config values for testing
        return [
            'secure' => strpos($content, "'secure' => true") !== false,
            'same_site' => strpos($content, "'same_site' => 'strict'") !== false ? 'strict' : 'lax',
            'http_only' => strpos($content, "'http_only' => true") !== false,
        ];
    }

    private function printResults() {
        echo "=== SECURITY ASSESSMENT RESULTS ===\n\n";
        
        echo "🔴 CRITICAL ISSUES (" . count($this->issues) . "):\n";
        foreach ($this->issues as $issue) {
            echo "  - $issue\n";
        }
        echo "\n";
        
        echo "🟡 WARNINGS (" . count($this->warnings) . "):\n";
        foreach ($this->warnings as $warning) {
            echo "  - $warning\n";
        }
        echo "\n";
        
        echo "🟢 PASSED TESTS (" . count($this->passed) . "):\n";
        foreach ($this->passed as $passed) {
            echo "  - $passed\n";
        }
        echo "\n";
        
        $total = count($this->issues) + count($this->warnings) + count($this->passed);
        $score = (count($this->passed) / $total) * 100;
        
        echo "SECURITY SCORE: " . round($score, 1) . "%\n";
        
        if (count($this->issues) > 0) {
            echo "⚠️  PRIORITY: Address critical issues immediately\n";
        } elseif (count($this->warnings) > 0) {
            echo "ℹ️  RECOMMENDATION: Review warnings for security improvements\n";
        } else {
            echo "✅ GOOD: No critical security issues found\n";
        }
    }
}

// Run the security tests
$tester = new SecurityTester();
$tester->runAllTests();