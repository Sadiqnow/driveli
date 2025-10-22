<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModeratorActionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('moderator_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('moderator_id');
            $table->string('action_type')->index(); // 'approve', 'reject', 'suspend', 'reinstate', 'flag', 'review'
            $table->string('resource_type')->index(); // 'driver', 'document', 'verification', 'profile'
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->json('action_data')->nullable(); // Store action-specific data
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_until')->nullable();
            $table->boolean('is_reversible')->default(true);
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('moderator_id')->references('id')->on('admin_users');
            $table->foreign('reversed_by')->references('id')->on('admin_users');

            $table->index(['driver_id', 'action_type']);
            $table->index(['moderator_id', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
            $table->index('effective_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderator_actions');
    }
}
