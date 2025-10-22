<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverVerificationLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('driver_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('action')->index(); // 'ocr_verification', 'facial_verification', 'document_verification', 'manual_review'
            $table->string('status')->index(); // 'started', 'completed', 'failed', 'pending'
            $table->json('verification_data')->nullable();
            $table->json('result_data')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('performed_at')->nullable();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('admin_users');

            $table->index(['driver_id', 'action']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_verification_logs');
    }
}
