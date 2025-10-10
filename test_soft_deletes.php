<?php

echo "Testing SoftDeletes Configuration...\n\n";

try {
    // Include necessary files
    require_once __DIR__ . '/vendor/autoload.php';

    // Bootstrap Laravel app
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "1. Testing Model Loading:\n";

    // Test AdminUser model
    echo "   Testing AdminUser model...\n";
    try {
        $adminUserTraits = class_uses(\App\Models\AdminUser::class);
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', $adminUserTraits)) {
            echo "   ✅ AdminUser uses SoftDeletes trait\n";
        } else {
            echo "   ❌ AdminUser does NOT use SoftDeletes trait\n";
        }
    } catch (Exception $e) {
        echo "   ❌ AdminUser error: " . $e->getMessage() . "\n";
    }

    // Test Driver model
    echo "   Testing Driver model...\n";
    try {
        $driverTraits = class_uses(\App\Models\Driver::class);
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', $driverTraits)) {
            echo "   ✅ Driver uses SoftDeletes trait\n";
        } else {
            echo "   ❌ Driver does NOT use SoftDeletes trait\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Driver error: " . $e->getMessage() . "\n";
    }

    // Test Company model
    echo "   Testing Company model...\n";
    try {
        $companyTraits = class_uses(\App\Models\Company::class);
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', $companyTraits)) {
            echo "   ✅ Company uses SoftDeletes trait\n";
        } else {
            echo "   ❌ Company does NOT use SoftDeletes trait\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Company error: " . $e->getMessage() . "\n";
    }

    echo "\n2. Testing Database Connection:\n";
    
    try {
        $connection = \DB::connection();
        $pdo = $connection->getPdo();
        echo "   ✅ Database connection successful\n";
        
        // Test if tables exist with deleted_at columns
        $tables = ['admin_users', 'drivers', 'companies'];
        
        foreach ($tables as $tableName) {
            try {
                $columns = $connection->select("SHOW COLUMNS FROM {$tableName} LIKE 'deleted_at'");
                if (count($columns) > 0) {
                    echo "   ✅ Table '{$tableName}' has deleted_at column\n";
                } else {
                    echo "   ⚠️  Table '{$tableName}' missing deleted_at column (migration needed)\n";
                }
            } catch (Exception $e) {
                echo "   ⚠️  Table '{$tableName}' does not exist (migration needed)\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    }

    echo "\n✅ SoftDeletes configuration test completed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}