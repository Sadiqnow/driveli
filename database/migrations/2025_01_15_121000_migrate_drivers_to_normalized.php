<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate data from drivers table to drivers
     */
    public function up(): void
    {
        if (!Schema::hasTable('drivers') || !Schema::hasTable('drivers')) {
            return;
        }

        // Get all drivers from old table
        $oldDrivers = DB::table('drivers')->get();
        
        foreach ($oldDrivers as $oldDriver) {
            // Check if driver already exists in normalized table
            $existingDriver = DB::table('drivers')
                ->where('driver_id', $oldDriver->driver_id)
                ->orWhere('email', $oldDriver->email)
                ->orWhere('phone', $oldDriver->phone)
                ->first();

            if ($existingDriver) {
                continue; // Skip if already exists
            }

            // Map old driver data to normalized structure
            $normalizedData = [
                'driver_id' => $oldDriver->driver_id,
                'first_name' => $oldDriver->first_name,
                'surname' => $oldDriver->last_name ?? $oldDriver->surname ?? '',
                'middle_name' => null,
                'nickname' => null,
                'phone' => $oldDriver->phone,
                'phone_2' => null,
                'email' => $oldDriver->email,
                'date_of_birth' => $oldDriver->date_of_birth,
                'gender' => $this->mapGender($oldDriver->gender ?? ''),
                'religion' => null,
                'blood_group' => null,
                'height_meters' => null,
                'disability_status' => 'None',
                'nationality_id' => 1, // Default to Nigerian
                'profile_picture' => $oldDriver->profile_photo ?? null,
                
                // NIN Information
                'nin_number' => $oldDriver->nin ?? null,
                
                // License Information
                'license_number' => $oldDriver->license_number ?? null,
                'license_class' => $oldDriver->license_class ?? null,
                
                // System Status - map old status to new
                'status' => $this->mapStatus($oldDriver->status ?? 'Available'),
                'verification_status' => $this->mapVerificationStatus($oldDriver->verification_status ?? 'Pending'),
                'is_active' => true,
                'last_active_at' => $oldDriver->last_login_at ?? null,
                'registered_at' => $oldDriver->created_at ?? now(),
                
                // Verification tracking
                'verified_at' => $oldDriver->verified_at ?? null,
                'verified_by' => $oldDriver->verified_by ?? null,
                'verification_notes' => $oldDriver->verification_notes ?? null,
                
                // OCR Status
                'ocr_verification_status' => $this->mapOcrStatus($oldDriver->nin_verification_status ?? 'Pending'),
                'ocr_verification_notes' => null,
                
                'created_at' => $oldDriver->created_at ?? now(),
                'updated_at' => $oldDriver->updated_at ?? now(),
                'deleted_at' => $oldDriver->deleted_at ?? null,
            ];

            // Insert into normalized table
            DB::table('drivers')->insert($normalizedData);

            // Migrate performance data if exists
            if (isset($oldDriver->rating) || isset($oldDriver->total_jobs) || isset($oldDriver->completed_jobs)) {
                $this->migratePerformanceData($oldDriver);
            }

            // Migrate address data if exists
            if (!empty($oldDriver->address) || !empty($oldDriver->state)) {
                $this->migrateAddressData($oldDriver);
            }
        }
    }

    /**
     * Map old gender values to new enum
     */
    private function mapGender($gender): string
    {
        return match(strtolower($gender)) {
            'male', 'm' => 'Male',
            'female', 'f' => 'Female',
            default => 'Male'
        };
    }

    /**
     * Map old status values to new enum
     */
    private function mapStatus($status): string
    {
        return match($status) {
            'Available', 'Active' => 'active',
            'Not Available', 'Inactive' => 'inactive',
            'Suspended' => 'suspended',
            'Booked' => 'active', // Booked drivers are still active
            default => 'inactive'
        };
    }

    /**
     * Map old verification status to new enum
     */
    private function mapVerificationStatus($status): string
    {
        return match($status) {
            'Verified' => 'verified',
            'Rejected' => 'rejected',
            'Pending' => 'pending',
            default => 'pending'
        };
    }

    /**
     * Map old OCR status to new enum
     */
    private function mapOcrStatus($status): string
    {
        return match($status) {
            'Verified' => 'passed',
            'Rejected' => 'failed',
            'Pending' => 'pending',
            default => 'pending'
        };
    }

    /**
     * Migrate performance data to driver_performance table
     */
    private function migratePerformanceData($oldDriver): void
    {
        if (!Schema::hasTable('driver_performance')) {
            return;
        }

        $normalizedDriver = DB::table('drivers')
            ->where('driver_id', $oldDriver->driver_id)
            ->first();

        if (!$normalizedDriver) {
            return;
        }

        // Check if performance record already exists
        $existingPerformance = DB::table('driver_performance')
            ->where('driver_id', $normalizedDriver->id)
            ->first();

        if ($existingPerformance) {
            return;
        }

        $performanceData = [
            'driver_id' => $normalizedDriver->id,
            'total_jobs_completed' => $oldDriver->completed_jobs ?? 0,
            'average_rating' => $oldDriver->rating ?? 0.00,
            'total_ratings' => $oldDriver->total_jobs ?? 0,
            'total_earnings' => $oldDriver->total_earnings ?? 0.00,
            'last_job_completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('driver_performance')->insert($performanceData);
    }

    /**
     * Migrate address data to driver_locations table
     */
    private function migrateAddressData($oldDriver): void
    {
        if (!Schema::hasTable('driver_locations')) {
            return;
        }

        $normalizedDriver = DB::table('drivers')
            ->where('driver_id', $oldDriver->driver_id)
            ->first();

        if (!$normalizedDriver) {
            return;
        }

        // Find state ID if state name is provided
        $stateId = null;
        if (!empty($oldDriver->state)) {
            $state = DB::table('states')
                ->where('name', 'LIKE', '%' . $oldDriver->state . '%')
                ->first();
            $stateId = $state?->id ?? 1; // Default to first state
        }

        // Find LGA ID if LGA name is provided
        $lgaId = null;
        if (!empty($oldDriver->lga) && $stateId) {
            $lga = DB::table('local_governments')
                ->where('state_id', $stateId)
                ->where('name', 'LIKE', '%' . $oldDriver->lga . '%')
                ->first();
            $lgaId = $lga?->id;
        }

        if (!empty($oldDriver->address) && $stateId) {
            $locationData = [
                'driver_id' => $normalizedDriver->id,
                'location_type' => 'residence',
                'address' => $oldDriver->address,
                'city' => $oldDriver->lga ?? 'Unknown',
                'state_id' => $stateId,
                'lga_id' => $lgaId ?? DB::table('local_governments')->where('state_id', $stateId)->first()?->id,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Check if location already exists
            $existingLocation = DB::table('driver_locations')
                ->where('driver_id', $normalizedDriver->id)
                ->where('location_type', 'residence')
                ->first();

            if (!$existingLocation) {
                DB::table('driver_locations')->insert($locationData);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible as it involves data mapping
        // The backup migration should be used to restore data if needed
    }
};