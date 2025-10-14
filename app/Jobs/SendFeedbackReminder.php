<?php

namespace App\Jobs;

use App\Models\DriverCompanyRelation;
use App\Services\EmploymentFeedbackService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendFeedbackReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Find pending feedback requests older than 7 days
        $pendingRelations = DriverCompanyRelation::whereNotNull('feedback_requested_at')
            ->whereNull('feedback_submitted_at')
            ->where('feedback_requested_at', '<', now()->subDays(7))
            ->where(function ($query) {
                $query->whereNull('last_reminder_sent_at')
                      ->orWhere('last_reminder_sent_at', '<', now()->subDays(3));
            })
            ->with(['driver', 'company'])
            ->get();

        $remindersSent = 0;

        foreach ($pendingRelations as $relation) {
            if ($this->sendReminder($relation)) {
                $remindersSent++;
            }
        }

        Log::info("Feedback reminder job completed", [
            'pending_relations_found' => $pendingRelations->count(),
            'reminders_sent' => $remindersSent
        ]);
    }

    /**
     * Send reminder for a specific relation
     *
     * @param DriverCompanyRelation $relation
     * @return bool
     */
    private function sendReminder(DriverCompanyRelation $relation): bool
    {
        $company = $relation->company;
        $driver = $relation->driver;

        if (!$company || !$company->email) {
            Log::warning("Cannot send reminder: Company email not found", [
                'company_id' => $relation->company_id,
                'relation_id' => $relation->id
            ]);
            return false;
        }

        $feedbackUrl = route('employment-feedback.form', ['token' => $relation->feedback_token]);

        try {
            // Send reminder email
            Mail::send('emails.feedback-reminder', [
                'company' => $company,
                'driver' => $driver,
                'feedbackUrl' => $feedbackUrl,
                'daysPending' => $relation->feedback_requested_at->diffInDays(now()),
            ], function ($message) use ($company, $driver) {
                $message->to($company->email)
                        ->subject('Reminder: Employment Reference Request - ' . $driver->full_name);
            });

            // Update last reminder sent timestamp
            $relation->update(['last_reminder_sent_at' => now()]);

            Log::info("Feedback reminder sent to company", [
                'company_id' => $company->id,
                'company_email' => $company->email,
                'driver_id' => $driver->id,
                'days_pending' => $relation->feedback_requested_at->diffInDays(now())
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send feedback reminder: " . $e->getMessage(), [
                'company_id' => $company->id,
                'driver_id' => $driver->id
            ]);

            return false;
        }
    }
}
