<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverPerformanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates a minimal driver_performance table if it does not exist.
     */
    public function up()
    {
        if (!Schema::hasTable('driver_performance')) {
            Schema::create('driver_performance', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('driver_id')->index();
                $table->integer('total_jobs_completed')->default(0);
                $table->decimal('average_rating', 3, 2)->default(0.00);
                $table->decimal('total_earnings', 12, 2)->default(0.00);
                $table->decimal('completion_rate', 5, 2)->default(0.00);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('driver_performance')) {
            Schema::dropIfExists('driver_performance');
        }
    }
}
