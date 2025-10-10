<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\AdminUser;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-super 
                            {--name= : The name of the super admin}
                            {--email= : The email of the super admin}
                            {--password= : The password for the super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Super Admin User...');

        // Get values from options or ask for input
        $name = $this->option('name') ?: $this->ask('Enter the super admin name');
        $email = $this->option('email') ?: $this->ask('Enter the super admin email');
        $password = $this->option('password') ?: $this->secret('Enter the super admin password');

        // Validate input
        if (empty($name) || empty($email) || empty($password)) {
            $this->error('All fields are required.');
            return 1;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format.');
            return 1;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return 1;
        }

        try {
            // Check if user already exists
            $existingUser = DB::table('admin_users')->where('email', $email)->first();
            if ($existingUser) {
                $this->error('A user with this email already exists.');
                return 1;
            }

            // Create the admin user
            $adminUser = DB::table('admin_users')->insert([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'Super Admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($adminUser) {
                $this->info('✅ Super Admin user created successfully!');
                $this->table(['Field', 'Value'], [
                    ['Name', $name],
                    ['Email', $email],
                    ['Role', 'Super Admin'],
                    ['Status', 'Active'],
                ]);
                $this->info('You can now login at: http://127.0.0.1:8000/admin/login');
                return 0;
            } else {
                $this->error('Failed to create admin user.');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Error creating super admin: ' . $e->getMessage());
            return 1;
        }
    }
}