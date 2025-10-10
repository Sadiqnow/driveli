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
        if (!Schema::hasTable('driver_matches')) {
            Schema::create('driver_matches', function (Blueprint $table) {
            $table->id();
            $table->string('match_id')->unique();
            $table->unsignedBigInteger('company_request_id');
            $table->unsignedBigInteger('driver_id');
            $table->enum('status', ['pending', 'accepted', 'declined', 'completed', 'cancelled'])->default('pending');
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->timestamp('matched_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('matched_by_admin')->nullable();
            $table->boolean('auto_matched')->default(false);
            $table->decimal('driver_rating', 2, 1)->nullable();
            $table->decimal('company_rating', 2, 1)->nullable();
            $table->text('driver_feedback')->nullable();
            $table->text('company_feedback')->nullable();
            $table->text('notes')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            // Note: foreign key constraints are added after table creation in a defensive manner

            // Indexes
            $table->index(['status']);
            $table->index(['driver_id', 'status']);
            $table->index(['company_request_id', 'status']);
            $table->index(['matched_at']);
            });
        }

        // Add foreign keys defensively (only if referenced tables exist)
        if (Schema::hasTable('company_requests') && Schema::hasTable('driver_matches')) {
            try {
                Schema::table('driver_matches', function (Blueprint $table) {
                    if (Schema::hasColumn('driver_matches', 'company_request_id') && Schema::hasTable('company_requests')) {
                        $table->foreign('company_request_id')->references('id')->on('company_requests')->onDelete('cascade');
                    }
                });
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('drivers') && Schema::hasTable('driver_matches')) {
            try {
                Schema::table('driver_matches', function (Blueprint $table) {
                    if (Schema::hasColumn('driver_matches', 'driver_id') && Schema::hasTable('drivers')) {
                        $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
                    }
                });
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('admin_users') && Schema::hasTable('driver_matches')) {
            try {
                Schema::table('driver_matches', function (Blueprint $table) {
                    if (Schema::hasColumn('driver_matches', 'matched_by_admin') && Schema::hasTable('admin_users')) {
                        $table->foreign('matched_by_admin')->references('id')->on('admin_users')->onDelete('set null');
                    }
                });
            } catch (\Exception $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_matches');
    }
};