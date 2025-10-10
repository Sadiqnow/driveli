<?php
/**
 * Performance Tests for DriveLink Application
 */

class PerformanceTester {
    private $issues = [];
    private $passed = [];
    private $warnings = [];

    public function runAllTests() {
        echo "=== DriveLink Performance Assessment ===\n\n";
        
        $this->testCodeComplexity();
        $this->testQueryOptimization();
        $this->testFileStructure();
        $this->testMemoryUsage();
        $this->testCachingStrategy();
        
        $this->printResults();
    }

    private function testCodeComplexity() {
        echo "Testing Code Complexity...\n";
        
        $controllerFiles = glob(__DIR__ . '/app/Http/Controllers/**/*.php');
        $modelFiles = glob(__DIR__ . '/app/Models/*.php');
        
        $totalFiles = count($controllerFiles) + count($modelFiles);
        $complexFiles = 0;
        $largeFiles = 0;
        
        foreach (array_merge($controllerFiles, $modelFiles) as $file) {
            $content = file_get_contents($file);
            $lines = substr_count($content, "\n");
            
            // Check file size
            if ($lines > 500) {
                $largeFiles++;
                $this->warnings[] = "Large file detected: " . basename($file) . " ($lines lines)";
            }
            
            // Check method complexity (basic cyclomatic complexity)
            $methodCount = preg_match_all('/(?:public|private|protected)\s+function/', $content);
            $conditionalCount = preg_match_all('/(?:if|while|for|foreach|switch|case|catch)/', $content);
            
            if ($methodCount > 0 && ($conditionalCount / $methodCount) > 10) {
                $complexFiles++;
                $this->warnings[] = "High complexity in " . basename($file) . " (avg " . round($conditionalCount / $methodCount, 1) . " conditionals per method)";
            }
        }
        
        if ($largeFiles === 0) {
            $this->passed[] = "No excessively large files found";
        }
        
        if ($complexFiles === 0) {
            $this->passed[] = "No high complexity files found";
        }
        
        $this->passed[] = "Analyzed $totalFiles PHP files for complexity";
        
        echo "✓ Code complexity tests completed\n\n";
    }

    private function testQueryOptimization() {
        echo "Testing Query Optimization...\n";
        
        $modelFiles = glob(__DIR__ . '/app/Models/*.php');
        $controllerFiles = glob(__DIR__ . '/app/Http/Controllers/**/*.php');
        
        $nPlusOneIssues = 0;
        $rawQueryUsage = 0;
        $eagerLoadingUsage = 0;
        
        foreach (array_merge($modelFiles, $controllerFiles) as $file) {
            $content = file_get_contents($file);
            
            // Check for N+1 query patterns
            if (preg_match_all('/foreach.*->/', $content) > preg_match_all('/->with\(/', $content)) {
                if (strpos($content, 'foreach') !== false && strpos($content, '->') !== false) {
                    $nPlusOneIssues++;
                }
            }
            
            // Check for raw queries
            if (strpos($content, 'DB::raw') !== false || strpos($content, 'whereRaw') !== false) {
                $rawQueryUsage++;
                $this->warnings[] = "Raw query usage in " . basename($file);
            }
            
            // Check for eager loading
            if (strpos($content, '->with(') !== false || strpos($content, '->load(') !== false) {
                $eagerLoadingUsage++;
            }
        }
        
        if ($nPlusOneIssues === 0) {
            $this->passed[] = "No obvious N+1 query patterns detected";
        } else {
            $this->warnings[] = "Potential N+1 query issues in $nPlusOneIssues files";
        }
        
        if ($eagerLoadingUsage > 0) {
            $this->passed[] = "Eager loading implemented in $eagerLoadingUsage files";
        } else {
            $this->warnings[] = "Limited eager loading usage detected";
        }
        
        echo "✓ Query optimization tests completed\n\n";
    }

    private function testFileStructure() {
        echo "Testing File Structure Performance...\n";
        
        // Check for large directories
        $directories = [
            'app/Http/Controllers',
            'app/Models', 
            'resources/views',
            'database/migrations'
        ];
        
        foreach ($directories as $dir) {
            if (is_dir(__DIR__ . '/' . $dir)) {
                $files = glob(__DIR__ . '/' . $dir . '/**/*.php');
                $fileCount = count($files);
                
                if ($fileCount > 50) {
                    $this->warnings[] = "Large directory: $dir has $fileCount files (consider subdividing)";
                } else {
                    $this->passed[] = "Directory size acceptable: $dir ($fileCount files)";
                }
            }
        }
        
        // Check for deep nesting
        $maxDepth = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/app'));
        
        foreach ($iterator as $file) {
            $depth = $iterator->getDepth();
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
            }
        }
        
