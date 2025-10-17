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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type'); // App\Models\AdminUser or App\Models\User
            $table->string('action', 50); // created, updated, deleted, login, etc.
            $table->text('description');
            $table->string('model_type')->nullable(); // App\Models\Driver, etc.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable(); // For tracking changes
            $table->json('new_values')->nullable(); // For tracking changes
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_type', 'user_id']);
            $table->index(['model_type', 'model_id']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
