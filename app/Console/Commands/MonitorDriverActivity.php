<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriverNormalized;
use App\Jobs\DriverPingMonitor;
use Illuminate\Support\Facades\Log;

class MonitorDriverActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:monitor-activity {--driver_id= : Specific driver ID to monitor} {--interval=5 : Ping interval in minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor driver activity and detect app uninstalls or ping failures';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $driverId = $this->option('driver_id');
        $interval = (int) $this->option('interval');

        $this->info('Starting driver activity monitoring...');

        // Get active drivers
        $query = DriverNormalized::where('is_current', true);

        if ($driverId) {
            $query->where('id', $driverId);
        }

        $drivers = $query->get();

        if ($drivers->isEmpty()) {
            $this->warn('No active drivers found to monitor.');
            return Command::SUCCESS;
        }

        $this->info("Monitoring {$drivers->count()} active drivers for activity...");

        $bar = $this->output->createProgressBar($drivers->count());
        $bar->start();

        $alertsTriggered = 0;

        foreach ($drivers as $driver) {
            try {
                // Dispatch ping monitor job for each driver
                DriverPingMonitor::dispatch($driver->id, $interval);

                $alertsTriggered++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error monitoring driver {$driver->id}: {$e->getMessage()}");
                Log::error("Driver activity monitoring failed for driver {$driver->id}", [
                    'exception' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Driver activity monitoring completed.");
        $this->info("Ping monitors dispatched: {$alertsTriggered}");

        return Command::SUCCESS;
    }
}
