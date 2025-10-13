<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Document System Health Check ===\n";

// Check if database connection works
try {
    DB::connection()->getPdo();
    echo "✓ Database connection: OK\n";
} catch (Exception $e) {
    echo "✗ Database connection: FAILED - " . $e->getMessage() . "\n";
    exit(1);
}

// Check if tables exist
$tables = [
    'drivers',
    'driver_documents',
    'admin_users'
];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "✓ Table '{$table}': EXISTS\n";
        $count = DB::table($table)->count();
        echo "  - Records: {$count}\n";
    } else {
        echo "✗ Table '{$table}': MISSING\n";
    }
}

// Check columns in driver_documents table
if (Schema::hasTable('driver_documents')) {
    echo "\n=== driver_documents table structure ===\n";
    $columns = Schema::getColumnListing('driver_documents');
    foreach ($columns as $column) {
        echo "  - {$column}\n";
    }
}

// Check sample driver documents
echo "\n=== Sample Document Check ===\n";
try {
    $driver = App\Models\Drivers::first();
    if ($driver) {
        echo "✓ Sample driver found: {$driver->first_name} {$driver->surname}\n";
        echo "  - Driver ID: {$driver->driver_id}\n";
        
        $docs = $driver->documents()->get();
        echo "  - Documents count: " . $docs->count() . "\n";
        
        foreach ($docs as $doc) {
            echo "    * {$doc->document_type} - {$doc->verification_status}\n";
        }
    } else {
        echo "✗ No drivers found in database\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking documents: " . $e->getMessage() . "\n";
}

// Check storage directory
echo "\n=== Storage Check ===\n";
$storagePath = storage_path('app/public');
if (is_dir($storagePath)) {
    echo "✓ Storage directory exists: {$storagePath}\n";
    if (is_writable($storagePath)) {
        echo "✓ Storage directory is writable\n";
    } else {
        echo "✗ Storage directory is not writable\n";
    }
} else {
    echo "✗ Storage directory missing: {$storagePath}\n";
}

// Check if storage link exists
$publicLink = public_path('storage');
if (is_link($publicLink)) {
    echo "✓ Storage link exists\n";
} else {
    echo "✗ Storage link missing - run 'php artisan storage:link'\n";
}

echo "\n=== Routes Check ===\n";
try {
    $routes = [
        'admin.drivers.documents',
        'admin.drivers.files.upload',
        'admin.drivers.files.list',
        'admin.drivers.ocr-verify'
    ];
    
    foreach ($routes as $routeName) {
        try {
            $url = route($routeName, ['driver' => 1]);
            echo "✓ Route '{$routeName}': OK\n";
        } catch (Exception $e) {
            echo "✗ Route '{$routeName}': MISSING\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking routes: " . $e->getMessage() . "\n";
}

echo "\n=== Health Check Complete ===\n";