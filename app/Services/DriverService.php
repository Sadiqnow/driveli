<?php

namespace App\Services;

use App\Models\Drivers;
use App\Models\DriverDocument;
use App\Models\DriverLocation;
use App\Models\DriverBankingDetail;
use App\Models\DriverNextOfKin;
use App\Models\DriverEmploymentHistory;
use App\Models\DriverPreference;
use App\Models\AdminUser;
use App\Exceptions\DriverException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Driver Service Class
 * 
 * Handles all driver-related business logic including:
 * - Driver registration and profile management
 * - Document uploads and verification
 * - Location and contact information management
 * - Performance tracking and statistics
 * 
 * @package App\Services
 * @author DriveLink Development Team
 * @since 1.0.0
 */
class DriverService
{
    /**
     * Create a new driver with complete profile data.
     *
     * This method handles the complete driver registration process including:
     * - Creating the main driver record
     * - Processing and storing document uploads
     * - Setting up location information
     * - Configuring banking details
     * - Recording next of kin information
     * - Setting employment history
     * - Configuring driver preferences
     *
     * @param array $data Driver registration data containing all required fields
     * @return DriverNormalized The created driver instance with relationships loaded
     * @throws DriverException If driver creation fails or validation errors occur
     * @throws \Exception If database transaction fails
     * 
     * @example
     * $driverData = [
     *     'first_name' => 'John',
     *     'surname' => 'Doe',
     *     'email' => 'john@example.com',
     *     'phone' => '+2348012345678',
     *     // ... other required fields
     * ];
     * $driver = $driverService->createDriver($driverData);
     */
    public function createDriver(array $data): Drivers
    {
        return DB::transaction(function () use ($data) {
            // Generate unique driver ID
            $driverId = $this->generateDriverId();

            // Create main driver record
            $driver = Drivers::create([
                'driver_id' => $driverId,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'surname' => $data['surname'],
                'nickname' => $data['nickname'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'phone_2' => $data['phone_2'] ?? null,
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'religion' => $data['religion'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'height_meters' => $data['height_meters'] ?? null,
                'disability_status' => $data['disability_status'] ?? 'None',
                'nationality_id' => $data['nationality_id'],
                'nin_number' => $data['nin_number'],
                'license_number' => $data['license_number'],
                'license_class' => $data['license_class'],
                'status' => 'inactive',
                'verification_status' => 'pending',
                'is_active' => false,
                'registered_at' => now(),
                'password' => bcrypt($data['password'] ?? Str::random(12)),
            ]);

            // Handle file uploads
            if (isset($data['profile_picture'])) {
                $profilePath = $this->storeDriverDocument($data['profile_picture'], 'profiles');
                $driver->update(['profile_picture' => $profilePath]);
            }

            // Store documents
            $this->storeDriverDocuments($driver, $data);

            // Store location information
            $this->storeDriverLocations($driver, $data);

            // Store banking details
            $this->storeDriverBankingDetails($driver, $data);

            // Store next of kin information
            $this->storeDriverNextOfKin($driver, $data);

            // Store employment history
            $this->storeDriverEmploymentHistory($driver, $data);

            // Store preferences
            $this->storeDriverPreferences($driver, $data);

            return $driver->load([
                'nationality',
                'locations',
                'bankingDetails',
                'nextOfKin',
                'employmentHistory',
                'preferences',
                'documents'
            ]);
        });
    }

    /**
     * Update driver profile with new data.
     *
     * Updates an existing driver's profile information including personal details,
     * contact information, and optional document uploads. Only provided fields
     * will be updated, existing values are preserved for null/missing fields.
     *
     * @param DriverNormalized $driver The driver instance to update
     * @param array $data Array of updated driver data
     * @return DriverNormalized The updated driver instance
     * @throws DriverException If driver update fails or validation errors occur
     * @throws \Exception If database transaction fails
     * 
     * @example
     * $updateData = [
     *     'phone' => '+2348087654321',
     *     'email' => 'newemail@example.com',
     *     'profile_picture' => $uploadedFile
     * ];
     * $updatedDriver = $driverService->updateDriver($driver, $updateData);
     */
    public function updateDriver(Drivers $driver, array $data): Drivers
    {
        return DB::transaction(function () use ($driver, $data) {
            // Update main driver record
            $driver->update(array_filter([
                'first_name' => $data['first_name'] ?? $driver->first_name,
                'middle_name' => $data['middle_name'] ?? $driver->middle_name,
                'surname' => $data['surname'] ?? $driver->surname,
                'nickname' => $data['nickname'] ?? $driver->nickname,
                'email' => $data['email'] ?? $driver->email,
                'phone' => $data['phone'] ?? $driver->phone,
                'phone_2' => $data['phone_2'] ?? $driver->phone_2,
                'date_of_birth' => $data['date_of_birth'] ?? $driver->date_of_birth,
                'gender' => $data['gender'] ?? $driver->gender,
                'religion' => $data['religion'] ?? $driver->religion,
                'blood_group' => $data['blood_group'] ?? $driver->blood_group,
                'height_meters' => $data['height_meters'] ?? $driver->height_meters,
                'disability_status' => $data['disability_status'] ?? $driver->disability_status,
                'nationality_id' => $data['nationality_id'] ?? $driver->nationality_id,
                'nin_number' => $data['nin_number'] ?? $driver->nin_number,
                'license_number' => $data['license_number'] ?? $driver->license_number,
                'license_class' => $data['license_class'] ?? $driver->license_class,
            ]));

            // Handle profile picture update
            if (isset($data['profile_picture'])) {
                $this->updateDriverProfilePicture($driver, $data['profile_picture']);
            }

            // Update documents if provided
            $this->updateDriverDocuments($driver, $data);

            return $driver->fresh();
        });
    }

    /**
     * Verify a driver and update verification status.
     *
     * Marks a driver as verified by an admin user, activating their account
     * and enabling them to receive job matches. This is a critical business
     * operation that affects driver eligibility for work assignments.
     *
     * @param DriverNormalized $driver The driver to verify
     * @param AdminUser $admin The admin performing the verification
     * @param string|null $notes Optional verification notes
     * @return DriverNormalized The verified driver instance
     * @throws DriverException If driver is already verified or not eligible
     * 
     * @example
     * $verifiedDriver = $driverService->verifyDriver($driver, $admin, 'All documents verified');
     */
    public function verifyDriver(DriverNormalized $driver, AdminUser $admin, string $notes = null): DriverNormalized
    {
        // Check if driver is already verified
        if ($driver->isVerified()) {
            throw DriverException::alreadyVerified($driver->driver_id);
        }

        // Check if driver profile is complete
        if (!$driver->hasCompleteProfile()) {
            throw DriverException::profileIncomplete($driver->driver_id, ['profile', 'documents', 'location']);
        }

        $driver->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'verification_notes' => $notes,
            'status' => 'active',
            'is_active' => true,
        ]);

        return $driver;
    }

