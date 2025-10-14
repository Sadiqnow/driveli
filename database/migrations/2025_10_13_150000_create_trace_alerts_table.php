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
        Schema::create('trace_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('alert_type'); // app_uninstalled, ping_missed, suspicious_activity, etc.
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->json('last_known_location')->nullable(); // GPS coordinates and timestamp
            $table->json('alert_data')->nullable(); // Additional alert context
            $table->enum('status', ['active', 'resolved', 'dismissed'])->default('active');
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable(); // Admin who resolved
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['driver_id', 'status']);
            $table->index(['alert_type', 'severity']);
            $table->index(['status', 'triggered_at']);
            $table->index('resolved_by');

            // Foreign keys
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('admin_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trace_alerts');
    }
};
