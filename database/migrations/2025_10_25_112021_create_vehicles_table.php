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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fleet_id');
            $table->string('registration_number')->unique();
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->string('color');
            $table->string('vin')->nullable();
            $table->string('engine_number')->nullable();
            $table->string('chassis_number')->nullable();
            $table->enum('vehicle_type', ['sedan', 'suv', 'truck', 'van', 'motorcycle', 'bus'])->default('sedan');
            $table->integer('seating_capacity');
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('current_value', 12, 2)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->date('road_worthiness_expiry')->nullable();
            $table->integer('mileage')->default(0);
            $table->enum('status', ['active', 'inactive', 'maintenance', 'sold'])->default('active');
            $table->text('notes')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();

            $table->foreign('fleet_id')->references('id')->on('fleets')->onDelete('cascade');
            $table->index(['fleet_id', 'status']);
            $table->index('vehicle_type');
            $table->index('registration_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
