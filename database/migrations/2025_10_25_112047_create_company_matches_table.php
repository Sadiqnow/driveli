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
        Schema::create('company_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_request_id');
            $table->unsignedBigInteger('driver_id');
            $table->decimal('match_score', 5, 2)->default(0);
            $table->json('matching_criteria')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('agreed_rate', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('matched_by')->nullable();
            $table->timestamps();

            $table->foreign('company_request_id')->references('id')->on('company_requests')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('matched_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->index(['company_request_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index('match_score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_matches');
    }
};
