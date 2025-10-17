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
        Schema::create('superadmin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('superadmin_id');
            $table->string('action'); // create, update, delete, approve, reject, flag, restore, bulk_operation
            $table->string('resource_type'); // driver, user, company, etc.
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('resource_name')->nullable(); // Human readable name
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable(); // Additional context like IP, user agent, etc.
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['superadmin_id', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');

            // Foreign key constraint
            $table->foreign('superadmin_id')->references('id')->on('admin_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('superadmin_activity_logs');
    }
};
