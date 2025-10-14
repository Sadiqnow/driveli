<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DriverService;
use App\Services\CompanyService;

class TestServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test service instantiation with repository dependencies';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing service instantiation...');

        $services = [
            'DriverService' => DriverService::class,
            'CompanyService' => CompanyService::class,
        ];

        $successCount = 0;
        $failCount = 0;

        foreach ($services as $name => $class) {
            try {
                $instance = app()->make($class);
                $this->info("✓ {$name} loaded successfully");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("✗ {$name} failed: " . $e->getMessage());
                $failCount++;
            }
        }

        $this->info("\nSummary:");
        $this->info("Successful: {$successCount}");
        $this->info("Failed: {$failCount}");

        return $failCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
