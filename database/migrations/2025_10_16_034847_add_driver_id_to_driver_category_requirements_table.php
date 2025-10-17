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
        Schema::table('driver_category_requirements', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->nullable()->after('id');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->index(['driver_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_category_requirements', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropIndex(['driver_id', 'category']);
            $table->dropColumn('driver_id');
        });
    }
};
