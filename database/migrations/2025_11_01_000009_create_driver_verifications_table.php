<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverVerificationsTable extends Migration
{
    public function up()
    {
        Schema::create('driver_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('status')->default('pending');
            $table->json('verification_data')->nullable();
            $table->integer('score')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers');
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_verifications');
    }
}
