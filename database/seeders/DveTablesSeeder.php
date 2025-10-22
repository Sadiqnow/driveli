<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Drivers;
use App\Models\DriverDocument;
use App\Models\DriverVerificationLog;
use App\Models\DriverFacialVerification;
use App\Models\ModeratorAction;
use App\Models\AdminUser;

class DveTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample drivers and admin users if they don't exist
        if (Drivers::count() === 0) {
            $this->createSampleDrivers();
        }

        if (AdminUser::count() === 0) {
            $this->createSampleAdminUsers();
        }

        // Get existing drivers and admin users
        $drivers = Drivers::limit(5)->get();
        $adminUsers = AdminUser::limit(3)->get();

        if ($drivers->isEmpty() || $adminUsers->isEmpty()) {
            $this->command->warn('No drivers or admin users found. Please seed those tables first.');
            return;
        }

        // Seed driver_documents
        $this->seedDriverDocuments($drivers, $adminUsers);

        // Seed driver_verification_logs
        $this->seedDriverVerificationLogs($drivers, $adminUsers);

        // Seed driver_facial_verifications
        $this->seedDriverFacialVerifications($drivers, $adminUsers);

        // Seed moderator_actions
        $this->seedModeratorActions($drivers, $adminUsers);

        $this->command->info('DVE tables seeded successfully!');
    }

    private function seedDriverDocuments($drivers, $adminUsers)
    {
        $documentTypes = ['nin', 'license_front', 'license_back', 'profile_picture', 'passport_photo'];

        foreach ($drivers as $driver) {
            foreach ($documentTypes as $type) {
                DriverDocument::create([
                    'driver_id' => $driver->id,
                    'document_type' => $type,
                    'document_path' => "documents/{$driver->id}/{$type}_sample.jpg",
                    'verification_status' => collect(['pending', 'approved', 'rejected'])->random(),
                    'verified_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                    'verified_by' => rand(0, 1) ? $adminUsers->random()->id : null,
                    'rejection_reason' => rand(0, 2) ? 'Document unclear' : null,
                    'ocr_data' => json_encode([
                        'extracted_text' => "Sample OCR data for {$type}",
                        'confidence' => rand(70, 95) / 100
                    ]),
                    'created_at' => now()->subDays(rand(1, 60)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedDriverVerificationLogs($drivers, $adminUsers)
    {
        $actions = ['ocr_verification', 'facial_verification', 'document_verification', 'manual_review'];

        foreach ($drivers as $driver) {
            for ($i = 0; $i < 3; $i++) {
                DriverVerificationLog::create([
                    'driver_id' => $driver->id,
                    'action' => collect($actions)->random(),
                    'status' => collect(['completed', 'failed', 'pending'])->random(),
                    'verification_data' => json_encode([
                        'input_data' => 'Sample verification input',
                        'parameters' => ['threshold' => 0.8]
                    ]),
                    'result_data' => json_encode([
                        'output' => 'Sample verification result',
                        'processing_time' => rand(100, 500) . 'ms'
                    ]),
                    'confidence_score' => rand(60, 95) / 100,
                    'notes' => 'Sample verification log entry',
                    'performed_by' => $adminUsers->random()->id,
                    'performed_at' => now()->subDays(rand(1, 30)),
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedDriverFacialVerifications($drivers, $adminUsers)
    {
        foreach ($drivers as $driver) {
            for ($i = 0; $i < 2; $i++) {
                $status = collect(['completed', 'failed', 'expired'])->random();
                $isMatch = $status === 'completed' ? (bool)rand(0, 1) : null;

                DriverFacialVerification::create([
                    'driver_id' => $driver->id,
                    'session_id' => 'session_' . uniqid(),
                    'status' => $status,
                    'facial_data' => json_encode([
                        'landmarks' => [/* sample facial landmarks */],
                        'features' => 'Sample facial features data'
                    ]),
                    'reference_image_path' => "facial_ref/{$driver->id}_ref.jpg",
                    'captured_image_path' => "facial_cap/{$driver->id}_cap.jpg",
                    'similarity_score' => $isMatch !== null ? rand(70, 95) / 100 : null,
                    'confidence_score' => $isMatch !== null ? rand(80, 98) / 100 : null,
                    'is_match' => $isMatch,
                    'verification_metadata' => json_encode([
                        'device_info' => 'Sample device',
                        'location' => 'Sample location'
                    ]),
                    'failure_reason' => $status === 'failed' ? 'Poor lighting conditions' : null,
                    'started_at' => now()->subMinutes(rand(5, 30)),
                    'completed_at' => $status === 'completed' ? now()->subMinutes(rand(1, 5)) : null,
                    'expires_at' => now()->addHours(rand(1, 24)),
                    'verified_by' => $status === 'completed' ? $adminUsers->random()->id : null,
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function createSampleDrivers()
    {
        for ($i = 1; $i <= 5; $i++) {
            Drivers::create([
                'driver_id' => 'DRV' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'first_name' => 'Driver' . $i,
                'surname' => 'Test' . $i,
                'email' => 'driver' . $i . '@example.com',
                'phone' => '+234' . rand(7000000000, 9999999999),
                'password' => bcrypt('password'),
                'status' => 'active',
                'verification_status' => 'pending',
                'is_active' => true,
                'kyc_status' => 'pending',
                'kyc_step' => 'not_started',
            ]);
        }
    }

    private function createSampleAdminUsers()
    {
        for ($i = 1; $i <= 3; $i++) {
            AdminUser::create([
                'name' => 'Admin User ' . $i,
                'email' => 'admin' . $i . '@example.com',
                'password' => bcrypt('password'),
                'phone' => '+234' . rand(7000000000, 9999999999),
                'status' => 'active',
                'role' => 'moderator',
                'permissions' => json_encode(['verify_drivers', 'moderate_content']),
                'last_login_at' => now()->subDays(rand(1, 7)),
            ]);
        }
    }

    private function seedModeratorActions($drivers, $adminUsers)
    {
        $actionTypes = ['approve', 'reject', 'suspend', 'reinstate', 'flag', 'review'];
        $resourceTypes = ['driver', 'document', 'verification', 'profile'];

        foreach ($drivers as $driver) {
            for ($i = 0; $i < 2; $i++) {
                $actionType = collect($actionTypes)->random();
                $isReversible = !in_array($actionType, ['approve', 'reject']);

                ModeratorAction::create([
                    'driver_id' => $driver->id,
                    'moderator_id' => $adminUsers->random()->id,
                    'action_type' => $actionType,
                    'resource_type' => collect($resourceTypes)->random(),
                    'resource_id' => rand(1, 100), // Sample resource ID
                    'action_data' => json_encode([
                        'previous_status' => 'pending',
                        'new_status' => $actionType === 'approve' ? 'approved' : 'rejected'
                    ]),
                    'reason' => "Sample reason for {$actionType} action",
                    'notes' => "Additional notes for {$actionType} action on driver",
                    'metadata' => json_encode([
                        'ip_address' => '192.168.1.' . rand(1, 255),
                        'user_agent' => 'Sample browser agent'
                    ]),
                    'effective_from' => now()->subDays(rand(0, 7)),
                    'effective_until' => in_array($actionType, ['suspend']) ? now()->addDays(rand(7, 30)) : null,
                    'is_reversible' => $isReversible,
                    'reversed_by' => rand(0, 2) && $isReversible ? $adminUsers->random()->id : null,
                    'reversed_at' => rand(0, 2) && $isReversible ? now()->subDays(rand(1, 7)) : null,
                    'reversal_reason' => rand(0, 2) && $isReversible ? 'Action reversed due to new evidence' : null,
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
