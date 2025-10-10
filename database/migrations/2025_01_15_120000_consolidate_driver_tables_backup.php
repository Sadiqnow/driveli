<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Create backup of existing data before consolidation
     */
    public function up(): void
    {
        // Create backup table for old drivers data
        if (Schema::hasTable('drivers')) {
            Schema::create('drivers_backup_' . date('Y_m_d'), function (Blueprint $table) {
                $table->id();
                $table->json('original_data');
                $table->string('source_table');
                $table->timestamp('backed_up_at');
            });

            // Backup all drivers data
            $drivers = DB::table('drivers')->get();
            $backupData = [];
            
            foreach ($drivers as $driver) {
                $backupData[] = [
                    'original_data' => json_encode($driver),
                    'source_table' => 'drivers',
                    'backed_up_at' => now(),
                ];
            }

            if (!empty($backupData)) {
                DB::table('drivers_backup_' . date('Y_m_d'))->insert($backupData);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers_backup_' . date('Y_m_d'));
    }
};