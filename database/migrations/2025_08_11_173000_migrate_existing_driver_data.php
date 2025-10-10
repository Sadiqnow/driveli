<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration will transfer data from the old drivers table to the new normalized structure
        // First, let's check if the old drivers table exists and has data
        
        if (Schema::hasTable('drivers') && DB::table('drivers')->count() > 0) {
            $this->info('Migrating existing driver data to normalized structure...');
            
            // Get existing drivers
            $oldDrivers = DB::table('drivers')->get();
            
            foreach ($oldDrivers as $oldDriver) {
                try {
                    // Insert into normalized drivers table
                    $newDriverId = DB::table('drivers')->insertGetId([
                        'driver_id' => $oldDriver->driver_id,
                        'nickname' => $oldDriver->nickname ?? null,
                        'first_name' => $oldDriver->first_name,
                        'middle_name' => $oldDriver->middle_name ?? null,
                        'surname' => $oldDriver->last_name, // Map last_name to surname
                        'phone' => $oldDriver->phone,
                        'phone_2' => $oldDriver->phone_2 ?? null,
                        'email' => $oldDriver->email,
                        'date_of_birth' => $oldDriver->date_of_birth,
                        'gender' => $oldDriver->gender,
                        'religion' => $oldDriver->religion ?? null,
                        'blood_group' => $oldDriver->blood_group ?? null,
                        'height_meters' => $oldDriver->height_meters ?? null,
                        'disability_status' => $oldDriver->disability_status ?? 'None',
                        'nationality_id' => 1, // Default to Nigerian
                        'profile_picture' => $oldDriver->profile_photo ?? null,
                        'nin_number' => $oldDriver->nin,
                        'license_number' => $oldDriver->license_number,
                        'license_class' => $oldDriver->license_class,
                        'status' => $oldDriver->status ?? 'inactive',
                        'verification_status' => $oldDriver->verification_status ?? 'pending',
                        'is_active' => $oldDriver->is_active ?? true,
                        'last_active_at' => $oldDriver->last_active_at,
                        'registered_at' => $oldDriver->joined_at ?? $oldDriver->created_at,
                        'verified_at' => $oldDriver->verified_at,
                        'verified_by' => $oldDriver->verified_by,
                        'verification_notes' => $oldDriver->verification_notes,
                        'rejected_at' => $oldDriver->rejected_at ?? null,
                        'rejection_reason' => $oldDriver->rejection_reason ?? null,
                        'ocr_verification_status' => $oldDriver->ocr_verification_status ?? 'pending',
                        'ocr_verification_notes' => $oldDriver->ocr_verification_notes,
                        'created_at' => $oldDriver->created_at,
                        'updated_at' => $oldDriver->updated_at,
                        'deleted_at' => $oldDriver->deleted_at ?? null,
                    ]);

                    // Migrate locations if address fields exist
                    if (!empty($oldDriver->address)) {
                        // Try to find state and LGA IDs (default to first ones if not found)
                        $stateId = $this->findStateId($oldDriver->state ?? 'Lagos');
                        $lgaId = $this->findLgaId($oldDriver->lga ?? 'Ikeja', $stateId);

                        // Create residence location
                        DB::table('driver_locations')->insert([
                            'driver_id' => $newDriverId,
                            'location_type' => 'residence',
                            'address' => $oldDriver->address,
                            'city' => $oldDriver->city ?? 'Unknown',
                            'state_id' => $stateId,
                            'lga_id' => $lgaId,
                            'is_primary' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Migrate documents
                    $this->migrateDocuments($newDriverId, $oldDriver);

                    // Create performance record
                    DB::table('driver_performance')->insert([
                        'driver_id' => $newDriverId,
                        'current_location_lat' => $oldDriver->current_location_lat ?? null,
                        'current_location_lng' => $oldDriver->current_location_lng ?? null,
                        'current_city' => $oldDriver->current_city ?? null,
                        'total_jobs_completed' => $oldDriver->completed_jobs ?? 0,
                        'average_rating' => $oldDriver->average_rating ?? 0.00,
                        'total_ratings' => $oldDriver->total_ratings ?? 0,
                        'total_earnings' => $oldDriver->total_earnings ?? 0.00,
                        'last_job_completed_at' => $oldDriver->last_job_completed_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Create preferences record
                    DB::table('driver_preferences')->insert([
                        'driver_id' => $newDriverId,
                        'vehicle_types' => $oldDriver->vehicle_types,
                        'experience_level' => $oldDriver->experience_level,
                        'years_of_experience' => $oldDriver->years_of_experience ?? null,
                        'preferred_routes' => $oldDriver->regions ?? null,
                        'special_skills' => $oldDriver->special_skills ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->info("Migrated driver: {$oldDriver->driver_id}");
                    
                } catch (\Exception $e) {
                    $this->error("Failed to migrate driver {$oldDriver->driver_id}: " . $e->getMessage());
                    continue;
                }
            }

            $this->info('Driver data migration completed!');
        } else {
            $this->info('No existing driver data to migrate.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the normalized tables
        DB::table('driver_preferences')->truncate();
        DB::table('driver_performance')->truncate();
        DB::table('driver_documents')->truncate();
        DB::table('driver_locations')->truncate();
        DB::table('drivers')->truncate();
    }

    private function findStateId($stateName)
    {
        $state = DB::table('states')
            ->where('name', 'LIKE', "%{$stateName}%")
            ->first();
            
        return $state ? $state->id : 25; // Default to Lagos
    }

    private function findLgaId($lgaName, $stateId)
    {
        $lga = DB::table('local_governments')
            ->where('state_id', $stateId)
            ->where('name', 'LIKE', "%{$lgaName}%")
            ->first();
            
        return $lga ? $lga->id : DB::table('local_governments')->where('state_id', $stateId)->first()->id;
    }

    private function migrateDocuments($driverId, $oldDriver)
    {
        $documents = [];

        // NIN Document
        if (!empty($oldDriver->nin_document)) {
            $documents[] = [
                'driver_id' => $driverId,
                'document_type' => 'nin',
                'document_path' => $oldDriver->nin_document,
                'document_number' => $oldDriver->nin,
                'verification_status' => $oldDriver->nin_ocr_match_score >= 80 ? 'approved' : 'pending',
                'verified_at' => $oldDriver->nin_verified_at,
                'ocr_data' => $oldDriver->nin_verification_data,
                'ocr_match_score' => $oldDriver->nin_ocr_match_score,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // FRSC License
        if (!empty($oldDriver->frsc_document)) {
            $documents[] = [
                'driver_id' => $driverId,
                'document_type' => 'license_front',
                'document_path' => $oldDriver->frsc_document,
                'document_number' => $oldDriver->license_number,
                'issue_date' => $oldDriver->license_current_issue_date ?? null,
                'expiry_date' => $oldDriver->license_expiry_date,
                'verification_status' => $oldDriver->frsc_ocr_match_score >= 80 ? 'approved' : 'pending',
                'verified_at' => $oldDriver->frsc_verified_at,
                'ocr_data' => $oldDriver->frsc_verification_data,
                'ocr_match_score' => $oldDriver->frsc_ocr_match_score,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Profile Picture
        if (!empty($oldDriver->profile_photo)) {
            $documents[] = [
                'driver_id' => $driverId,
                'document_type' => 'profile_picture',
                'document_path' => $oldDriver->profile_photo,
                'verification_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // License Images
        if (!empty($oldDriver->license_front_image)) {
            $documents[] = [
                'driver_id' => $driverId,
                'document_type' => 'license_front',
                'document_path' => $oldDriver->license_front_image,
                'document_number' => $oldDriver->license_number,
                'verification_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($oldDriver->license_back_image)) {
            $documents[] = [
                'driver_id' => $driverId,
                'document_type' => 'license_back',
                'document_path' => $oldDriver->license_back_image,
                'verification_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Passport Photo
        if (!empty($oldDriver->passport_photograph)) {
            $documents[] = [
                'driver_id' => $driverId,
                'document_type' => 'passport_photo',
                'document_path' => $oldDriver->passport_photograph,
                'verification_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all documents
        if (!empty($documents)) {
            DB::table('driver_documents')->insert($documents);
        }
    }

    private function info($message)
    {
        echo "[INFO] " . $message . PHP_EOL;
    }

    private function error($message)
    {
        echo "[ERROR] " . $message . PHP_EOL;
    }
};