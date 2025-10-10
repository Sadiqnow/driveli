<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CreateDriversTableCommand extends Command
{
    protected $signature = 'drivelink:create-drivers-table {--force : Force creation even if table exists}';
    protected $description = 'Create the drivers table in drivelink_db database';

    public function handle()
    {
        $this->info('DriveLink: Creating drivers table');
        $this->info('Target database: ' . config('database.connections.mysql.database'));
        
        try {
            // Check if table already exists
            if (Schema::hasTable('drivers') && !$this->option('force')) {
                $this->info('✓ drivers table already exists');
                $count = DB::table('drivers')->count();
                $this->info("Current record count: {$count}");
                return 0;
            }
            
            if ($this->option('force') && Schema::hasTable('drivers')) {
                $this->warn('Dropping existing drivers table...');
                Schema::dropIfExists('drivers');
            }
            
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
                $table->unsignedBigInteger('nationality_id')->nullable()->default(1);
                
                // Profile and Documents
                $table->string('profile_picture')->nullable();
                $table->string('profile_photo')->nullable();
                $table->string('passport_photograph')->nullable();
                $table->string('nin_number', 11)->nullable();
                $table->string('nin_document')->nullable();
                $table->string('license_number')->nullable();
                $table->string('license_class')->nullable();
                $table->date('license_expiry_date')->nullable();
                $table->string('frsc_document')->nullable();
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
                $table->unsignedBigInteger('verified_by')->nullable();
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
            
            $this->info('✅ drivers table created successfully!');
            
            // Test table creation
            $this->info('Testing table access...');
            $count = DB::table('drivers')->count();
            $this->info("✓ Table accessible, record count: {$count}");
            
            // Test model access
            try {
                $modelCount = \App\Models\DriverNormalized::count();
                $this->info("✓ Model access works: {$modelCount} records");
            } catch (\Exception $e) {
                $this->warn("Model access issue: " . $e->getMessage());
            }
            
            $this->info('');
            $this->info('🎯 Next steps:');
            $this->info('1. Go to: http://localhost/drivelink/admin/login');
            $this->info('2. Login with admin credentials');
            $this->info('3. Navigate to Drivers → Create New Driver');
            $this->info('4. Test driver creation');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error creating table: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }
}