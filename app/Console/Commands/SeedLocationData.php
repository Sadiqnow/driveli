<?php

namespace App\Console\Commands;

use App\Models\State;
use App\Models\LocalGovernment;
use Database\Seeders\NigerianStatesLGASeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedLocationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivelink:seed-locations {--force : Force reseed even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Nigerian states and local government areas data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        $this->info('Checking current location data...');
        
        $stateCount = State::count();
        $lgaCount = LocalGovernment::count();
        
        $this->info("Current data: {$stateCount} states, {$lgaCount} LGAs");
        
        if ($stateCount > 0 && $lgaCount > 0 && !$force) {
            $this->warn('Location data already exists. Use --force to reseed.');
            
            if (!$this->confirm('Do you want to continue anyway?')) {
                return Command::SUCCESS;
            }
        }
        
        try {
            $this->info('Running NigerianStatesLGASeeder...');
            
            $seeder = new NigerianStatesLGASeeder();
            
            // Use reflection to set the command property
            $reflection = new \ReflectionClass($seeder);
            $commandProperty = $reflection->getProperty('command');
            $commandProperty->setAccessible(true);
            $commandProperty->setValue($seeder, $this);
            
            $seeder->run();
            
            $newStateCount = State::count();
            $newLgaCount = LocalGovernment::count();
            
            $this->info("Seeding completed!");
            $this->info("Final data: {$newStateCount} states, {$newLgaCount} LGAs");
            
            // Test with a sample query
            $lagosState = State::where('name', 'Lagos')->first();
            if ($lagosState) {
                $lagosLgas = LocalGovernment::where('state_id', $lagosState->id)->count();
                $this->info("Test query - Lagos has {$lagosLgas} LGAs");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to seed location data: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            return Command::FAILURE;
        }
    }
}