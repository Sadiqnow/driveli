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
        Schema::table('local_governments', function (Blueprint $table) {
            $table->string('lga_code', 10)->nullable()->after('name');
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('cascade')->after('lga_code');
            $table->boolean('is_active')->default(true)->after('state_id');
        });

        // Note: LGAs would be populated via seeders due to large volume of data
        // This migration focuses on schema enhancement
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('local_governments', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropColumn(['lga_code', 'state_id', 'is_active']);
        });
    }
};
