<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverFacialVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('driver_facial_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('session_id')->unique();
            $table->string('status')->index(); // 'pending', 'in_progress', 'completed', 'failed', 'expired'
            $table->json('facial_data')->nullable(); // Store facial recognition data
            $table->string('reference_image_path')->nullable();
            $table->string('captured_image_path')->nullable();
            $table->decimal('similarity_score', 5, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->boolean('is_match')->nullable();
            $table->json('verification_metadata')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('admin_users');

            $table->index(['driver_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_facial_verifications');
    }
}
