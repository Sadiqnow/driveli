<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverNextOfKinTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('driver_next_of_kin')) {
            Schema::create('driver_next_of_kin', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('driver_id')->index();
                $table->string('name')->nullable();
                $table->string('relationship')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('is_primary')->default(false)->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('driver_next_of_kin')) {
            Schema::dropIfExists('driver_next_of_kin');
        }
    }
}
