<?php

use Illuminate\Database\Migrations\Migration;
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
        // Use raw ALTER TABLE statements to avoid requiring doctrine/dbal
        $cols = ['phone', 'nin_number', 'emergency_contact_phone', 'account_number', 'bvn'];

        foreach ($cols as $col) {
            try {
                // Only attempt if column exists
                $exists = Schema::hasColumn('drivers', $col);
                if ($exists) {
                    DB::statement("ALTER TABLE `drivers` MODIFY `{$col}` TEXT NULL");
                }
            } catch (\Exception $e) {
                // Log and continue - migration should be idempotent/safe
                \Illuminate\Support\Facades\Log::warning('Could not modify drivers.' . $col . ': ' . $e->getMessage());
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
        // Attempt to revert to varchar(255) where possible
        $cols = ['phone', 'nin_number', 'emergency_contact_phone', 'account_number', 'bvn'];

        foreach ($cols as $col) {
            try {
                $exists = Schema::hasColumn('drivers', $col);
                if ($exists) {
                    DB::statement("ALTER TABLE `drivers` MODIFY `{$col}` VARCHAR(255) NULL");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not revert drivers.' . $col . ': ' . $e->getMessage());
            }
        }
    }
};
