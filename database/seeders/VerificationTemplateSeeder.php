<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VerificationTemplate;

class VerificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Driver License Verification',
                'type' => 'driver_license',
                'description' => 'Template for verifying driver licenses',
                'template_data' => [
                    'required_fields' => ['license_number', 'issue_date', 'expiry_date', 'issuing_authority'],
                    'validation_rules' => [
                        'license_number' => 'required|string|min:5|max:20',
                        'issue_date' => 'required|date|before:today',
                        'expiry_date' => 'required|date|after:today',
                        'issuing_authority' => 'required|string|max:100'
                    ],
                    'api_endpoints' => [
                        'verify' => 'https://api.verification-service.com/driver-license',
                        'check_status' => 'https://api.verification-service.com/status'
                    ]
                ],
                'is_active' => true,
                'priority' => 1
            ],
            [
                'name' => 'Identity Verification',
                'type' => 'identity',
                'description' => 'Template for verifying national ID or passport',
                'template_data' => [
                    'required_fields' => ['id_number', 'id_type', 'full_name', 'date_of_birth'],
                    'validation_rules' => [
                        'id_number' => 'required|string|min:8|max:20',
                        'id_type' => 'required|in:national_id,passport,drivers_license',
                        'full_name' => 'required|string|max:255',
                        'date_of_birth' => 'required|date|before:today'
                    ],
                    'api_endpoints' => [
                        'verify' => 'https://api.verification-service.com/identity',
                        'biometric_check' => 'https://api.verification-service.com/biometric'
                    ]
                ],
                'is_active' => true,
                'priority' => 2
            ],
            [
                'name' => 'Address Verification',
                'type' => 'address',
                'description' => 'Template for verifying residential address',
                'template_data' => [
                    'required_fields' => ['street_address', 'city', 'state', 'postal_code', 'country'],
                    'validation_rules' => [
                        'street_address' => 'required|string|max:255',
                        'city' => 'required|string|max:100',
                        'state' => 'required|string|max:100',
                        'postal_code' => 'required|string|max:20',
                        'country' => 'required|string|max:100'
                    ],
                    'api_endpoints' => [
                        'verify' => 'https://api.verification-service.com/address',
                        'geocode' => 'https://api.verification-service.com/geocode'
                    ]
                ],
                'is_active' => true,
                'priority' => 3
            ],
            [
                'name' => 'Vehicle Verification',
                'type' => 'vehicle',
                'description' => 'Template for verifying vehicle ownership and registration',
                'template_data' => [
                    'required_fields' => ['vehicle_make', 'vehicle_model', 'license_plate', 'registration_number', 'year'],
                    'validation_rules' => [
                        'vehicle_make' => 'required|string|max:50',
                        'vehicle_model' => 'required|string|max:50',
                        'license_plate' => 'required|string|max:20',
                        'registration_number' => 'required|string|max:30',
                        'year' => 'required|integer|min:1900|max:' . (date('Y') + 1)
                    ],
                    'api_endpoints' => [
                        'verify' => 'https://api.verification-service.com/vehicle',
                        'insurance_check' => 'https://api.verification-service.com/insurance'
                    ]
                ],
                'is_active' => true,
                'priority' => 4
            ],
            [
                'name' => 'Employment Verification',
                'type' => 'employment',
                'description' => 'Template for verifying employment history',
                'template_data' => [
                    'required_fields' => ['employer_name', 'position', 'employment_start_date', 'employment_end_date', 'contact_person'],
                    'validation_rules' => [
                        'employer_name' => 'required|string|max:255',
                        'position' => 'required|string|max:100',
                        'employment_start_date' => 'required|date|before:today',
                        'employment_end_date' => 'nullable|date|after:employment_start_date',
                        'contact_person' => 'required|string|max:255'
                    ],
                    'api_endpoints' => [
                        'verify' => 'https://api.verification-service.com/employment',
                        'reference_check' => 'https://api.verification-service.com/reference'
                    ]
                ],
                'is_active' => true,
                'priority' => 5
            ],
            [
                'name' => 'Criminal Background Check',
                'type' => 'criminal_background',
                'description' => 'Template for criminal background verification',
                'template_data' => [
                    'required_fields' => ['full_name', 'date_of_birth', 'national_id', 'consent_given'],
                    'validation_rules' => [
                        'full_name' => 'required|string|max:255',
                        'date_of_birth' => 'required|date|before:today',
                        'national_id' => 'required|string|min:8|max:20',
                        'consent_given' => 'required|boolean|accepted'
                    ],
                    'api_endpoints' => [
                        'verify' => 'https://api.verification-service.com/criminal-background',
                        'detailed_report' => 'https://api.verification-service.com/criminal-report'
                    ]
                ],
                'is_active' => true,
                'priority' => 6
            ]
        ];

        foreach ($templates as $template) {
            VerificationTemplate::firstOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        $this->command->info('Verification templates seeded successfully.');
    }
}