    /**
     * Reject a driver verification.
     *
     * Marks a driver verification as rejected with a specific reason.
     * The driver will need to address the issues and resubmit for verification.
     *
     * @param DriverNormalized $driver The driver to reject
     * @param AdminUser $admin The admin performing the rejection
     * @param string $reason Detailed reason for rejection
     * @return DriverNormalized The rejected driver instance
     * @throws DriverException If driver is already rejected or verified
     * 
     * @example
     * $rejectedDriver = $driverService->rejectDriver($driver, $admin, 'Incomplete documentation');
     */
    public function rejectDriver(DriverNormalized $driver, AdminUser $admin, string $reason): DriverNormalized
    {
        // Check if driver is already verified
        if ($driver->isVerified()) {
            throw DriverException::alreadyVerified($driver->driver_id);
        }

        // Check if driver is already rejected
        if ($driver->verification_status === 'rejected') {
            throw DriverException::alreadyRejected($driver->driver_id);
        }

        $driver->update([
            'verification_status' => 'rejected',
            'rejected_at' => now(),
            'verified_by' => $admin->id,
            'rejection_reason' => $reason,
            'status' => 'inactive',
            'is_active' => false,
        ]);

        return $driver;
    }

    /**
     * Get drivers with filters and pagination.
     *
     * Retrieves a paginated list of drivers with optional filtering and sorting.
     * Supports advanced search capabilities and optimized eager loading for
     * performance. This method is commonly used for admin dashboard listings.
     *
     * @param array $filters Optional filters for driver selection
     *   - status: Driver status (active, inactive, suspended)
     *   - verification_status: Verification status (pending, verified, rejected)
     *   - gender: Driver gender (Male, Female)
     *   - nationality_id: Nationality filter
     *   - search: Text search across name, email, phone, driver_id
     *   - age_min/age_max: Age range filters
     *   - sort_by: Field to sort by (default: created_at)
     *   - sort_order: Sort direction (asc/desc, default: desc)
     * @param int $perPage Number of results per page (default: 15)
     * @return LengthAwarePaginator Paginated driver results with relationships
     * 
     * @example
     * $filters = [
     *     'status' => 'active',
     *     'verification_status' => 'verified',
     *     'search' => 'john',
     *     'sort_by' => 'verified_at'
     * ];
     * $drivers = $driverService->getDrivers($filters, 20);
     */
    public function getDrivers(array $filters = [], int $perPage = 15)
    {
        $query = DriverNormalized::query()
            ->with(['nationality', 'verifiedBy', 'performance']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['nationality_id'])) {
            $query->where('nationality_id', $filters['nationality_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('driver_id', 'like', "%{$search}%");
            });
        }

