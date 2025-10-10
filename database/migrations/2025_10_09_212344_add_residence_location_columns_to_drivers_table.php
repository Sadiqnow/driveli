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
        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'residence_state_id')) {
                $table->unsignedBigInteger('residence_state_id')->nullable()->after('residential_address');
            }
            if (!Schema::hasColumn('drivers', 'residence_lga_id')) {
                $table->unsignedBigInteger('residence_lga_id')->nullable()->after('residence_state_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'residence_state_id')) {
                $table->dropColumn('residence_state_id');
            }
            if (Schema::hasColumn('drivers', 'residence_lga_id')) {
                $table->dropColumn('residence_lga_id');
            }
        });
    }
};
