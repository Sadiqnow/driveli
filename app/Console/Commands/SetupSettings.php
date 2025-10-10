<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Models\Setting;
use App\Services\SettingsService;

class SetupSettings extends Command
{
    protected $signature = 'settings:setup {--seed : Seed default settings}';
    protected $description = 'Setup the settings system';

    public function handle()
    {
        $this->info('Setting up DriveLink Settings System...');
        
        // Check if settings table exists
        if (!Schema::hasTable('settings')) {
            $this->info('Settings table not found. Running migration...');
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_01_09_000000_create_settings_table.php',
                '--force' => true
            ]);
            $this->info('✓ Settings table created successfully');
        } else {
            $this->info('✓ Settings table already exists');
        }
        
        // Test settings service
        try {
            $settingsService = app(SettingsService::class);
            $this->info('✓ Settings service instantiated successfully');
            
            // Test basic functionality
            $testKey = 'test_' . time();
            $settingsService->set($testKey, 'test_value', 'string', 'test');
            $retrieved = $settingsService->get($testKey, null, 'test');
            
            if ($retrieved === 'test_value') {
                $this->info('✓ Settings storage and retrieval working correctly');
                
                // Clean up test data
                Setting::where('key', $testKey)->where('group', 'test')->delete();
            } else {
                $this->error('✗ Settings storage/retrieval test failed');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('✗ Error testing settings service: ' . $e->getMessage());
            return 1;
        }
        
        // Seed default settings if requested
        if ($this->option('seed')) {
            $this->info('Seeding default settings...');
            Artisan::call('db:seed', ['--class' => 'SettingsSeeder']);
            $this->info('✓ Default settings seeded successfully');
        }
        
        // Test helper functions
        if (function_exists('settings')) {
            $this->info('✓ Settings helper functions available');
        } else {
            $this->warn('⚠ Settings helper functions not loaded. You may need to run "composer dump-autoload"');
        }
        
        $this->info('');
        $this->info('🎉 Settings system setup completed successfully!');
        $this->info('');
        $this->info('You can now:');
        $this->info('• Access settings via Settings facade: Settings::get("key")');
        $this->info('• Use helper functions: settings("key") or app_setting("key")');
        $this->info('• Manage settings via Super Admin panel at /admin/superadmin/settings');
        
        return 0;
    }
}