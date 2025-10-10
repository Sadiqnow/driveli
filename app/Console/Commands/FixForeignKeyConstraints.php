<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixForeignKeyConstraints extends Command
{
    protected $signature = 'drivelink:fix-foreign-keys {--force : Force the operation without confirmation}';
    protected $description = 'Fix foreign key constraints preventing table drops';

    public function handle()
    {
        $this->info('DriveLink Foreign Key Constraint Fixer');
        $this->info('=====================================');
        
        if (!$this->option('force')) {
            if (!$this->confirm('This will drop tables with foreign key constraints. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            // Disable foreign key checks
            $this->info('Disabling foreign key checks...');
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            // Get all tables in the database
            $dbName = DB::connection()->getDatabaseName();
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
            
            $tablesToDrop = [
                'guarantors',
                'driver_matches',
                'driver_performances', 
                'commissions',
                'driver_locations',
                'driver_employment_history',
                'driver_next_of_kin',
                'driver_banking_details',
                'driver_referees',
                'driver_preferences',
                'driver_documents',
                'drivers'
            ];

            $this->info('Checking and dropping problematic tables...');
            
            foreach ($tablesToDrop as $table) {
                if (Schema::hasTable($table)) {
                    $this->line("- Dropping table: {$table}");
                    Schema::dropIfExists($table);
                    $this->info("  ✓ {$table} dropped");
                } else {
                    $this->line("- Table {$table} does not exist");
                }
            }

            // Re-enable foreign key checks
            $this->info('Re-enabling foreign key checks...');
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            $this->info('✓ Foreign key constraint issues resolved');
            $this->info('You can now run your migrations safely: php artisan migrate');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error occurred: ' . $e->getMessage());
            
            // Try to re-enable foreign key checks even on error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Exception $fkError) {
                $this->error('Failed to re-enable foreign key checks: ' . $fkError->getMessage());
            }
            
            return 1;
        }
    }
}