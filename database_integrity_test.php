<?php
/**
 * Database Integrity Test for DriveLink Application
 * Tests database structure, relationships, and data integrity
 */

require_once 'vendor/autoload.php';

echo "=== DRIVELINK DATABASE INTEGRITY TEST ===" . PHP_EOL;
echo "Generated on: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "==========================================" . PHP_EOL;

try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $db = $app->make('db');
    
    echo "✓ Database connection established" . PHP_EOL;
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "1. TABLE STRUCTURE VERIFICATION" . PHP_EOL;
echo "===============================" . PHP_EOL;

$requiredTables = [
    'admin_users',
    'drivers', 
    'companies',
    'company_requests',
    'driver_matches',
    'commissions',
    'states',
    'local_governments',
    'nationalities',
    'driver_documents',
    'driver_locations',
    'driver_employment_history',
    'driver_next_of_kin',
    'driver_banking_details',
    'driver_referees',
    'driver_performance',
    'driver_preferences',
    'guarantors'
];

$existingTables = [];
$missingTables = [];

foreach ($requiredTables as $table) {
    try {
        $result = $db->select("SHOW TABLES LIKE '{$table}'");
        if (count($result) > 0) {
            $existingTables[] = $table;
            echo "✓ {$table}" . PHP_EOL;
        } else {
            $missingTables[] = $table;
            echo "✗ {$table} (missing)" . PHP_EOL;
        }
    } catch (Exception $e) {
        $missingTables[] = $table;
        echo "✗ {$table} (error: " . $e->getMessage() . ")" . PHP_EOL;
    }
}

echo PHP_EOL . "Tables Summary:" . PHP_EOL;
echo "Found: " . count($existingTables) . "/" . count($requiredTables) . PHP_EOL;
if (count($missingTables) > 0) {
    echo "Missing: " . implode(', ', $missingTables) . PHP_EOL;
}

echo PHP_EOL . "2. RELATIONSHIPS INTEGRITY TEST" . PHP_EOL;
echo "===============================" . PHP_EOL;

// Test foreign key relationships
$relationshipTests = [
    [
        'name' => 'DriverNormalized -> Nationality',
        'query' => "SELECT COUNT(*) as broken FROM drivers d 
                   LEFT JOIN nationalities n ON d.nationality_id = n.id 
                   WHERE d.nationality_id IS NOT NULL AND n.id IS NULL"
    ],
    [
        'name' => 'DriverNormalized -> State (residence)',
        'query' => "SELECT COUNT(*) as broken FROM drivers d 
                   LEFT JOIN states s ON d.residence_state_id = s.id 
                   WHERE d.residence_state_id IS NOT NULL AND s.id IS NULL"
    ],
    [
        'name' => 'DriverNormalized -> AdminUser (verified_by)',
        'query' => "SELECT COUNT(*) as broken FROM drivers d 
                   LEFT JOIN admin_users a ON d.verified_by = a.id 
                   WHERE d.verified_by IS NOT NULL AND a.id IS NULL"
    ],
    [
        'name' => 'DriverDocument -> DriverNormalized',
        'query' => "SELECT COUNT(*) as broken FROM driver_documents dd 
                   LEFT JOIN drivers d ON dd.driver_id = d.id 
                   WHERE dd.driver_id IS NOT NULL AND d.id IS NULL"
    ],
    [
        'name' => 'DriverLocation -> DriverNormalized',
        'query' => "SELECT COUNT(*) as broken FROM driver_locations dl 
                   LEFT JOIN drivers d ON dl.driver_id = d.id 
                   WHERE dl.driver_id IS NOT NULL AND d.id IS NULL"
    ]
];

$totalBrokenRelationships = 0;

