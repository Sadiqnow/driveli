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
        if (!Schema::hasTable('company_requests')) {
            Schema::create('company_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->string('request_id')->unique();
                $table->string('position_title');
                $table->string('request_type')->default('driver_assignment');
                $table->text('description')->nullable();
                $table->string('location');
                $table->json('requirements')->nullable();
                $table->string('salary_range')->nullable();
                $table->enum('status', ['pending', 'active', 'completed', 'cancelled', 'expired'])->default('pending');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->integer('queue_position')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->text('acceptance_notes')->nullable();
                $table->timestamp('estimated_completion')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->unsignedBigInteger('cancelled_by')->nullable();
                $table->string('cancellation_reason')->nullable();
                $table->text('processing_notes')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->text('completion_notes')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->decimal('rating', 2, 1)->nullable();
                $table->string('pause_reason')->nullable();
                $table->timestamp('paused_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Indexes
                $table->index(['status', 'created_at']);
                $table->index(['company_id', 'status']);
                $table->index(['driver_id', 'status']);
                $table->index(['expires_at']);
                $table->index(['priority', 'status']);
            });
        }

        // Add foreign keys defensively (only if referenced tables/columns exist)
        if (Schema::hasTable('company_requests')) {
            try {
                Schema::table('company_requests', function (Blueprint $table) {
                    if (Schema::hasColumn('company_requests', 'company_id') && Schema::hasTable('companies') && Schema::hasColumn('companies', 'id')) {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                    }
                    if (Schema::hasColumn('company_requests', 'driver_id') && Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'id')) {
                        $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
                    }
                    if (Schema::hasColumn('company_requests', 'created_by') && Schema::hasTable('admin_users') && Schema::hasColumn('admin_users', 'id')) {
                        $table->foreign('created_by')->references('id')->on('admin_users')->onDelete('cascade');
                    }
                    if (Schema::hasColumn('company_requests', 'approved_by') && Schema::hasTable('admin_users') && Schema::hasColumn('admin_users', 'id')) {
                        $table->foreign('approved_by')->references('id')->on('admin_users')->onDelete('set null');
                    }
                    if (Schema::hasColumn('company_requests', 'assigned_to') && Schema::hasTable('admin_users') && Schema::hasColumn('admin_users', 'id')) {
                        $table->foreign('assigned_to')->references('id')->on('admin_users')->onDelete('set null');
                    }
                    if (Schema::hasColumn('company_requests', 'cancelled_by') && Schema::hasTable('admin_users') && Schema::hasColumn('admin_users', 'id')) {
                        $table->foreign('cancelled_by')->references('id')->on('admin_users')->onDelete('set null');
                    }
                });
            } catch (\Exception $e) {
                // ignore; migration will still have created the table
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_requests');
    }
};