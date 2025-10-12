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
        Schema::create('otp_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->string('otp_code', 10);
            $table->string('type');
            $table->enum('status', ['pending', 'sent', 'verified', 'expired', 'failed'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_type', 'user_id']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otp_notifications');
    }
};