        if ($maxDepth > 5) {
            $this->warnings[] = "Deep directory nesting detected (max depth: $maxDepth)";
        } else {
            $this->passed[] = "Directory nesting acceptable (max depth: $maxDepth)";
        }
        
        echo "✓ File structure tests completed\n\n";
    }

    private function testMemoryUsage() {
        echo "Testing Memory Usage Patterns...\n";
        
        $files = glob(__DIR__ . '/app/**/*.php', GLOB_BRACE);
        $memoryIssues = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            // Check for memory-intensive operations
            $patterns = [
                '/->get\(\)/' => 'Potential memory issue: ->get() without limit',
                '/->all\(\)/' => 'Potential memory issue: ->all() loads all records',
                '/file_get_contents.*\.php/' => 'Warning: Dynamic file inclusion detected'
            ];
            
            foreach ($patterns as $pattern => $message) {
                if (preg_match($pattern, $content)) {
                    $memoryIssues++;
                    $this->warnings[] = $message . " in " . basename($file);
                }
            }
        }
        
        if ($memoryIssues === 0) {
            $this->passed[] = "No obvious memory usage issues detected";
        }
        
        // Check for pagination usage
        $paginationUsage = 0;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, '->paginate(') !== false || strpos($content, '->simplePaginate(') !== false) {
                $paginationUsage++;
            }
        }
        
        if ($paginationUsage > 0) {
            $this->passed[] = "Pagination implemented in $paginationUsage files";
        } else {
            $this->warnings[] = "Limited pagination usage detected";
        }
        
        echo "✓ Memory usage tests completed\n\n";
    }

    private function testCachingStrategy() {
        echo "Testing Caching Strategy...\n";
        
        $files = glob(__DIR__ . '/app/**/*.php', GLOB_BRACE);
        $cacheUsage = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            // Check for caching usage
            if (strpos($content, 'Cache::') !== false || 
                strpos($content, '->remember(') !== false ||
                strpos($content, '->cache(') !== false) {
                $cacheUsage++;
            }
        }
        
        if ($cacheUsage > 0) {
            $this->passed[] = "Caching implemented in $cacheUsage files";
        } else {
            $this->warnings[] = "No caching strategy detected";
        }
        
        // Check config for cache configuration
        if (file_exists(__DIR__ . '/config/cache.php')) {
            $cacheConfig = file_get_contents(__DIR__ . '/config/cache.php');
            if (strpos($cacheConfig, "'driver' => 'file'") !== false) {
                $this->warnings[] = "Using file cache driver (consider Redis/Memcached for production)";
            } else {
                $this->passed[] = "Cache driver configuration found";
            }
        }
        
        echo "✓ Caching strategy tests completed\n\n";
    }

    private function printResults() {
        echo "=== PERFORMANCE ASSESSMENT RESULTS ===\n\n";
        
        echo "🔴 CRITICAL ISSUES (" . count($this->issues) . "):\n";
        foreach ($this->issues as $issue) {
            echo "  - $issue\n";
        }
        echo "\n";
        
        echo "🟡 PERFORMANCE WARNINGS (" . count($this->warnings) . "):\n";
        foreach ($this->warnings as $warning) {
            echo "  - $warning\n";
        }
        echo "\n";
        
        echo "🟢 GOOD PRACTICES (" . count($this->passed) . "):\n";
        foreach ($this->passed as $passed) {
            echo "  - $passed\n";
        }
        echo "\n";
        
        $total = count($this->issues) + count($this->warnings) + count($this->passed);
        if ($total > 0) {
            $score = (count($this->passed) / $total) * 100;
            echo "PERFORMANCE SCORE: " . round($score, 1) . "%\n";
        } else {
            echo "PERFORMANCE SCORE: Unable to calculate\n";
        }
        
        if (count($this->issues) > 0) {
            echo "⚠️  PRIORITY: Address critical performance issues immediately\n";
        } elseif (count($this->warnings) > 0) {
            echo "ℹ️  RECOMMENDATION: Review warnings for performance improvements\n";
        } else {
            echo "✅ GOOD: No critical performance issues found\n";
        }
    }
}

// Run the performance tests
$tester = new PerformanceTester();
$tester->runAllTests();