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
            $table->string('kyc_step')->default('personal_info')->after('kyc_status');
            $table->timestamp('kyc_step_updated_at')->nullable()->after('kyc_step');
            $table->json('kyc_step_data')->nullable()->after('kyc_step_updated_at');
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
            $table->dropColumn(['kyc_step', 'kyc_step_updated_at', 'kyc_step_data']);
        });
    }
};
