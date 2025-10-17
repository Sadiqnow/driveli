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
        Schema::create('driver_management_comparison_report', function (Blueprint $table) {
            $table->id();
            $table->string('report_type')->default('comparison'); // comparison, sync, migration
            $table->json('old_admin_features')->nullable(); // Features from admin portal
            $table->json('old_superadmin_features')->nullable(); // Features from superadmin portal
            $table->json('new_features')->nullable(); // New unified features
            $table->json('resolved_issues')->nullable(); // Issues that were fixed
            $table->json('unchanged_components')->nullable(); // Components that remained the same
            $table->json('rbac_implementation')->nullable(); // RBAC details
            $table->json('performance_metrics')->nullable(); // Performance improvements
            $table->json('security_enhancements')->nullable(); // Security improvements
            $table->json('api_endpoints')->nullable(); // New API endpoints
            $table->json('database_changes')->nullable(); // Database schema changes
            $table->json('ui_ux_improvements')->nullable(); // UI/UX enhancements
            $table->json('testing_results')->nullable(); // Test results
            $table->string('generated_by')->nullable(); // User who generated the report
            $table->timestamp('generated_at')->nullable();
            $table->text('summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_management_comparison_report');
    }
};
