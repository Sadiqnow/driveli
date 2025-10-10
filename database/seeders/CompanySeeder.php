<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run()
    {
        // Get the first admin user for verification, or null if none exists
        $adminUser = \App\Models\AdminUser::first();
        $adminId = $adminUser ? $adminUser->id : null;
        
        // Dangote Group
        Company::firstOrCreate(
            ['email' => 'logistics@dangote.com'],
            [
            'name' => 'Dangote Group',
            'company_id' => Company::generateCompanyId(),
            'registration_number' => 'RC123456',
            'tax_id' => 'TIN001234567',
            'email' => 'logistics@dangote.com',
            'phone' => '+2341234567890',
            'website' => 'https://dangote.com',
            'address' => '1, Alfred Rewane Road, Ikoyi, Lagos',
            'state' => 'Lagos',
            'industry' => 'Manufacturing',
            'company_size' => '1000+',
            'description' => 'Leading manufacturer of cement, sugar, salt, flour, and other products.',
            'contact_person_name' => 'Mr. Ibrahim Logistics',
            'contact_person_title' => 'Head of Logistics',
            'contact_person_phone' => '+2348012345678',
            'contact_person_email' => 'ibrahim.logistics@dangote.com',
            'default_commission_rate' => 12.00,
            'payment_terms' => '7 days',
            'preferred_regions' => ['Lagos', 'Kano', 'Abuja', 'Port Harcourt'],
            'vehicle_types_needed' => ['Tanker', 'Trailer', 'Tipper'],
            'verification_status' => $adminId ? 'Verified' : 'Pending',
            'verified_at' => $adminId ? now() : null,
            'verified_by' => $adminId,
            'status' => 'Active',
            ]
        );

        // BUA Group
        Company::firstOrCreate(
            ['email' => 'transport@buagroup.com'],
            [
            'name' => 'BUA Group',
            'company_id' => Company::generateCompanyId(),
            'registration_number' => 'RC234567',
            'tax_id' => 'TIN002345678',
            'email' => 'transport@buagroup.com',
            'phone' => '+2342345678901',
            'website' => 'https://buagroup.com',
            'address' => 'BUA House, 270 Ozumba Mbadiwe Avenue, Victoria Island, Lagos',
            'state' => 'Lagos',
            'industry' => 'Manufacturing',
            'company_size' => '1000+',
            'description' => 'Diversified conglomerate with interests in cement, sugar, ports, shipping, and oil & gas.',
            'contact_person_name' => 'Mrs. Fatima Transport',
            'contact_person_title' => 'Transport Manager',
            'contact_person_phone' => '+2348023456789',
            'contact_person_email' => 'fatima.transport@buagroup.com',
            'default_commission_rate' => 15.00,
            'payment_terms' => '14 days',
            'preferred_regions' => ['Lagos', 'Abuja', 'Kano'],
            'vehicle_types_needed' => ['Tipper', 'Trailer', 'Container'],
            'verification_status' => $adminId ? 'Verified' : 'Pending',
            'verified_at' => $adminId ? now() : null,
            'verified_by' => $adminId,
            'status' => 'Active',
            ]
        );

        // Mangal Industries
        Company::firstOrCreate(
            ['email' => 'fleet@mangalindustries.com'],
            [
            'name' => 'Mangal Industries',
            'company_id' => Company::generateCompanyId(),
            'registration_number' => 'RC345678',
            'tax_id' => 'TIN003456789',
            'email' => 'fleet@mangalindustries.com',
            'phone' => '+2343456789012',
            'website' => 'https://mangalindustries.com',
            'address' => 'Mangal Industrial Estate, Warri, Delta State',
            'state' => 'Delta',
            'industry' => 'Manufacturing',
            'company_size' => '201-1000',
            'description' => 'Steel manufacturing and industrial equipment.',
            'contact_person_name' => 'Mr. David Fleet',
            'contact_person_title' => 'Fleet Coordinator',
            'contact_person_phone' => '+2348034567890',
            'contact_person_email' => 'david.fleet@mangalindustries.com',
            'default_commission_rate' => 18.00,
            'payment_terms' => '30 days',
            'preferred_regions' => ['Port Harcourt', 'Lagos', 'Abuja'],
            'vehicle_types_needed' => ['Trailer', 'Flatbed'],
            'verification_status' => $adminId ? 'Verified' : 'Pending',
            'verified_at' => $adminId ? now() : null,
            'verified_by' => $adminId,
            'status' => 'Active',
            ]
        );

        $this->command->info('Companies seeded successfully!');
    }
}