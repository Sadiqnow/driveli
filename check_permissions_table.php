<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

try {
    // Initialize Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo "=== PERMISSIONS TABLE SCHEMA CHECK ===\n\n";

    // Check if table exists
    if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
        echo "✅ Permissions table exists\n\n";

        // Get column listing
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('permissions');
        echo "Current columns in permissions table:\n";
        foreach ($columns as $column) {
            echo "  - {$column}\n";
        }
        echo "\n";

        // Check specific columns from migration
        $requiredColumns = [
            'id',
            'name',
            'display_name',
            'description',
            'category',
            'resource',
            'action',
            'is_active',
            'meta',
            'deleted_at',
            'created_at',
            'updated_at'
        ];

        echo "Checking required columns:\n";
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columns)) {
                echo "  ✅ {$column}\n";
            } else {
                echo "  ❌ {$column} (MISSING)\n";
                $missingColumns[] = $column;
            }
        }

        if (!empty($missingColumns)) {
            echo "\n⚠️  Missing columns: " . implode(', ', $missingColumns) . "\n";
            echo "Need to add these columns to the existing permissions table.\n";
        } else {
            echo "\n✅ All required columns are present!\n";
        }

    } else {
        echo "❌ Permissions table does not exist\n";
    }

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
