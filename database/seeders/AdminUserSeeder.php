<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create Super Admin (only if doesn't exist)
        AdminUser::firstOrCreate(
            ['email' => 'admin@drivelink.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'phone' => '+2348012345678',
                'role' => 'Super Admin',
                'status' => 'Active',
                'email_verified_at' => now(),
            ]
        );

        // Create Regular Admin (only if doesn't exist)
        AdminUser::firstOrCreate(
            ['email' => 'john@drivelink.com'],
            [
                'name' => 'John Manager',
                'password' => Hash::make('password123'),
                'phone' => '+2348023456789',
                'role' => 'Admin',
                'status' => 'Active',
                'email_verified_at' => now(),
                'permissions' => [
                    'manage_drivers',
                    'manage_requests',
                    'send_notifications',
                    'view_reports',
                ],
            ]
        );

        // Create Manager (only if doesn't exist)
        AdminUser::firstOrCreate(
            ['email' => 'sarah@drivelink.com'],
            [
                'name' => 'Sarah Operations',
                'password' => Hash::make('password123'),
                'phone' => '+2348034567890',
                'role' => 'Manager',
                'status' => 'Active',
                'email_verified_at' => now(),
                'permissions' => [
                    'view_drivers',
                    'view_requests',
                    'send_notifications',
                    'view_reports',
                ],
            ]
        );

        $this->command->info('Admin users seeded successfully!');
    }
}