<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

echo "=== Adding Missing Columns via Laravel ===\n\n";

try {
    // Test database connection
    DB::connection()->getPdo();
    echo "✅ Connected to database\n";
    
    // Get current columns
    $currentColumns = Schema::getColumnListing('drivers');
    echo "Current columns: " . count($currentColumns) . "\n\n";
    
    // Add missing columns one by one
    $columnsToAdd = [
        'city' => 'string',
        'postal_code' => 'string', 
        'license_issue_date' => 'date',
        'years_of_experience' => 'integer',
        'previous_company' => 'string',
        'bank_id' => 'unsignedBigInteger',
        'account_number' => 'string',
        'account_name' => 'string',
        'bvn' => 'string',
        'residential_address' => 'text',
        'has_vehicle' => 'boolean',
        'vehicle_type' => 'string',
        'vehicle_year' => 'integer',
        'preferred_work_location' => 'string',
        'available_for_night_shifts' => 'boolean',
        'available_for_weekend_work' => 'boolean'
    ];
    
    foreach ($columnsToAdd as $column => $type) {
        if (!Schema::hasColumn('drivers', $column)) {
            echo "➕ Adding column '$column'...\n";
            
            Schema::table('drivers', function (Blueprint $table) use ($column, $type) {
                switch ($type) {
                    case 'string':
                        $table->string($column)->nullable();
                        break;
                    case 'text':
                        $table->text($column)->nullable();
                        break;
                    case 'date':
                        $table->date($column)->nullable();
                        break;
                    case 'integer':
                        $table->integer($column)->nullable();
                        break;
                    case 'boolean':
                        $table->boolean($column)->nullable();
                        break;
                    case 'unsignedBigInteger':
                        $table->unsignedBigInteger($column)->nullable();
                        break;
                    default:
                        $table->string($column)->nullable();
                }
            });
            
            echo "✅ Column '$column' added successfully\n";
        } else {
            echo "⏭️  Column '$column' already exists\n";
        }
    }
    
    echo "\n=== Verification ===\n";
    $newColumns = Schema::getColumnListing('drivers');
    echo "New column count: " . count($newColumns) . "\n";
    echo "Columns added: " . (count($newColumns) - count($currentColumns)) . "\n";
    
    // Verify specific columns exist
    $kycStep2Fields = ['city', 'residential_address', 'bank_id', 'bvn'];
    echo "\nKYC Step 2 critical fields:\n";
    foreach ($kycStep2Fields as $field) {
        if (Schema::hasColumn('drivers', $field)) {
            echo "✅ $field exists\n";
        } else {
            echo "❌ $field missing\n";
        }
    }
    
    echo "\n✅ Column addition process completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}