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
        Schema::create('permission_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('permission_name');
            $table->enum('result', ['granted', 'denied'])->default('denied');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('route_name')->nullable();
            $table->string('method')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'permission_name']);
            $table->index(['permission_name', 'result']);
            $table->index('created_at');
            $table->index(['route_name', 'result']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_logs');
    }
};
