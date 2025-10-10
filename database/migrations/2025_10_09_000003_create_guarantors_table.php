<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('guarantors')) {
            Schema::create('guarantors', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('driver_id')->index();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('relationship')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('guarantors')) {
            Schema::dropIfExists('guarantors');
        }
    }
};
