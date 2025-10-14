<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\DriverRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\VerificationRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\LocationRepository;
use App\Repositories\PerformanceRepository;
use App\Repositories\BankingDetailRepository;
use App\Repositories\NextOfKinRepository;
use App\Repositories\EmploymentHistoryRepository;
use App\Repositories\PreferenceRepository;
use App\Repositories\RoleRepository;
use App\Repositories\PermissionRepository;

class TestRepositories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:repositories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all repository bindings and instantiation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing repository bindings...');

        $repositories = [
            'DriverRepository' => DriverRepository::class,
            'CompanyRepository' => CompanyRepository::class,
            'DocumentRepository' => DocumentRepository::class,
            'VerificationRepository' => VerificationRepository::class,
            'NotificationRepository' => NotificationRepository::class,
            'LocationRepository' => LocationRepository::class,
            'PerformanceRepository' => PerformanceRepository::class,
            'BankingDetailRepository' => BankingDetailRepository::class,
            'NextOfKinRepository' => NextOfKinRepository::class,
            'EmploymentHistoryRepository' => EmploymentHistoryRepository::class,
            'PreferenceRepository' => PreferenceRepository::class,
            'RoleRepository' => RoleRepository::class,
            'PermissionRepository' => PermissionRepository::class,
        ];

        $successCount = 0;
        $failCount = 0;

        foreach ($repositories as $name => $class) {
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
