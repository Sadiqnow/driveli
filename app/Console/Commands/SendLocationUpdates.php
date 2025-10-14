<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LocationMonitoringService;
use App\Models\DriverNormalized;
use App\Models\DriverLocationTracking;
use Carbon\Carbon;

class SendLocationUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:send-updates {--driver_id= : Specific driver ID to update} {--simulate : Simulate location updates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send location updates for active drivers and check for suspicious activity';

    protected $locationService;

    public function __construct(LocationMonitoringService $locationService)
    {
        parent::__construct();
        $this->locationService = $locationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $driverId = $this->option('driver_id');
        $simulate = $this->option('simulate');

        if ($simulate) {
            $this->simulateLocationUpdates($driverId);
            return Command::SUCCESS;
        }

        $this->info('Starting location update process...');

        // Get active drivers
        $query = DriverNormalized::where('is_current', true);

        if ($driverId) {
            $query->where('id', $driverId);
        }

        $drivers = $query->get();

        if ($drivers->isEmpty()) {
            $this->warn('No active drivers found.');
            return Command::SUCCESS;
        }

        $this->info("Processing {$drivers->count()} active drivers...");

        $bar = $this->output->createProgressBar($drivers->count());
        $bar->start();

        $suspiciousCount = 0;

        foreach ($drivers as $driver) {
            try {
                // Check for suspicious activity
                $isSuspicious = $this->locationService->detectSuspiciousActivity($driver->id);

                if ($isSuspicious) {
                    $this->locationService->sendOTPChallenge($driver->id, 'suspicious_location_activity');
                    $suspiciousCount++;
                    $this->newLine();
                    $this->warn("Suspicious activity detected for driver {$driver->id}");
                }

                // Clean up old location data (keep last 30 days)
                DriverLocationTracking::where('driver_id', $driver->id)
                    ->where('recorded_at', '<', Carbon::now()->subDays(30))
                    ->delete();

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error processing driver {$driver->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Location update process completed.");
        $this->info("Suspicious activities detected: {$suspiciousCount}");

        return Command::SUCCESS;
    }

    /**
     * Simulate location updates for testing
     */
    private function simulateLocationUpdates($driverId = null)
    {
        $this->info('Simulating location updates...');

        $query = DriverNormalized::where('is_current', true);

        if ($driverId) {
            $query->where('id', $driverId);
        }

        $drivers = $query->take(5)->get();

        foreach ($drivers as $driver) {
            // Simulate Lagos area coordinates with small variations
            $baseLat = 6.5244 + (mt_rand(-50, 50) / 1000);
            $baseLng = 3.3792 + (mt_rand(-50, 50) / 1000);

            DriverLocationTracking::create([
                'driver_id' => $driver->id,
                'latitude' => $baseLat,
                'longitude' => $baseLng,
                'accuracy' => mt_rand(5, 25),
                'device_info' => 'iPhone Simulator',
                'metadata' => json_encode([
                    'speed' => mt_rand(0, 60),
                    'heading' => mt_rand(0, 359),
                    'battery_level' => mt_rand(20, 100),
                    'network_type' => 'simulated',
                    'simulated' => true,
                ]),
                'recorded_at' => Carbon::now(),
            ]);

            $this->info("Simulated location for driver {$driver->id}");
        }

        $this->info('Location simulation completed.');
    }
}