        if (isset($filters['age_min']) || isset($filters['age_max'])) {
            $query->byAge($filters['age_min'] ?? null, $filters['age_max'] ?? null);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get optimized driver statistics for dashboard
     */
    public function getDashboardStats(): array
    {
        $basicStats = [
            'verified' => DriverNormalized::where('verification_status', 'verified')->count(),
            'pending' => DriverNormalized::where('verification_status', 'pending')->count(),
            'rejected' => DriverNormalized::where('verification_status', 'rejected')->count(),
            'active' => DriverNormalized::active()->count(),
            'new_this_month' => DriverNormalized::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->count(),
            'active_today' => DriverNormalized::where('last_active_at', '>=', now()->startOfDay())->count(),
            'online' => DriverNormalized::where('last_active_at', '>=', now()->subMinutes(15))
                             ->where('is_active', true)
                             ->count(),
        ];

        // Performance stats with optimized aggregation
        $performanceStats = DB::table('driver_performance')
            ->selectRaw('
                COALESCE(SUM(total_earnings), 0) as total_earnings,
                COALESCE(SUM(total_jobs_completed), 0) as total_jobs,
                COALESCE(AVG(CASE WHEN average_rating > 0 THEN average_rating END), 0) as avg_rating
            ')
            ->first();

        return array_merge($basicStats, [
            'total_earnings' => $performanceStats->total_earnings ?? 0,
            'total_jobs_completed' => $performanceStats->total_jobs ?? 0,
            'average_rating' => round($performanceStats->avg_rating ?? 0, 2),
        ]);
    }

    /**
     * Get driver statistics and metrics.
     *
     * @return array
     */
    public function getDriverStatistics(): array
    {
        return [
            'total' => DriverNormalized::count(),
            'verified' => DriverNormalized::verified()->count(),
            'pending' => DriverNormalized::where('verification_status', 'pending')->count(),
            'rejected' => DriverNormalized::where('verification_status', 'rejected')->count(),
            'active' => DriverNormalized::active()->count(),
            'available' => DriverNormalized::available()->count(),
            'by_gender' => [
                'male' => DriverNormalized::where('gender', 'Male')->count(),
                'female' => DriverNormalized::where('gender', 'Female')->count(),
            ],
            'by_status' => [
                'active' => DriverNormalized::where('status', 'active')->count(),
                'inactive' => DriverNormalized::where('status', 'inactive')->count(),
                'suspended' => DriverNormalized::where('status', 'suspended')->count(),
            ],
            'recent_registrations' => DriverNormalized::where('created_at', '>=', now()->subDays(30))->count(),
            'recent_verifications' => DriverNormalized::where('verified_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * Handle document approval/rejection with notifications
     */
    public function handleDocumentAction(DriverNormalized $driver, string $documentType, string $action, AdminUser $admin, string $notes = null): array
    {
        return DB::transaction(function () use ($driver, $documentType, $action, $admin, $notes) {
            $adminName = $admin->name ?? $admin->email ?? 'Admin';
            $timestamp = now()->format('Y-m-d H:i:s');
            
            $actionText = $action === 'approved' ? 'approved' : 'REJECTED';
            $newNote = "Document '{$documentType}' {$actionText} by {$adminName} at {$timestamp}";
            
            if ($notes) {
                $reasonText = $action === 'approved' ? 'Notes' : 'Reason';
                $newNote .= ". {$reasonText}: {$notes}";
            }
            
            // Update driver verification notes
            $currentNotes = $driver->verification_notes ?: '';
            $driver->update([
                'verification_notes' => $currentNotes . "\n" . $newNote,
                'verification_status' => $action === 'rejected' ? 'rejected' : $driver->verification_status
            ]);

            return [
                'success' => true,
                'message' => "Document '{$documentType}' {$actionText} successfully!",
                'notification_sent' => false // Will be handled by calling controller
            ];
        });
    }

    /**
     * Update driver verification status with notifications
     */
    public function updateVerificationStatus(DriverNormalized $driver, string $status, AdminUser $admin, string $notes = null, string $adminPassword = null): array
    {
        // Verify admin password if provided
        if ($adminPassword && !\Hash::check($adminPassword, $admin->password)) {
            throw new \Exception('Invalid admin password');
        }

        return DB::transaction(function () use ($driver, $status, $admin, $notes) {
            $updateData = [
                'verification_status' => $status,
                'verified_by' => $admin->id,
                'verification_notes' => $notes,
            ];

            if ($status === 'verified') {
                $updateData['verified_at'] = now();
                $updateData['rejected_at'] = null;
                $updateData['rejection_reason'] = null;
            } elseif ($status === 'rejected') {
                $updateData['rejected_at'] = now();
                $updateData['rejection_reason'] = $notes;
                $updateData['verified_at'] = null;
            }

            $driver->update($updateData);

            return [
                'success' => true,
                'message' => ucfirst($status) . ' driver successfully!',
                'notification_sent' => false // Will be handled by calling controller
            ];
        });
    }

    /**
     * Generate a unique driver ID.
     *
     * @return string
     */
    private function generateDriverId(): string
    {
        do {
            $id = 'DRV' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (DriverNormalized::where('driver_id', $id)->exists());

        return $id;
    }

    /**
     * Store driver documents.
     *
     * @param DriverNormalized $driver
     * @param array $data
     * @return void
     */
    private function storeDriverDocuments(DriverNormalized $driver, array $data): void
    {
        $documents = [
            'nin_document' => 'nin',
            'license_front' => 'license_front',
            'license_back' => 'license_back',
        ];

        foreach ($documents as $field => $type) {
            if (isset($data[$field])) {
                $path = $this->storeDriverDocument($data[$field], 'documents');
                
                DriverDocument::create([
                    'driver_id' => $driver->driver_id,
                    'document_type' => $type,
                    'file_path' => $path,
                    'file_name' => $data[$field]->getClientOriginalName(),
                    'file_size' => $data[$field]->getSize(),
                    'mime_type' => $data[$field]->getMimeType(),
                    'status' => 'pending',
                    'uploaded_at' => now(),
                ]);
            }
        }
    }

    /**
     * Store a driver document file.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     */
    private function storeDriverDocument(UploadedFile $file, string $folder): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        return $file->storeAs("drivers/{$folder}", $filename, 'public');
    }

    /**
     * Store driver location information.
     *
     * @param DriverNormalized $driver
     * @param array $data
     * @return void
     */
    private function storeDriverLocations(DriverNormalized $driver, array $data): void
    {
        $locations = [
            [
                'type' => 'origin',
                'state_id' => $data['origin_state_id'],
                'lga_id' => $data['origin_lga_id'],
                'address' => $data['origin_address'],
            ],
            [
                'type' => 'residence',
                'state_id' => $data['residence_state_id'],
                'lga_id' => $data['residence_lga_id'],
                'address' => $data['residence_address'],
            ],
        ];

        foreach ($locations as $location) {
            DriverLocation::create([
                'driver_id' => $driver->driver_id,
                'location_type' => $location['type'],
                'state_id' => $location['state_id'],
                'lga_id' => $location['lga_id'],
                'address' => $location['address'],
                'is_primary' => true,
            ]);
        }
    }

    /**
     * Store driver banking details.
     *
     * @param DriverNormalized $driver
     * @param array $data
     * @return void
     */
    private function storeDriverBankingDetails(DriverNormalized $driver, array $data): void
    {
        DriverBankingDetail::create([
            'driver_id' => $driver->driver_id,
            'bank_id' => $data['bank_id'],
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'],
            'bvn' => $data['bvn'] ?? null,
            'is_primary' => true,
            'is_verified' => false,
        ]);
    }

    /**
     * Store driver next of kin information.
     *
     * @param DriverNormalized $driver
     * @param array $data
     * @return void
     */
    private function storeDriverNextOfKin(DriverNormalized $driver, array $data): void
    {
        DriverNextOfKin::create([
            'driver_id' => $driver->driver_id,
            'name' => $data['nok_name'],
            'relationship' => $data['nok_relationship'],
            'phone' => $data['nok_phone'],
            'address' => $data['nok_address'],
            'is_primary' => true,
        ]);
    }

    /**
     * Store driver employment history.
     *
     * @param DriverNormalized $driver
     * @param array $data
     * @return void
     */
    private function storeDriverEmploymentHistory(DriverNormalized $driver, array $data): void
    {
        if (isset($data['current_employment_status']) && $data['current_employment_status'] !== 'Unemployed') {
            DriverEmploymentHistory::create([
                'driver_id' => $driver->driver_id,
                'company_name' => $data['current_employer'] ?? 'Self-employed',
                'job_title' => $data['current_job_title'] ?? 'Driver',
                'employment_type' => $data['current_employment_status'],
                'start_date' => now()->subYear(),
                'end_date' => null,
                'is_current' => true,
            ]);
        }
    }

    /**
     * Store driver preferences.
     *
     * @param DriverNormalized $driver
     * @param array $data
     * @return void
     */
    private function storeDriverPreferences(DriverNormalized $driver, array $data): void
    {
        DriverPreference::create([
            'driver_id' => $driver->driver_id,
            'preferred_work_areas' => json_encode($data['preferred_work_areas'] ?? []),
            'vehicle_type_preference' => $data['vehicle_type_preference'] ?? null,
            'work_schedule_preference' => $data['work_schedule_preference'] ?? null,
            'max_distance_km' => $data['max_distance_km'] ?? null,
            'min_pay_rate' => $data['min_pay_rate'] ?? null,
        ]);
    }

    /**
     * Update driver profile picture.
     *
     * @param DriverNormalized $driver
     * @param UploadedFile $file
     * @return void
     */
    private function updateDriverProfilePicture(DriverNormalized $driver, UploadedFile $file): void
    {
        // Delete old profile picture
        if ($driver->profile_picture) {
            Storage::disk('public')->delete($driver->profile_picture);
        }

        // Store new profile picture
        $path = $this->storeDriverDocument($file, 'profiles');
        $driver->update(['profile_picture' => $path]);
    }

    /**
     * Update driver documents.
     *
     * @param DriverNormalized $driver
     * @param array $data
     * @return void
     */
    private function updateDriverDocuments(DriverNormalized $driver, array $data): void
    {
        $documents = [
            'nin_document' => 'nin',
            'license_front' => 'license_front',
            'license_back' => 'license_back',
        ];

        foreach ($documents as $field => $type) {
            if (isset($data[$field])) {
                // Find existing document
                $existingDoc = DriverDocument::where('driver_id', $driver->driver_id)
                    ->where('document_type', $type)
                    ->first();

                // Delete old file if exists
                if ($existingDoc && $existingDoc->file_path) {
                    Storage::disk('public')->delete($existingDoc->file_path);
                }

                // Store new document
                $path = $this->storeDriverDocument($data[$field], 'documents');

                if ($existingDoc) {
                    $existingDoc->update([
                        'file_path' => $path,
                        'file_name' => $data[$field]->getClientOriginalName(),
                        'file_size' => $data[$field]->getSize(),
                        'mime_type' => $data[$field]->getMimeType(),
                        'status' => 'pending',
                        'uploaded_at' => now(),
                    ]);
                } else {
                    DriverDocument::create([
                        'driver_id' => $driver->driver_id,
                        'document_type' => $type,
                        'file_path' => $path,
                        'file_name' => $data[$field]->getClientOriginalName(),
                        'file_size' => $data[$field]->getSize(),
                        'mime_type' => $data[$field]->getMimeType(),
                        'status' => 'pending',
                        'uploaded_at' => now(),
                    ]);
                }
            }
        }
    }
}