<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class FixDriversTable extends Command
{
    protected $signature = 'drivelink:fix-drivers-table {--force : Force the operation without confirmation}';
    protected $description = 'Fix the drivers table creation issue in drivelink_db database';

    public function handle()
    {
        $this->info('DriveLink Drivers Table Fixer');
        $this->info('==============================');
        $this->info('Target Database: ' . config('database.connections.mysql.database'));
        
        if (!$this->option('force')) {
            if (!$this->confirm('This will create/fix the drivers table. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            // Check if table exists
            $this->info('Checking drivers table...');
            
            if (Schema::hasTable('drivers')) {
                $this->info('✓ drivers table already exists');
                return 0;
            }
            
            $this->info('Table does not exist. Creating...');
            
            // Check dependencies
            $this->checkDependencies();
            
            // Create the table
            $this->createDriversTable();
            
            $this->info('✅ drivers table created successfully!');
            $this->info('You can now use the driver creation functionality.');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('Error occurred: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function checkDependencies()
    {
        $this->info('Checking dependencies...');
        
        // Check admin_users table
        if (!Schema::hasTable('admin_users')) {
            $this->info('Creating admin_users table...');
            Schema::create('admin_users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->string('role')->nullable();
                $table->string('status')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
            $this->info('✓ admin_users table created');
        }
        
        // Check nationalities table
        if (!Schema::hasTable('nationalities')) {
            $this->info('Creating nationalities table...');
            Schema::create('nationalities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 10);
                $table->timestamps();
            });
            
            // Insert default nationality
            DB::table('nationalities')->insert([
                'id' => 1,
                'name' => 'Nigerian',
                'code' => 'NG',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->info('✓ nationalities table created with default data');
        }
    }
    
    private function createDriversTable()
    {
        $this->info('Creating drivers table...');
        
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_id')->unique();
            
            // Personal Information
            $table->string('nickname')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname');
            $table->string('phone')->unique();
            $table->string('phone_2')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->decimal('height_meters', 3, 2)->nullable();
            $table->string('disability_status')->nullable();
            $table->foreignId('nationality_id')->nullable()->default(1)->constrained('nationalities')->onDelete('set null');
            
            // Profile and Documents
            $table->string('profile_picture')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('passport_photograph')->nullable();
            $table->string('nin_number', 11)->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_class')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('license_front_image')->nullable();
            $table->string('license_back_image')->nullable();
            
            // Professional Information
            $table->string('current_employer')->nullable();
            $table->integer('experience_years')->nullable();
            $table->date('employment_start_date')->nullable();
            $table->text('residence_address')->nullable();
            $table->integer('residence_state_id')->nullable();
            $table->integer('residence_lga_id')->nullable();
            $table->json('vehicle_types')->nullable();
            $table->json('work_regions')->nullable();
            $table->text('special_skills')->nullable();
            
            // System Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'blocked'])->default('active');
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'reviewing'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            
            // Verification tracking
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->text('verification_notes')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason')->nullable();
            
            // OCR Status
            $table->enum('ocr_verification_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('ocr_verification_notes')->nullable();
            $table->json('nin_verification_data')->nullable();
            $table->timestamp('nin_verified_at')->nullable();
            $table->decimal('nin_ocr_match_score', 5, 2)->nullable();
            $table->json('frsc_verification_data')->nullable();
            $table->timestamp('frsc_verified_at')->nullable();
            $table->decimal('frsc_ocr_match_score', 5, 2)->nullable();
            
            // Laravel Auth
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'verification_status']);
            $table->index(['verified_at', 'verified_by']);
            $table->index(['nationality_id']);
            $table->index(['driver_id']);
            $table->index(['phone']);
            $table->index(['email']);
        });
        
        // Mark migration as complete
        DB::table('migrations')->insertOrIgnore([
            'migration' => '2025_08_11_172000_create_normalized_drivers_table',
            'batch' => 1
        ]);
    }
}