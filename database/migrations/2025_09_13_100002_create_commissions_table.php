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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->string('commission_id')->unique();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('driver_match_id');
            $table->unsignedBigInteger('company_request_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('rate', 5, 2);
            $table->decimal('base_amount', 10, 2)->nullable();
            $table->enum('status', ['pending', 'unpaid', 'paid', 'disputed', 'resolved', 'refunded'])->default('pending');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('disputed_at')->nullable();
            $table->text('dispute_reason')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('driver_match_id')->references('id')->on('driver_matches')->onDelete('cascade');
            $table->foreign('company_request_id')->references('id')->on('company_requests')->onDelete('cascade');

            // Indexes
            $table->index(['status', 'due_date']);
            $table->index(['driver_id', 'status']);
            $table->index(['driver_match_id']);
            $table->index(['company_request_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};