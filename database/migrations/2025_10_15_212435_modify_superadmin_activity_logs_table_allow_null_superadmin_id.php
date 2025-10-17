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
        Schema::table('superadmin_activity_logs', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['superadmin_id']);

            // Make superadmin_id nullable
            $table->unsignedBigInteger('superadmin_id')->nullable()->change();

            // Re-add the foreign key constraint allowing nulls
            $table->foreign('superadmin_id')->references('id')->on('admin_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('superadmin_activity_logs', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['superadmin_id']);

            // Make superadmin_id not nullable again
            $table->unsignedBigInteger('superadmin_id')->nullable(false)->change();

            // Re-add the original foreign key constraint
            $table->foreign('superadmin_id')->references('id')->on('admin_users')->onDelete('cascade');
        });
    }
};
