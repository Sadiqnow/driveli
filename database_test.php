<?php
/**
 * Database Integrity Tests for DriveLink Application
 */

require_once __DIR__ . '/vendor/autoload.php';

class DatabaseIntegrityTester {
    private $issues = [];
    private $passed = [];
    private $warnings = [];
    private $connection;

    public function __construct() {
        try {
            // Bootstrap Laravel
            $app = require_once __DIR__ . '/bootstrap/app.php';
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            $this->connection = \Illuminate\Support\Facades\DB::connection();
        } catch (Exception $e) {
            echo "Laravel bootstrap error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public function runAllTests() {
        echo "=== DriveLink Database Integrity Assessment ===\n\n";
        
        try {
            $this->testDatabaseConnection();
            $this->testTableExistence();
            $this->testForeignKeyConstraints();
            $this->testDataConsistency();
            $this->printResults();
        } catch (Exception $e) {
            echo "Database error: " . $e->getMessage() . "\n";
            $this->issues[] = "Database error: " . $e->getMessage();
            $this->printResults();
        }
    }

    private function testDatabaseConnection() {
        echo "Testing Database Connection...\n";
        
        try {
            $result = $this->connection->select('SELECT 1 as test');
            $this->passed[] = "Database connection successful";
        } catch (Exception $e) {
            $this->issues[] = "Database connection failed: " . $e->getMessage();
            throw $e;
        }
        
        echo "✓ Database connection tests completed\n\n";
    }

    private function testTableExistence() {
        echo "Testing Table Existence...\n";
        
        $requiredTables = [
            'users',
            'admin_users', 
            'drivers',
            'companies',
            'company_requests',
            'driver_matches',
            'guarantors'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                $exists = $this->connection->select("SHOW TABLES LIKE '$table'");
                if (!empty($exists)) {
                    $this->passed[] = "Table '$table' exists";
                } else {
                    $this->issues[] = "Required table '$table' is missing";
                }
            } catch (Exception $e) {
                $this->warnings[] = "Could not check table '$table': " . $e->getMessage();
            }
        }
        
        echo "✓ Table existence tests completed\n\n";
    }

    private function testForeignKeyConstraints() {
        echo "Testing Foreign Key Constraints...\n";
        
        // Test basic referential integrity
        $foreignKeys = [
            'drivers' => [
                'verified_by' => 'admin_users.id'
            ],
            'company_requests' => [
                'created_by' => 'admin_users.id'
            ]
        ];
        
        foreach ($foreignKeys as $table => $fks) {
            foreach ($fks as $column => $reference) {
                try {
                    list($refTable, $refColumn) = explode('.', $reference);
                    
                    $orphaned = $this->connection->select("
                        SELECT COUNT(*) as count 
                        FROM $table t 
                        LEFT JOIN $refTable r ON t.$column = r.$refColumn 
                        WHERE t.$column IS NOT NULL 
                        AND r.$refColumn IS NULL
                    ");
                    
                    $count = $orphaned[0]->count ?? 0;
                    
                    if ($count > 0) {
                        $this->issues[] = "Referential integrity violation: $count orphaned records in $table.$column";
                    } else {
                        $this->passed[] = "Referential integrity maintained for $table.$column";
                    }
                } catch (Exception $e) {
                    $this->warnings[] = "Could not test $table.$column: " . $e->getMessage();
                }
            }
        }
        
        echo "✓ Foreign key constraint tests completed\n\n";
    }

    private function testDataConsistency() {
        echo "Testing Data Consistency...\n";
        
        // Test email formats
        $emailTables = ['admin_users', 'drivers', 'companies'];
        
        foreach ($emailTables as $table) {
            try {
                $tableExists = $this->connection->select("SHOW TABLES LIKE '$table'");
                if (empty($tableExists)) {
                    continue;
                }
                
                $invalidEmails = $this->connection->select("
                    SELECT COUNT(*) as count 
                    FROM $table 
                    WHERE email IS NOT NULL 
                    AND email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\\\.[A-Za-z]{2,}$'
                ");
                
                $count = $invalidEmails[0]->count ?? 0;
                if ($count > 0) {
                    $this->issues[] = "Invalid email formats found in $table ($count records)";
                } else {
                    $this->passed[] = "All email formats valid in $table";
                }
            } catch (Exception $e) {
                $this->warnings[] = "Could not validate email formats in $table: " . $e->getMessage();
            }
        }
        
        echo "✓ Data consistency tests completed\n\n";
    }

    private function printResults() {
        echo "=== DATABASE INTEGRITY ASSESSMENT RESULTS ===\n\n";
        
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
        if ($total > 0) {
            $score = (count($this->passed) / $total) * 100;
            echo "DATABASE INTEGRITY SCORE: " . round($score, 1) . "%\n";
        } else {
            echo "DATABASE INTEGRITY SCORE: Unable to calculate\n";
        }
    }
}

// Run the tests
$tester = new DatabaseIntegrityTester();
$tester->runAllTests();