<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\MigrationOrderService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add foreign key constraints only after all tables are created
        
    // Add driver_matches foreign key to drivers if not exists
    if (Schema::hasTable('driver_matches') && Schema::hasTable('drivers')) {
            // Check if foreign key constraint doesn't already exist
            $constraintExists = collect(\DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'driver_matches' 
                AND COLUMN_NAME = 'driver_id' 
                AND REFERENCED_TABLE_NAME = 'drivers'
                AND TABLE_SCHEMA = DATABASE()
            "))->count() > 0;

                if (!$constraintExists) {
                    Schema::table('driver_matches', function (Blueprint $table) {
                        $table->foreign('driver_id')
                            ->references('id')
                            ->on('drivers')
                            ->onDelete('cascade');
                    });
                }
        }

        // Add any other missing foreign key constraints here
        // This ensures proper referential integrity after all tables exist
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints
        if (Schema::hasTable('driver_matches')) {
            Schema::table('driver_matches', function (Blueprint $table) {
                // Find and drop the foreign key constraint
                try {
                        $foreignKeys = \DB::select("
                            SELECT CONSTRAINT_NAME 
                            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'driver_matches' 
                            AND COLUMN_NAME = 'driver_id' 
                            AND REFERENCED_TABLE_NAME = 'drivers'
                            AND TABLE_SCHEMA = DATABASE()
                        ");
                    
                    foreach ($foreignKeys as $fk) {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    }
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
            });
        }
    }
};