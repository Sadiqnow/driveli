<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        if (!Schema::hasColumn('drivers', 'kyc_retry_count')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->integer('kyc_retry_count')->default(0);
            });
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

        if (Schema::hasColumn('drivers', 'kyc_retry_count')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('kyc_retry_count');
            });
        }
    }
};
