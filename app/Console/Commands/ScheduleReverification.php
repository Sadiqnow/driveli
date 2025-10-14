<?php

namespace App\Console\Commands;

use App\Jobs\ReverificationSchedulerJob;
use Illuminate\Console\Command;

class ScheduleReverification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verification:schedule-reverification {--force : Force run even if recently executed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule reverification for expired and failed verifications';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting reverification scheduler...');

        // Dispatch the reverification scheduler job
        ReverificationSchedulerJob::dispatch();

        $this->info('Reverification scheduler job dispatched successfully.');

        return Command::SUCCESS;
    }
}
