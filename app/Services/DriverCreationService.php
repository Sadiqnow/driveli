<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\DrivelinkHelper;

/**
 * Service class for handling driver creation operations
 *
 * This service provides methods for creating drivers in different modes:
 * - Simple driver creation (basic account setup)
 * - Comprehensive driver creation (full KYC information)
 * - Unified driver creation (mode-based creation)
 *
 * @package App\Services
 */
class DriverCreationService
{
    /**
     * Create a simple driver account (Step 1 - Basic Account Creation)
     *
     * Creates a driver with minimal required information for account setup.
     * This is typically used for initial registration before KYC completion.
     *
     * @param array $data Driver creation data containing:
     *                   - first_name: string (required)
     *                   - surname: string (required)
     *                   - phone: string (required)
     *                   - email: string (required)
     *                   - password: string (required)
     *                   - driver_license_number: string (required)
     *                   - status: string (optional, default: 'active')
     *
     * @return array Returns an array with 'success', 'driver', and 'message' keys
     *               On success: ['success' => true, 'driver' => Driver, 'message' => string]
     *               On failure: ['success' => false, 'message' => string]
     *
     * @throws \Exception When driver creation fails due to database errors
     *
     * @example
     * ```php
     * $service = new DriverCreationService();
     * $result = $service->createSimpleDriver([
     *     'first_name' => 'John',
     *     'surname' => 'Doe',
     *     'phone' => '+1234567890',
     *     'email' => 'john.doe@example.com',
     *     'password' => 'securepassword',
     *     'driver_license_number' => 'DL123456'
     * ]);
     * ```
     */
    public function createSimpleDriver(array $data): array
    {
        try {
            DB::beginTransaction();

            // Generate driver ID
            $driverId = $this->generateDriverId();

            // Create driver with basic data
            $driver = Driver::create([
                'driver_id' => $driverId,
                'first_name' => $data['first_name'],
                'surname' => $data['surname'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'license_number' => $data['driver_license_number'],
                'status' => $data['status'] ?? 'active',
                'verification_status' => 'pending',
                'kyc_status' => 'pending',
                'created_by_admin_id' => auth('admin')->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info('Simple driver created successfully', [
                'driver_id' => $driver->driver_id,
                'admin_id' => auth('admin')->id()
            ]);

            return [
                'success' => true,
                'driver' => $driver,
                'message' => 'Driver account created successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Simple driver creation failed', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create driver account: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a comprehensive driver with complete KYC information
     *
     * Creates a driver with all available fields including personal details,
     * employment information, vehicle preferences, and uploaded documents.
     * This method is used when all KYC information is collected at once.
     *
     * @param array $data Comprehensive driver data containing all possible fields:
     *                   - first_name: string (required)
     *                   - surname: string (required)
     *                   - middle_name: string (optional)
     *                   - nickname: string (optional)
     *                   - email: string (required)
     *                   - phone: string (required)
     *                   - phone_2: string (optional)
     *                   - password: string (required)
     *                   - date_of_birth: string (optional)
     *                   - gender: string (optional)
     *                   - religion: string (optional)
     *                   - blood_group: string (optional)
     *                   - height_meters: float (optional)
     *                   - disability_status: string (optional)
     *                   - state_of_origin: string (optional)
     *                   - lga_of_origin: string (optional)
     *                   - address_of_origin: string (optional)
     *                   - residence_state_id: int (optional)
     *                   - residence_lga_id: int (optional)
     *                   - residence_address: string (optional)
     *                   - nationality_id: int (optional)
     *                   - nin_number: string (optional)
     *                   - bvn_number: string (optional)
     *                   - license_number: string (optional)
     *                   - license_class: string (optional)
     *                   - license_expiry_date: string (optional)
     *                   - current_employer: string (optional)
     *                   - experience_years: int (optional)
     *                   - employment_start_date: string (optional)
     *                   - vehicle_types: array (optional)
     *                   - work_regions: array (optional)
     *                   - special_skills: string (optional)
     *                   - status: string (optional, default: 'active')
     *                   - verification_status: string (optional, default: 'pending')
     *                   - verification_notes: string (optional)
     * @param array $uploadedFiles Array of uploaded file paths keyed by field name
     *
     * @return array Returns an array with 'success', 'driver', and 'message' keys
     *               On success: ['success' => true, 'driver' => Driver, 'message' => string]
     *               On failure: ['success' => false, 'message' => string]
     *
     * @throws \Exception When driver creation fails due to database errors or validation
     *
     * @example
     * ```php
     * $service = new DriverCreationService();
     * $result = $service->createComprehensiveDriver([
     *     'first_name' => 'John',
     *     'surname' => 'Doe',
     *     'email' => 'john.doe@example.com',
     *     'phone' => '+1234567890',
     *     'password' => 'securepassword',
     *     'date_of_birth' => '1990-01-01',
     *     'gender' => 'male',
     *     'nin_number' => '12345678901',
     *     'license_number' => 'DL123456',
     *     'experience_years' => 5
     * ], [
     *     'profile_picture_path' => 'uploads/profiles/profile.jpg',
     *     'license_document_path' => 'uploads/licenses/license.pdf'
     * ]);
     * ```
     */
    public function createComprehensiveDriver(array $data, array $uploadedFiles = []): array
    {
        try {
            DB::beginTransaction();

            // Generate driver ID
            $driverId = $this->generateDriverId();

            // Build comprehensive driver data
            $driverData = [
                'driver_id' => $driverId,
                'first_name' => $data['first_name'],
                'surname' => $data['surname'],
                'middle_name' => $data['middle_name'] ?? null,
                'nickname' => $data['nickname'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'phone_2' => $data['phone_2'] ?? null,
                'password' => Hash::make($data['password']),
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'religion' => $data['religion'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'height_meters' => $data['height_meters'] ?? null,
                'disability_status' => $data['disability_status'] ?? null,
                'state_of_origin' => $data['state_of_origin'] ?? null,
                'lga_of_origin' => $data['lga_of_origin'] ?? null,
                'address_of_origin' => $data['address_of_origin'] ?? null,
                'residence_state_id' => $data['residence_state_id'] ?? null,
                'residence_lga_id' => $data['residence_lga_id'] ?? null,
                'residence_address' => $data['residence_address'] ?? null,
                'nationality_id' => $data['nationality_id'] ?? null,
                'nin_number' => $data['nin_number'] ?? null,
                'bvn_number' => $data['bvn_number'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'license_class' => $data['license_class'] ?? null,
                'license_expiry_date' => $data['license_expiry_date'] ?? null,
                'current_employer' => $data['current_employer'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'employment_start_date' => $data['employment_start_date'] ?? null,
                'vehicle_types' => isset($data['vehicle_types']) ? json_encode($data['vehicle_types']) : null,
                'work_regions' => isset($data['work_regions']) ? json_encode($data['work_regions']) : null,
                'special_skills' => $data['special_skills'] ?? null,
                'status' => $data['status'] ?? 'active',
                'verification_status' => $data['verification_status'] ?? 'pending',
                'verification_notes' => $data['verification_notes'] ?? null,
                'created_by_admin_id' => auth('admin')->id(),
                'kyc_completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Add uploaded files
            $driverData = array_merge($driverData, $uploadedFiles);

            $driver = Driver::create($driverData);

            DB::commit();

            Log::info('Comprehensive driver created successfully', [
                'driver_id' => $driver->driver_id,
                'admin_id' => auth('admin')->id()
            ]);

            return [
                'success' => true,
                'driver' => $driver,
                'message' => 'Driver created successfully with complete KYC information'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comprehensive driver creation failed', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create comprehensive driver: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a unified driver based on registration mode
     *
     * Creates a driver with different levels of information based on the specified mode.
     * This provides flexibility for different registration workflows.
     *
     * @param array $data Driver creation data (same as simple driver, plus mode-specific fields)
     * @param string $mode Registration mode: 'simple', 'standard', or 'comprehensive'
     *                     - 'simple': Basic account info only
     *                     - 'standard': Basic + KYC fields (DOB, gender, nationality, NIN, BVN, address)
     *                     - 'comprehensive': All available fields including employment and emergency contacts
     *
     * @return array Returns an array with 'success', 'driver', and 'message' keys
     *               On success: ['success' => true, 'driver' => Driver, 'message' => string]
     *               On failure: ['success' => false, 'message' => string]
     *
     * @throws \Exception When driver creation fails due to database errors or invalid mode
     *
     * @example
     * ```php
     * $service = new DriverCreationService();
     * $result = $service->createUnifiedDriver([
     *     'first_name' => 'John',
     *     'surname' => 'Doe',
     *     'email' => 'john.doe@example.com',
     *     'phone' => '+1234567890',
     *     'password' => 'securepassword',
     *     'driver_license_number' => 'DL123456',
     *     'date_of_birth' => '1990-01-01',
     *     'gender' => 'male',
     *     'nin_number' => '12345678901'
     * ], 'standard');
     * ```
     */
    public function createUnifiedDriver(array $data, string $mode): array
    {
        try {
            DB::beginTransaction();

            // Generate driver ID
            $driverId = $this->generateDriverId();

            // Build driver data based on mode
            $driverData = $this->buildDriverDataForMode($data, $mode, $driverId);

            $driver = Driver::create($driverData);

            DB::commit();

            Log::info('Unified driver created successfully', [
                'driver_id' => $driver->driver_id,
                'mode' => $mode,
                'admin_id' => auth('admin')->id()
            ]);

            return [
                'success' => true,
                'driver' => $driver,
                'message' => ucfirst($mode) . ' driver account created successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unified driver creation failed', [
                'error' => $e->getMessage(),
                'mode' => $mode,
                'admin_id' => auth('admin')->id()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create driver account: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build driver data array based on registration mode
     *
     * Constructs the appropriate driver data array based on the specified registration mode.
     * Different modes include different sets of required and optional fields.
     *
     * @param array $data Raw input data from the request
     * @param string $mode Registration mode ('simple', 'standard', or 'comprehensive')
     * @param string $driverId Pre-generated unique driver ID
     *
     * @return array Filtered driver data array ready for database insertion
     *
     * @throws \InvalidArgumentException When an invalid mode is provided
     */
    private function buildDriverDataForMode(array $data, string $mode, string $driverId): array
    {
        // Base data for all modes
        $driverData = [
            'driver_id' => $driverId,
            'first_name' => $data['first_name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'driver_license_number' => $data['driver_license_number'],
            'status' => $data['status'] ?? 'active',
            'verification_status' => $data['verification_status'] ?? 'pending',
            'kyc_status' => $data['kyc_status'] ?? 'pending',
            'created_by_admin_id' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add standard KYC fields
        if ($mode === 'standard' || $mode === 'comprehensive') {
            $driverData = array_merge($driverData, [
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'nationality_id' => $data['nationality_id'] ?? null,
                'nin_number' => $data['nin_number'] ?? null,
                'bvn_number' => $data['bvn_number'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        }

        // Add comprehensive fields
        if ($mode === 'comprehensive') {
            $driverData = array_merge($driverData, [
                'middle_name' => $data['middle_name'] ?? null,
                'state_of_origin' => $data['state_of_origin'] ?? null,
                'lga_of_origin' => $data['lga_of_origin'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'license_expiry_date' => $data['license_expiry_date'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'kyc_completed_at' => now(),
            ]);
        }

        return array_filter($driverData, function($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Generate a unique driver ID
     *
     * Creates a unique identifier for the driver using the DrivelinkHelper.
     * Falls back to a random string generation if the helper fails.
     *
     * @return string Unique driver ID in format 'DRV-XXXXXXXX'
     *
     * @throws \Exception When ID generation fails after multiple attempts
     */
    private function generateDriverId(): string
    {
        try {
            return DrivelinkHelper::generateDriverId();
        } catch (\Exception $e) {
            // Fallback if helper fails
            do {
                $id = 'DRV-' . strtoupper(Str::random(8));
            } while (Driver::where('driver_id', $id)->exists());

            return $id;
        }
    }
}
