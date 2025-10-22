<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create matching_criteria table
        Schema::create('matching_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('criteria_type', 50); // location, experience, vehicle, etc.
            $table->decimal('weight', 5, 2)->default(1.00); // Weight in matching algorithm
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable(); // Additional configuration for the criteria
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('admin_users')->onDelete('cascade');
            $table->index(['is_active', 'criteria_type']);
            $table->index('name');
        });

        // Create matching_logs table
        Schema::create('matching_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_request_id');
            $table->unsignedBigInteger('driver_id');
            $table->string('algorithm_version', 20)->default('v1.0');
            $table->json('criteria_scores')->nullable(); // Scores for each criteria
            $table->decimal('final_score', 5, 2)->nullable();
            $table->boolean('matched')->default(false);
            $table->text('reason')->nullable(); // Why this driver was matched or not
            $table->decimal('execution_time', 8, 3)->nullable(); // Execution time in seconds
            $table->timestamps();

            $table->foreign('company_request_id')->references('id')->on('company_requests')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers_normalized')->onDelete('cascade');
            $table->index(['company_request_id', 'final_score']);
            $table->index(['driver_id', 'matched']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matching_logs');
        Schema::dropIfExists('matching_criteria');
    }
};
