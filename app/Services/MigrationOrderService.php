<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationOrderService
{
    /**
     * Get the proper migration order based on foreign key dependencies
     */
    public static function getMigrationOrder(): array
    {
        return [
            // Base tables first (no dependencies)
            'admin_users',
            'nationalities',
            'states',  
            'local_governments',
            'users',
            'password_resets',
            'failed_jobs',
            'personal_access_tokens',
            'companies',
            
            // Tables with minimal dependencies
            'company_requests',
            
            // Main drivers table (depends on admin_users, nationalities)
            'drivers',
            
            // Tables that depend on drivers
            'driver_matches',
            'commission',
            
            // Additional driver-related tables
            'driver_banking_details',
            'driver_employment_history',
            'driver_locations',
            'driver_next_of_kins',
            'driver_performances',
            'driver_preferences',
            'driver_referees',
            'guarantors',
        ];
    }

    /**
     * Disable foreign key checks temporarily
     */
    public static function disableForeignKeyChecks(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    }

    /**
     * Enable foreign key checks
     */
    public static function enableForeignKeyChecks(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Drop a table safely with foreign key handling
     */
    public static function safeDropTable(string $tableName): bool
    {
        try {
            self::disableForeignKeyChecks();
            Schema::dropIfExists($tableName);
            self::enableForeignKeyChecks();
            return true;
        } catch (\Exception $e) {
            self::enableForeignKeyChecks();
            throw $e;
        }
    }

    /**
     * Get tables that reference the given table
     */
    public static function getReferencingTables(string $tableName): array
    {
        $query = "
            SELECT 
                TABLE_NAME as table_name,
                COLUMN_NAME as column_name,
                CONSTRAINT_NAME as constraint_name
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE 
                REFERENCED_TABLE_NAME = ? 
                AND TABLE_SCHEMA = DATABASE()
        ";
        
        return DB::select($query, [$tableName]);
    }

    /**
     * Drop foreign key constraints for a table
     */
    public static function dropForeignKeyConstraints(string $tableName): array
    {
        $referencingTables = self::getReferencingTables($tableName);
        $droppedConstraints = [];

        foreach ($referencingTables as $reference) {
            try {
                $sql = "ALTER TABLE `{$reference->table_name}` DROP FOREIGN KEY `{$reference->constraint_name}`";
                DB::statement($sql);
                $droppedConstraints[] = [
                    'table' => $reference->table_name,
                    'constraint' => $reference->constraint_name,
                    'column' => $reference->column_name
                ];
            } catch (\Exception $e) {
                // Constraint might not exist, continue
                continue;
            }
        }

        return $droppedConstraints;
    }

    /**
     * Recreate foreign key constraints
     */
    public static function recreateForeignKeyConstraints(array $constraints): void
    {
        foreach ($constraints as $constraint) {
            try {
                // This is a simplified recreation - in practice, you'd need to store more info
                $sql = "ALTER TABLE `{$constraint['table']}` 
                       ADD CONSTRAINT `{$constraint['constraint']}` 
                       FOREIGN KEY (`{$constraint['column']}`) 
                       REFERENCES `drivers`(`id`) 
                       ON DELETE CASCADE";
                DB::statement($sql);
            } catch (\Exception $e) {
                // Log error but continue
                \Log::warning("Could not recreate constraint: " . $e->getMessage());
            }
        }
    }

    /**
     * Check if all required tables exist before creating foreign keys
     */
    public static function checkTablesExist(array $tableNames): array
    {
        $missing = [];
        foreach ($tableNames as $table) {
            if (!Schema::hasTable($table)) {
                $missing[] = $table;
            }
        }
        return $missing;
    }
}