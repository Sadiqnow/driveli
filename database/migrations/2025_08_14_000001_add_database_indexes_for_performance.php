<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DISABLED: This migration conflicts with existing indexes
        // The drivers table already has the necessary indexes
        return;
        
        // Add indexes for drivers table
        Schema::table('drivers', function (Blueprint $table) {
            // Check if indexes don't already exist before creating them
            if (!collect(DB::select("SHOW INDEX FROM drivers WHERE Key_name = 'idx_drivers_status_verification'"))->count()) {
                $table->index(['status', 'verification_status'], 'idx_drivers_status_verification');
            }
            if (!collect(DB::select("SHOW INDEX FROM drivers WHERE Key_name = 'idx_drivers_status_active'"))->count()) {
                $table->index(['status', 'is_active'], 'idx_drivers_status_active');
            }
            if (!collect(DB::select("SHOW INDEX FROM drivers WHERE Key_name = 'idx_drivers_verification_date'"))->count()) {
                $table->index(['verification_status', 'verified_at'], 'idx_drivers_verification_date');
            }
            
            // Search indexes
            $table->index(['email'], 'idx_drivers_email');
            $table->index(['phone'], 'idx_drivers_phone');
            $table->index(['nin_number'], 'idx_drivers_nin');
            $table->index(['license_number'], 'idx_drivers_license');
            $table->index(['driver_id'], 'idx_drivers_driver_id');
            
            // Filtering indexes
            $table->index(['nationality_id'], 'idx_drivers_nationality');
            $table->index(['gender'], 'idx_drivers_gender');
            $table->index(['date_of_birth'], 'idx_drivers_dob');
            $table->index(['verified_by'], 'idx_drivers_verified_by');
            
            // Timestamp indexes
            $table->index(['created_at'], 'idx_drivers_created');
            $table->index(['last_active_at'], 'idx_drivers_last_active');
            
            // Composite indexes for common queries
            $table->index(['verification_status', 'created_at'], 'idx_drivers_verification_created');
            $table->index(['status', 'verification_status', 'is_active'], 'idx_drivers_full_status');
        });

        // Add indexes for admin_users table
        Schema::table('admin_users', function (Blueprint $table) {
            $table->index(['status'], 'idx_admin_status');
            $table->index(['role'], 'idx_admin_role');
            $table->index(['email'], 'idx_admin_email');
            $table->index(['last_login_at'], 'idx_admin_last_login');
            $table->index(['status', 'role'], 'idx_admin_status_role');
        });

        // Add indexes for companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->index(['status'], 'idx_companies_status');
            $table->index(['verification_status'], 'idx_companies_verification');
            $table->index(['industry'], 'idx_companies_industry');
            $table->index(['state'], 'idx_companies_state');
            $table->index(['company_size'], 'idx_companies_size');
            $table->index(['registration_number'], 'idx_companies_reg_number');
            $table->index(['email'], 'idx_companies_email');
            $table->index(['phone'], 'idx_companies_phone');
            $table->index(['company_id'], 'idx_companies_company_id');
            $table->index(['status', 'verification_status'], 'idx_companies_status_verification');
            $table->index(['created_at'], 'idx_companies_created');
        });

        // Add indexes for company_requests table
        Schema::table('company_requests', function (Blueprint $table) {
            $table->index(['company_id'], 'idx_requests_company');
            $table->index(['driver_id'], 'idx_requests_driver');
            $table->index(['status'], 'idx_requests_status');
            $table->index(['created_at'], 'idx_requests_created');
            $table->index(['expires_at'], 'idx_requests_expires');
            $table->index(['company_id', 'status'], 'idx_requests_company_status');
        });

        // Add indexes for driver_locations table
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_locations_driver');
            $table->index(['location_type'], 'idx_locations_type');
            $table->index(['state_id'], 'idx_locations_state');
            $table->index(['lga_id'], 'idx_locations_lga');
            $table->index(['is_primary'], 'idx_locations_primary');
            $table->index(['driver_id', 'location_type', 'is_primary'], 'idx_locations_driver_type_primary');
        });

        // Add indexes for driver_documents table
        Schema::table('driver_documents', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_documents_driver');
            $table->index(['document_type'], 'idx_documents_type');
            $table->index(['status'], 'idx_documents_status');
            $table->index(['uploaded_at'], 'idx_documents_uploaded');
            $table->index(['driver_id', 'document_type'], 'idx_documents_driver_type');
            $table->index(['status', 'uploaded_at'], 'idx_documents_status_uploaded');
        });

        // Add indexes for driver_banking_details table
        Schema::table('driver_banking_details', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_banking_driver');
            $table->index(['bank_id'], 'idx_banking_bank');
            $table->index(['is_primary'], 'idx_banking_primary');
            $table->index(['is_verified'], 'idx_banking_verified');
            $table->index(['account_number'], 'idx_banking_account');
            $table->index(['driver_id', 'is_primary'], 'idx_banking_driver_primary');
        });

        // Add indexes for driver_next_of_kin table
        Schema::table('driver_next_of_kin', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_nok_driver');
            $table->index(['is_primary'], 'idx_nok_primary');
            $table->index(['driver_id', 'is_primary'], 'idx_nok_driver_primary');
        });

        // Add indexes for driver_employment_history table
        Schema::table('driver_employment_history', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_employment_driver');
            $table->index(['is_current'], 'idx_employment_current');
            $table->index(['start_date'], 'idx_employment_start');
            $table->index(['end_date'], 'idx_employment_end');
            $table->index(['driver_id', 'is_current'], 'idx_employment_driver_current');
        });

        // Add indexes for driver_performance table
        Schema::table('driver_performance', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_performance_driver');
            $table->index(['average_rating'], 'idx_performance_rating');
            $table->index(['total_jobs_completed'], 'idx_performance_jobs');
            $table->index(['completion_rate'], 'idx_performance_completion');
        });

        // Add indexes for driver_preferences table
        Schema::table('driver_preferences', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_preferences_driver');
            $table->index(['vehicle_type_preference'], 'idx_preferences_vehicle');
            $table->index(['work_schedule_preference'], 'idx_preferences_schedule');
        });

        // Add indexes for driver_matches table
        Schema::table('driver_matches', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_matches_driver');
            $table->index(['company_request_id'], 'idx_matches_request');
            $table->index(['status'], 'idx_matches_status');
            $table->index(['created_at'], 'idx_matches_created');
            $table->index(['driver_id', 'status'], 'idx_matches_driver_status');
            $table->index(['company_request_id', 'status'], 'idx_matches_request_status');
        });

        // Add indexes for commissions table
        Schema::table('commissions', function (Blueprint $table) {
            $table->index(['driver_id'], 'idx_commissions_driver');
            $table->index(['company_id'], 'idx_commissions_company');
            $table->index(['status'], 'idx_commissions_status');
            $table->index(['created_at'], 'idx_commissions_created');
            $table->index(['due_date'], 'idx_commissions_due');
            $table->index(['paid_at'], 'idx_commissions_paid');
        });

        // Add indexes for lookup tables
        Schema::table('nationalities', function (Blueprint $table) {
            $table->index(['is_active'], 'idx_nationalities_active');
            $table->index(['code'], 'idx_nationalities_code');
        });

        Schema::table('states', function (Blueprint $table) {
            $table->index(['country_id'], 'idx_states_country');
            $table->index(['is_active'], 'idx_states_active');
        });

        Schema::table('local_governments', function (Blueprint $table) {
            $table->index(['state_id'], 'idx_lgas_state');
            $table->index(['is_active'], 'idx_lgas_active');
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->index(['is_active'], 'idx_banks_active');
            $table->index(['code'], 'idx_banks_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes for drivers table
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropIndex('idx_drivers_status_verification');
            $table->dropIndex('idx_drivers_status_active');
            $table->dropIndex('idx_drivers_verification_date');
            $table->dropIndex('idx_drivers_email');
            $table->dropIndex('idx_drivers_phone');
            $table->dropIndex('idx_drivers_nin');
            $table->dropIndex('idx_drivers_license');
            $table->dropIndex('idx_drivers_driver_id');
            $table->dropIndex('idx_drivers_nationality');
            $table->dropIndex('idx_drivers_gender');
            $table->dropIndex('idx_drivers_dob');
            $table->dropIndex('idx_drivers_verified_by');
            $table->dropIndex('idx_drivers_created');
            $table->dropIndex('idx_drivers_last_active');
            $table->dropIndex('idx_drivers_verification_created');
            $table->dropIndex('idx_drivers_full_status');
        });

        // Drop indexes for other tables
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropIndex('idx_admin_status');
            $table->dropIndex('idx_admin_role');
            $table->dropIndex('idx_admin_email');
            $table->dropIndex('idx_admin_last_login');
            $table->dropIndex('idx_admin_status_role');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex('idx_companies_status');
            $table->dropIndex('idx_companies_verification');
            $table->dropIndex('idx_companies_industry');
            $table->dropIndex('idx_companies_state');
            $table->dropIndex('idx_companies_size');
            $table->dropIndex('idx_companies_reg_number');
            $table->dropIndex('idx_companies_email');
            $table->dropIndex('idx_companies_phone');
            $table->dropIndex('idx_companies_company_id');
            $table->dropIndex('idx_companies_status_verification');
            $table->dropIndex('idx_companies_created');
        });

        // Continue with other tables...
        // (Additional drop statements for all other indexes)
    }
};