<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('drivers')) {
            return;
        }

        // Try to alter the column type to VARCHAR to accept values like 'not_started'.
        // Using DB::statement to avoid Doctrine dependency issues.
        try {
            DB::statement("ALTER TABLE `drivers` MODIFY `kyc_step` VARCHAR(50) NOT NULL DEFAULT 'not_started'");
        } catch (\Exception $e) {
            // If modify fails (e.g., column missing or other DB error), try to create the column if it doesn't exist
            if (!Schema::hasColumn('drivers', 'kyc_step')) {
                Schema::table('drivers', function (Blueprint $table) {
                    $table->string('kyc_step', 50)->default('not_started')->after('kyc_status');
                });
            } else {
                // Log the exception for debugging (no logger here to avoid side-effects)
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('drivers')) {
            return;
        }

        // Be conservative: do not attempt to convert back to integer automatically because that could lose data.
        // If you need to revert, please inspect current values and create a custom migration.
    }
};
