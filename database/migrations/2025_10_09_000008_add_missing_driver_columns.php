<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingDriverColumns extends Migration
{
    /**
     * Run the migrations.
     * Idempotent: only adds columns if they don't already exist.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'gender')) {
                $table->string('gender', 20)->nullable()->after('date_of_birth');
            }

            if (!Schema::hasColumn('drivers', 'religion')) {
                $table->string('religion', 100)->nullable()->after('gender');
            }

            if (!Schema::hasColumn('drivers', 'blood_group')) {
                $table->string('blood_group', 5)->nullable()->after('religion');
            }

            if (!Schema::hasColumn('drivers', 'height_meters')) {
                $table->decimal('height_meters', 4, 2)->nullable()->after('blood_group');
            }

            if (!Schema::hasColumn('drivers', 'disability_status')) {
                $table->string('disability_status', 100)->nullable()->after('height_meters');
            }

            if (!Schema::hasColumn('drivers', 'last_active_at')) {
                $table->dateTime('last_active_at')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Drops only if columns exist and table exists.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'last_active_at')) {
                $table->dropColumn('last_active_at');
            }

            $cols = ['disability_status', 'height_meters', 'blood_group', 'religion', 'gender'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
