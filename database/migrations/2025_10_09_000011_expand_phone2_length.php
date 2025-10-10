<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ExpandPhone2Length extends Migration
{
    public function up()
    {
        if (Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'phone_2')) {
            // Use raw SQL to avoid requiring doctrine/dbal for column changes
            try {
                DB::statement('ALTER TABLE `drivers` MODIFY `phone_2` TEXT NULL');
            } catch (\Exception $e) {
                // If the database doesn't support MODIFY or the column is already large enough, ignore
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'phone_2')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->string('phone_2')->nullable()->change();
            });
        }
    }
}
