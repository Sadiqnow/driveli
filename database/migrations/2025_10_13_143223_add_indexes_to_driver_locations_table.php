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
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->index(['driver_id', 'recorded_at'], 'driver_locations_driver_id_recorded_at_index');
            $table->index(['latitude', 'longitude'], 'driver_locations_coordinates_index');
            $table->index('recorded_at', 'driver_locations_recorded_at_index');
            $table->index('accuracy', 'driver_locations_accuracy_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->dropIndex('driver_locations_driver_id_recorded_at_index');
            $table->dropIndex('driver_locations_coordinates_index');
            $table->dropIndex('driver_locations_recorded_at_index');
            $table->dropIndex('driver_locations_accuracy_index');
        });
    }
};
