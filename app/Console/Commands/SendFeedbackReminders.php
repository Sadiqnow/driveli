<?php

namespace App\Console\Commands;

use App\Jobs\SendFeedbackReminder;
use App\Models\DriverCompanyRelation;
use Illuminate\Console\Command;

class SendFeedbackReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedback:send-reminders {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to companies that haven\'t submitted employment feedback';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for pending feedback requests that need reminders...');

        // Get pending feedback requests that are 7+ days old and haven't had a reminder in the last 3 days
        $pendingFeedback = DriverCompanyRelation::pendingFeedback()
            ->where('feedback_requested_at', '<=', now()->subDays(7))
            ->where(function ($query) {
                $query->whereNull('last_reminder_sent_at')
                      ->orWhere('last_reminder_sent_at', '<=', now()->subDays(3));
            })
            ->with(['driver', 'company'])
            ->get();

        if ($pendingFeedback->isEmpty()) {
            $this->info('No pending feedback requests need reminders at this time.');
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingFeedback->count()} pending feedback requests that need reminders.");

        if ($this->option('dry-run')) {
            $this->line('DRY RUN - The following reminders would be sent:');
            foreach ($pendingFeedback as $relation) {
                $this->line("- {$relation->company->name} for driver {$relation->driver->full_name} (requested {$relation->feedback_requested_at->diffForHumans()})");
            }
            return Command::SUCCESS;
        }

        $sentCount = 0;
        foreach ($pendingFeedback as $relation) {
            try {
                // Dispatch the reminder job
                SendFeedbackReminder::dispatch($relation);

                // Update the last reminder sent timestamp
                $relation->update(['last_reminder_sent_at' => now()]);

                $sentCount++;
                $this->line("Queued reminder for {$relation->company->name}");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for relation ID {$relation->id}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully queued {$sentCount} feedback reminders.");
        return Command::SUCCESS;
    }
}