foreach ($relationshipTests as $test) {
    try {
        $result = $db->select($test['query']);
        $brokenCount = $result[0]->broken ?? 0;
        $totalBrokenRelationships += $brokenCount;
        
        if ($brokenCount > 0) {
            echo "⚠ {$test['name']}: {$brokenCount} broken references" . PHP_EOL;
        } else {
            echo "✓ {$test['name']}: OK" . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "✗ {$test['name']}: Error - " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "Total broken relationships: {$totalBrokenRelationships}" . PHP_EOL;

echo PHP_EOL . "3. DATA CONSISTENCY CHECKS" . PHP_EOL;
echo "==========================" . PHP_EOL;

// Data consistency tests
$consistencyTests = [
    [
        'name' => 'Email uniqueness in drivers',
        'query' => "SELECT email, COUNT(*) as duplicates FROM drivers 
                   WHERE email IS NOT NULL GROUP BY email HAVING COUNT(*) > 1"
    ],
    [
        'name' => 'Driver ID uniqueness',
        'query' => "SELECT driver_id, COUNT(*) as duplicates FROM drivers 
                   WHERE driver_id IS NOT NULL GROUP BY driver_id HAVING COUNT(*) > 1"
    ],
    [
        'name' => 'Invalid verification statuses',
        'query' => "SELECT COUNT(*) as invalid FROM drivers 
                   WHERE verification_status NOT IN ('pending', 'verified', 'rejected', 'reviewing')"
    ],
    [
        'name' => 'Invalid driver statuses',
        'query' => "SELECT COUNT(*) as invalid FROM drivers 
                   WHERE status NOT IN ('active', 'inactive', 'suspended', 'blocked')"
    ],
    [
        'name' => 'Orphaned driver documents',
        'query' => "SELECT COUNT(*) as orphaned FROM driver_documents dd 
                   LEFT JOIN drivers d ON dd.driver_id = d.id 
                   WHERE d.id IS NULL"
    ]
];

foreach ($consistencyTests as $test) {
    try {
        $result = $db->select($test['query']);
        
        if ($test['name'] === 'Email uniqueness in drivers' || 
            $test['name'] === 'Driver ID uniqueness') {
            $count = count($result);
            if ($count > 0) {
                echo "⚠ {$test['name']}: {$count} duplicates found" . PHP_EOL;
                foreach ($result as $duplicate) {
                    echo "  - " . ($duplicate->email ?? $duplicate->driver_id) . " ({$duplicate->duplicates} times)" . PHP_EOL;
                }
            } else {
                echo "✓ {$test['name']}: OK" . PHP_EOL;
            }
        } else {
            $count = $result[0]->invalid ?? $result[0]->orphaned ?? 0;
            if ($count > 0) {
                echo "⚠ {$test['name']}: {$count} issues found" . PHP_EOL;
            } else {
                echo "✓ {$test['name']}: OK" . PHP_EOL;
            }
        }
    } catch (Exception $e) {
        echo "✗ {$test['name']}: Error - " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "4. INDEX OPTIMIZATION ANALYSIS" . PHP_EOL;
echo "==============================" . PHP_EOL;

// Check for essential indexes
$indexChecks = [
    [
        'table' => 'drivers',
        'indexes' => ['email', 'driver_id', 'verification_status', 'status', 'nationality_id']
    ],
    [
        'table' => 'driver_documents',
        'indexes' => ['driver_id', 'document_type', 'verification_status']
    ],
    [
        'table' => 'driver_locations',
        'indexes' => ['driver_id', 'location_type', 'state_id']
    ]
];

foreach ($indexChecks as $check) {
    echo "Indexes for {$check['table']}:" . PHP_EOL;
    try {
        $indexes = $db->select("SHOW INDEX FROM {$check['table']}");
        $existingIndexes = [];
        
        foreach ($indexes as $index) {
            $existingIndexes[] = $index->Column_name;
        }
        
        foreach ($check['indexes'] as $expectedIndex) {
            if (in_array($expectedIndex, $existingIndexes)) {
                echo "  ✓ {$expectedIndex}" . PHP_EOL;
            } else {
                echo "  ⚠ {$expectedIndex} (missing - performance impact)" . PHP_EOL;
            }
        }
    } catch (Exception $e) {
        echo "  ✗ Error checking indexes: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "5. STORAGE USAGE ANALYSIS" . PHP_EOL;
echo "==========================" . PHP_EOL;

try {
    $storageInfo = $db->select("
        SELECT 
            table_name,
            ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb,
            table_rows
        FROM information_schema.TABLES 
        WHERE table_schema = DATABASE()
        ORDER BY (data_length + index_length) DESC
        LIMIT 10
    ");
    
    echo "Top 10 tables by size:" . PHP_EOL;
    foreach ($storageInfo as $info) {
        echo "  {$info->table_name}: {$info->size_mb}MB ({$info->table_rows} rows)" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "✗ Error getting storage info: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== DATABASE INTEGRITY TEST COMPLETE ===" . PHP_EOL;
echo "Summary:" . PHP_EOL;
echo "- Tables: " . count($existingTables) . "/" . count($requiredTables) . " found" . PHP_EOL;
echo "- Broken relationships: {$totalBrokenRelationships}" . PHP_EOL;
echo "- Data consistency issues: See individual test results above" . PHP_EOL;