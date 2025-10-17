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
        // Migrate existing driver data to transactional tables
        // This migration runs after the new columns are added to transactional tables
        // and before the non-core fields are removed from drivers table

        echo "Starting data migration from drivers to transactional tables...\n";

        // Get all drivers with their data
        $drivers = DB::table('drivers')->get();

        foreach ($drivers as $driver) {
            // 1. Migrate personal info to driver_next_of_kin
            if ($driver->date_of_birth || $driver->gender || $driver->religion ||
                $driver->blood_group || $driver->height_meters || $driver->disability_status ||
                $driver->nationality_id || $driver->nin_number) {

                DB::table('driver_next_of_kin')->updateOrInsert(
                    ['driver_id' => $driver->id],
                    [
                        'date_of_birth' => $driver->date_of_birth,
                        'gender' => $driver->gender,
                        'religion' => $driver->religion,
                        'blood_group' => $driver->blood_group,
                        'height_meters' => $driver->height_meters,
                        'disability_status' => $driver->disability_status,
                        'nationality_id' => $driver->nationality_id,
                        'nin_number' => $driver->nin_number,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // 2. Migrate professional info to driver_performance
            if ($driver->current_employer || $driver->years_of_experience ||
                $driver->employment_start_date || $driver->is_working ||
                $driver->previous_company || $driver->reason_stopped_working ||
                $driver->license_number || $driver->license_class ||
                $driver->license_issue_date || $driver->license_expiry_date ||
                $driver->has_vehicle || $driver->vehicle_type || $driver->vehicle_year ||
                $driver->preferred_work_location || $driver->available_for_night_shifts ||
                $driver->available_for_weekend_work) {

                DB::table('driver_performance')->updateOrInsert(
                    ['driver_id' => $driver->id],
                    [
                        'current_employer' => $driver->current_employer,
                        'years_of_experience' => $driver->years_of_experience,
                        'employment_start_date' => $driver->employment_start_date,
                        'is_working' => $driver->is_working,
                        'previous_company' => $driver->previous_company,
                        'reason_stopped_working' => $driver->reason_stopped_working,
                        'license_number' => $driver->license_number,
                        'license_class' => $driver->license_class,
                        'license_issue_date' => $driver->license_issue_date,
                        'license_expiry_date' => $driver->license_expiry_date,
                        'has_vehicle' => $driver->has_vehicle,
                        'vehicle_type' => $driver->vehicle_type,
                        'vehicle_year' => $driver->vehicle_year,
                        'preferred_work_location' => $driver->preferred_work_location,
                        'available_for_night_shifts' => $driver->available_for_night_shifts,
                        'available_for_weekend_work' => $driver->available_for_weekend_work,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // 3. Migrate banking details to driver_banking_details
            if ($driver->bank_id || $driver->account_number || $driver->account_name || $driver->bvn_number) {
                DB::table('driver_banking_details')->updateOrInsert(
                    ['driver_id' => $driver->id],
                    [
                        'bank_id' => $driver->bank_id,
                        'account_name' => $driver->account_name,
                        'account_number' => $driver->account_number,
                        'is_verified' => false, // Default to false, can be updated later
                        'is_primary' => true, // Assume primary for now
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // 4. Migrate emergency contact to driver_next_of_kin (if not already migrated)
            if ($driver->emergency_contact_name || $driver->emergency_contact_phone || $driver->emergency_contact_relationship) {
                DB::table('driver_next_of_kin')->updateOrInsert(
                    ['driver_id' => $driver->id],
                    [
                        'name' => $driver->emergency_contact_name,
                        'phone' => $driver->emergency_contact_phone,
                        'relationship' => $driver->emergency_contact_relationship,
                        'is_primary' => true, // Emergency contact is typically primary
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // 5. Migrate KYC and OCR data to driver_documents
            $kycData = [];
            if (isset($driver->kyc_step)) $kycData['kyc_step'] = $driver->kyc_step;
            if (isset($driver->kyc_step_data)) $kycData['kyc_step_data'] = $driver->kyc_step_data;
            if (isset($driver->kyc_step_1_completed_at)) $kycData['kyc_step_1_completed_at'] = $driver->kyc_step_1_completed_at;
            if (isset($driver->kyc_step_2_completed_at)) $kycData['kyc_step_2_completed_at'] = $driver->kyc_step_2_completed_at;
            if (isset($driver->kyc_step_3_completed_at)) $kycData['kyc_step_3_completed_at'] = $driver->kyc_step_3_completed_at;
            if (isset($driver->kyc_completed_at)) $kycData['kyc_completed_at'] = $driver->kyc_completed_at;
            if (isset($driver->kyc_submitted_at)) $kycData['kyc_submitted_at'] = $driver->kyc_submitted_at;
            if (isset($driver->kyc_reviewed_at)) $kycData['kyc_reviewed_at'] = $driver->kyc_reviewed_at;
            if (isset($driver->kyc_reviewed_by)) $kycData['kyc_reviewed_by'] = $driver->kyc_reviewed_by;

            // Check for OCR fields only if they exist
            if (Schema::hasColumn('drivers', 'ocr_verification_status') && isset($driver->ocr_verification_status)) {
                $kycData['ocr_verification_status'] = $driver->ocr_verification_status;
            }
            if (Schema::hasColumn('drivers', 'ocr_verification_notes') && isset($driver->ocr_verification_notes)) {
                $kycData['ocr_verification_notes'] = $driver->ocr_verification_notes;
            }
            if (Schema::hasColumn('drivers', 'nin_verification_data') && isset($driver->nin_verification_data)) {
                $kycData['nin_verification_data'] = $driver->nin_verification_data;
            }
            if (Schema::hasColumn('drivers', 'nin_verified_at') && isset($driver->nin_verified_at)) {
                $kycData['nin_verified_at'] = $driver->nin_verified_at;
            }
            if (Schema::hasColumn('drivers', 'nin_ocr_match_score') && isset($driver->nin_ocr_match_score)) {
                $kycData['nin_ocr_match_score'] = $driver->nin_ocr_match_score;
            }
            if (Schema::hasColumn('drivers', 'frsc_verification_data') && isset($driver->frsc_verification_data)) {
                $kycData['frsc_verification_data'] = $driver->frsc_verification_data;
            }
            if (Schema::hasColumn('drivers', 'frsc_verified_at') && isset($driver->frsc_verified_at)) {
                $kycData['frsc_verified_at'] = $driver->frsc_verified_at;
            }
            if (Schema::hasColumn('drivers', 'frsc_ocr_match_score') && isset($driver->frsc_ocr_match_score)) {
                $kycData['frsc_ocr_match_score'] = $driver->frsc_ocr_match_score;
            }
            if (Schema::hasColumn('drivers', 'profile_picture') && isset($driver->profile_picture)) {
                $kycData['profile_picture'] = $driver->profile_picture;
            }
            if (Schema::hasColumn('drivers', 'passport_photograph') && isset($driver->passport_photograph)) {
                $kycData['passport_photograph'] = $driver->passport_photograph;
            }
            if (Schema::hasColumn('drivers', 'registered_at') && isset($driver->registered_at)) {
                $kycData['registered_at'] = $driver->registered_at;
            }
            if (Schema::hasColumn('drivers', 'last_active_at') && isset($driver->last_active_at)) {
                $kycData['last_active_at'] = $driver->last_active_at;
            }

            if (!empty($kycData)) {
                $kycData['document_type'] = 'profile'; // Default document type
                $kycData['document_path'] = $driver->profile_picture ?? null;
                $kycData['verification_status'] = ($driver->kyc_status ?? null) === 'completed' ? 'approved' : 'pending';
                $kycData['verified_at'] = $driver->kyc_completed_at ?? null;
                $kycData['created_at'] = now();
                $kycData['updated_at'] = now();

                DB::table('driver_documents')->updateOrInsert(
                    ['driver_id' => $driver->id],
                    $kycData
                );
            }
        }

        echo "Data migration completed successfully!\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Truncate transactional tables to remove migrated data
        DB::table('driver_next_of_kin')->truncate();
        DB::table('driver_performance')->truncate();
        DB::table('driver_banking_details')->truncate();
        DB::table('driver_documents')->truncate();

        echo "Transactional tables truncated.\n";
    }
};
