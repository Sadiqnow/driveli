<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Only proceed if both tables exist
        if (!Schema::hasTable('drivers') || !Schema::hasTable('drivers_normalized')) {
            return;
        }

        echo "Starting data migration from drivers to drivers_normalized...\n";

        // Get total count for progress tracking
        $totalRecords = DB::table('drivers')->count();
        echo "Migrating {$totalRecords} driver records...\n";

        // Migrate data in chunks to avoid memory issues
        DB::table('drivers')->orderBy('id')->chunk(100, function ($drivers) {
            foreach ($drivers as $driver) {
                try {
                    DB::table('drivers_normalized')->insert([
                        // Core Identification
                        'id' => $driver->id,
                        'driver_id' => $driver->driver_id,
                        'first_name' => $driver->first_name,
                        'middle_name' => $driver->middle_name,
                        'surname' => $driver->surname ?? $driver->last_name ?? '',
                        'email' => $driver->email,
                        'phone' => $driver->phone,
                        'phone_2' => $driver->phone_2,

                        // Authentication
                        'password' => $driver->password,
                        'remember_token' => $driver->remember_token,
                        'email_verified_at' => $driver->email_verified_at,
                        'phone_verified_at' => $driver->phone_verified_at,
                        'phone_verification_status' => $driver->phone_verification_status,
                        'email_verification_status' => $driver->email_verification_status,

                        // Personal Information
                        'date_of_birth' => $driver->date_of_birth,
                        'gender' => $driver->gender,
                        'religion' => $driver->religion,
                        'blood_group' => $driver->blood_group,
                        'height_meters' => $driver->height_meters,
                        'disability_status' => $driver->disability_status,
                        'nationality_id' => $driver->nationality_id,

                        // Professional Information
                        'current_employer' => $driver->current_employer,
                        'years_of_experience' => $driver->years_of_experience ?? (isset($driver->experience_years) ? $driver->experience_years : null),
                        'employment_start_date' => $driver->employment_start_date,
                        'is_working' => $driver->is_working,
                        'previous_company' => $driver->previous_company ?? $driver->previous_workplace,
                        'reason_stopped_working' => $driver->reason_stopped_working,
                        'license_number' => $driver->license_number,
                        'license_class' => $driver->license_class,
                        'license_issue_date' => $driver->license_issue_date,
                        'license_expiry_date' => $driver->license_expiry_date,

                        // Vehicle Information
                        'has_vehicle' => $driver->has_vehicle,
                        'vehicle_type' => $driver->vehicle_type,
                        'vehicle_year' => $driver->vehicle_year,

                        // Banking Information
                        'bank_id' => $driver->bank_id,
                        'account_number' => $driver->account_number,
                        'account_name' => $driver->account_name,
                        'bvn_number' => $driver->bvn_number ?? $driver->bvn,

                        // Work Preferences
                        'preferred_work_location' => $driver->preferred_work_location,
                        'available_for_night_shifts' => $driver->available_for_night_shifts,
                        'available_for_weekend_work' => $driver->available_for_weekend_work,

                        // Emergency Contact
                        'emergency_contact_name' => $driver->emergency_contact_name,
                        'emergency_contact_phone' => $driver->emergency_contact_phone,
                        'emergency_contact_relationship' => $driver->emergency_contact_relationship,

                        // System Status
                        'status' => $driver->status,
                        'verification_status' => $driver->verification_status,
                        'is_active' => $driver->is_active,
                        'is_available' => $driver->is_available ?? $driver->available,
                        'registered_at' => $driver->registered_at ?? $driver->registration_date,
                        'last_active_at' => $driver->last_active_at,
                        'verified_at' => $driver->verified_at,
                        'verified_by' => $driver->verified_by,
                        'verification_notes' => $driver->verification_notes,

                        // KYC Fields
                        'kyc_status' => $driver->kyc_status,
                        'kyc_step' => $driver->kyc_step,
                        'kyc_step_data' => $driver->kyc_step_data,
                        'kyc_step_1_completed_at' => $driver->kyc_step_1_completed_at,
                        'kyc_step_2_completed_at' => $driver->kyc_step_2_completed_at,
                        'kyc_step_3_completed_at' => $driver->kyc_step_3_completed_at,
                        'kyc_completed_at' => $driver->kyc_completed_at,
                        'kyc_submitted_at' => $driver->kyc_submitted_at,
                        'kyc_reviewed_at' => $driver->kyc_reviewed_at,
                        'kyc_reviewed_by' => $driver->kyc_reviewed_by,
                        'kyc_retry_count' => $driver->kyc_retry_count,
                        'kyc_rejection_reason' => $driver->kyc_rejection_reason,
                        'kyc_submission_ip' => $driver->kyc_submission_ip,
                        'kyc_user_agent' => $driver->kyc_user_agent,
                        'kyc_last_activity_at' => $driver->kyc_last_activity_at,

                        // OCR Verification
                        'ocr_verification_status' => $driver->ocr_verification_status,
                        'ocr_verification_notes' => $driver->ocr_verification_notes,
                        'nin_verification_data' => $driver->nin_verification_data,
                        'nin_verified_at' => isset($driver->nin_verified_at) ? $driver->nin_verified_at : null,
                        'nin_ocr_match_score' => isset($driver->nin_ocr_match_score) ? $driver->nin_ocr_match_score : null,
                        'frsc_verification_data' => isset($driver->frsc_verification_data) ? $driver->frsc_verification_data : null,
                        'frsc_verified_at' => isset($driver->frsc_verified_at) ? $driver->frsc_verified_at : null,
                        'frsc_ocr_match_score' => isset($driver->frsc_ocr_match_score) ? $driver->frsc_ocr_match_score : null,

                        // Profile and Completion
                        'profile_picture' => $driver->profile_picture ?? $driver->profile_photo,
                        'passport_photograph' => $driver->passport_photograph ?? $driver->passport_photo,
                        'nin_number' => $driver->nin_number,
                        'profile_completion_percentage' => $driver->profile_completion_percentage,
                        'registration_source' => $driver->registration_source,
                        'registration_ip' => $driver->registration_ip,

                        // Audit Fields
                        'created_by_admin_id' => $driver->created_by_admin_id,

                        // Timestamps
                        'created_at' => $driver->created_at,
                        'updated_at' => $driver->updated_at,
                        'deleted_at' => $driver->deleted_at,
                    ]);
                } catch (\Exception $e) {
                    echo "Failed to migrate driver ID {$driver->id}: " . $e->getMessage() . "\n";
                    // Continue with next record
                }
            }
        });

        echo "Data migration completed successfully!\n";
    }

    public function down(): void
    {
        // Truncate the normalized table
        if (Schema::hasTable('drivers_normalized')) {
            DB::table('drivers_normalized')->truncate();
            echo "Normalized drivers table truncated.\n";
        }
    }
};
