<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DriveLink Migration Runner ===\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    DB::connection()->getPdo();
    echo "   ✓ Connected to: " . env('DB_DATABASE') . "\n\n";
    
    // Check current tables
    echo "2. Current tables before migration:\n";
    $currentTables = collect(DB::select('SHOW TABLES'))->pluck('Tables_in_' . env('DB_DATABASE', 'drivelink_db'));
    if ($currentTables->count() > 0) {
        foreach ($currentTables as $table) {
            $count = DB::table($table)->count();
            echo "   - $table ($count records)\n";
        }
    } else {
        echo "   No tables found\n";
    }
    
    echo "\n3. Running migrations...\n";
    
    // Check if migrations table exists
    if (!$currentTables->contains('migrations')) {
        echo "   Creating migrations table...\n";
        DB::statement("
            CREATE TABLE migrations (
                id int unsigned AUTO_INCREMENT PRIMARY KEY,
                migration varchar(255) NOT NULL,
                batch int NOT NULL
            )
        ");
        echo "   ✓ Migrations table created\n";
    }
    
    // Run key migrations in order
    $criticalMigrations = [
        '2025_08_11_170000_create_lookup_tables.php',
        '2025_08_08_115236_create_admin_users_table.php',
        '2025_08_11_172000_create_normalized_drivers_table.php',
        '2025_08_15_000002_add_missing_fields_to_drivers_table.php'
    ];
    
    foreach ($criticalMigrations as $migrationFile) {
        $migrationPath = "database/migrations/$migrationFile";
        if (file_exists($migrationPath)) {
            echo "   Running: $migrationFile\n";
            
            // Extract migration name without extension
            $migrationName = pathinfo($migrationFile, PATHINFO_FILENAME);
            
            // Check if already run
            $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
            if ($exists) {
                echo "   ⚠ Already run, skipping\n";
                continue;
            }
            
            try {
                // Include and run the migration
                $migration = include($migrationPath);
                $migration->up();
                
                // Record in migrations table
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => 1
                ]);
                
                echo "   ✓ Success\n";
            } catch (Exception $e) {
                echo "   ✗ Failed: " . $e->getMessage() . "\n";
                
                // Continue with other migrations even if one fails
                continue;
            }
        } else {
            echo "   ⚠ Migration file not found: $migrationFile\n";
        }
    }
    
    echo "\n4. Checking tables after migration:\n";
    $newTables = collect(DB::select('SHOW TABLES'))->pluck('Tables_in_' . env('DB_DATABASE', 'drivelink_db'));
    
    $expectedTables = [
        'states', 'nationalities', 'banks', 'local_governments', 
        'admin_users', 'drivers'
    ];
    
    foreach ($expectedTables as $table) {
        if ($newTables->contains($table)) {
            $count = DB::table($table)->count();
            echo "   ✓ $table ($count records)\n";
        } else {
            echo "   ✗ $table missing\n";
        }
    }
    
    // Check drivers structure
    if ($newTables->contains('drivers')) {
        echo "\n5. Checking drivers table structure...\n";
        $columns = collect(DB::select('DESCRIBE drivers'))->pluck('Field');
        
        $requiredColumns = ['id', 'driver_id', 'first_name', 'surname', 'phone', 'password', 'status', 'verification_status'];
        foreach ($requiredColumns as $column) {
            if ($columns->contains($column)) {
                echo "   ✓ $column column exists\n";
            } else {
                echo "   ✗ $column column missing\n";
            }
        }
    }
    
    // Create default admin user if none exists
    if ($newTables->contains('admin_users')) {
        echo "\n6. Checking admin users...\n";
        $adminCount = DB::table('admin_users')->count();
        if ($adminCount === 0) {
            echo "   Creating default admin user...\n";
            DB::table('admin_users')->insert([
                'name' => 'Admin',
                'email' => 'admin@drivelink.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'super_admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "   ✓ Admin user created (admin@drivelink.com / admin123)\n";
        } else {
            echo "   ✓ Admin users exist ($adminCount)\n";
        }
    }
    
    echo "\n=== MIGRATION COMPLETE ===\n";
    echo "Your DriveLink database should now be ready!\n\n";
    echo "Next steps:\n";
    echo "1. Start your web server\n";
    echo "2. Go to: http://localhost/drivelink/public/admin/login\n";
    echo "3. Login with: admin@drivelink.com / admin123\n";
    echo "4. Navigate to: /admin/drivers/create\n";
    echo "5. Test driver creation\n";
    
} catch (Exception $e) {
    echo "MIGRATION FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    
    echo "Troubleshooting:\n";
    echo "1. Ensure XAMPP MySQL is running\n";
    echo "2. Check database exists: " . env('DB_DATABASE') . "\n";
    echo "3. Verify .env database credentials\n";
    echo "4. Try: CREATE DATABASE drivelink_db;\n";
}