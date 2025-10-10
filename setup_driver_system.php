<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== DRIVER SYSTEM SETUP ===\n\n";

try {
    echo "1. Running migrations...\n";
    
    // Run migrations
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    if ($exitCode === 0) {
        echo "✓ Migrations completed successfully\n\n";
    } else {
        echo "✗ Migration failed\n";
        echo Artisan::output();
        exit(1);
    }
    
    echo "2. Checking required tables...\n";
    
    $requiredTables = ['drivers', 'admin_users', 'nationalities', 'states'];
    foreach ($requiredTables as $table) {
        if (Schema::hasTable($table)) {
            echo "✓ $table exists\n";
        } else {
            echo "✗ $table missing\n";
        }
    }
    
    echo "\n3. Seeding lookup data...\n";
    
    // Run seeders
    $seeders = [
        'RequiredLookupDataSeeder'
    ];
    
    foreach ($seeders as $seeder) {
        try {
            $exitCode = Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
            if ($exitCode === 0) {
                echo "✓ $seeder completed\n";
            } else {
                echo "! $seeder may have had issues (but continuing)\n";
            }
        } catch (\Exception $e) {
            echo "! $seeder failed: " . $e->getMessage() . " (continuing)\n";
        }
    }
    
    echo "\n4. Creating storage directories...\n";
    
    $directories = [
        storage_path('app/public/driver_documents'),
        storage_path('app/public/driver_photos'),
        storage_path('logs'),
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "✓ Created $dir\n";
        } else {
            echo "✓ $dir already exists\n";
        }
    }
    
    echo "\n5. Running final verification...\n";
    
    // Test database connection
    $connection = DB::connection();
    echo "✓ Database connected: " . $connection->getDatabaseName() . "\n";
    
    // Check drivers table structure
    if (Schema::hasTable('drivers')) {
        $columns = Schema::getColumnListing('drivers');
        echo "✓ drivers has " . count($columns) . " columns\n";
    }
    
    // Check if we have lookup data
    $nationalityCount = DB::table('nationalities')->count();
    echo "✓ Nationalities: $nationalityCount records\n";
    
    if (Schema::hasTable('states')) {
        $stateCount = DB::table('states')->count();
        echo "✓ States: $stateCount records\n";
    }
    
    echo "\n=== SETUP COMPLETE ===\n\n";
    echo "The driver creation system should now be ready!\n\n";
    echo "Next steps:\n";
    echo "1. Access the admin panel: /admin/login\n";
    echo "2. Navigate to: /admin/drivers/create\n";
    echo "3. Fill out the form to create a test driver\n\n";
    echo "If you encounter any issues:\n";
    echo "1. Check the Laravel log files in storage/logs/\n";
    echo "2. Run: php test_driver_creation_complete.php\n";
    echo "3. Ensure your database user has proper permissions\n\n";
    
} catch (\Exception $e) {
    echo "SETUP FAILED:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n\n";
    echo "Please resolve the error and run this script again.\n";
}