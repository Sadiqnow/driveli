<?php

namespace App\Services;

use App\Models\DriverCompanyRelation;
use App\Models\AdminUser;
use App\Models\Company;
use App\Jobs\SendEmploymentFeedbackRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Twilio\Rest\Client;

class EmploymentFeedbackService
{
    /**
     * Request employment feedback from a previous employer
     *
     * @param DriverCompanyRelation $relation
     * @param AdminUser $admin
     * @param bool $queue Whether to queue the job or send immediately
     * @return bool
     */
    public function requestFeedback(DriverCompanyRelation $relation, AdminUser $admin, bool $queue = false): bool
    {
        try {
            if ($queue) {
                // Queue the feedback request for background processing
                SendEmploymentFeedbackRequest::dispatch($relation->id, $admin->id);

                Log::info("Employment feedback request queued for driver {$relation->driver_id} from company {$relation->company_id}", [
                    'admin_id' => $admin->id,
                    'relation_id' => $relation->id
                ]);

                return true;
            }

            // Generate secure token
            $token = $relation->requestFeedback($admin);

            // Log the activity
            try {
                $ipAddress = app()->runningInConsole() ? null : request()->ip();
                $userAgent = app()->runningInConsole() ? null : request()->userAgent();

                \App\Models\ActivityLog::create([
                    'user_type' => get_class($admin),
                    'user_id' => $admin->id,
                    'action' => 'feedback_requested',
                    'description' => "Requested employment feedback for driver {$relation->driver->full_name} from company {$relation->company->name}",
                    'metadata' => json_encode([
                        'driver_id' => $relation->driver_id,
                        'company_id' => $relation->company_id,
                        'relation_id' => $relation->id,
                    ]),
                    'ip_address' => $ipAddress,
                ]);
            } catch (\Exception $e) {
                Log::warning("Failed to log activity: " . $e->getMessage());
            }

            // Send email/SMS notification
            $this->sendFeedbackRequestNotification($relation, $token);

            Log::info("Employment feedback requested for driver {$relation->driver_id} from company {$relation->company_id}", [
                'admin_id' => $admin->id,
                'token' => substr($token, 0, 8) . '...' // Log partial token for debugging
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to request employment feedback: " . $e->getMessage(), [
                'relation_id' => $relation->id,
                'admin_id' => $admin->id
            ]);

            return false;
        }
    }

    /**
     * Submit employment feedback
     *
     * @param string $token
     * @param array $feedbackData
     * @return DriverCompanyRelation|null
     */
    public function submitFeedback(string $token, array $feedbackData): ?DriverCompanyRelation
    {
        try {
            $relation = DriverCompanyRelation::where('feedback_token', $token)
                ->whereNotNull('feedback_requested_at')
                ->whereNull('feedback_submitted_at')
                ->first();

            if (!$relation) {
                Log::warning("Invalid or expired feedback token used", ['token' => substr($token, 0, 8) . '...']);
                return null;
            }

            $relation->submitFeedback($feedbackData);

            Log::info("Employment feedback submitted for driver {$relation->driver_id}", [
                'relation_id' => $relation->id,
                'performance_rating' => $feedbackData['performance_rating'] ?? null,
                'is_flagged' => $relation->is_flagged
            ]);

            return $relation;
        } catch (\Exception $e) {
            Log::error("Failed to submit employment feedback: " . $e->getMessage(), [
                'token' => substr($token, 0, 8) . '...'
            ]);

            return null;
        }
    }

    /**
     * Validate feedback token
     *
     * @param string $token
     * @return DriverCompanyRelation|null
     */
    public function validateToken(string $token): ?DriverCompanyRelation
    {
        return DriverCompanyRelation::where('feedback_token', $token)
            ->whereNotNull('feedback_requested_at')
            ->whereNull('feedback_submitted_at')
            ->with(['driver', 'company'])
            ->first();
    }

    /**
     * Send feedback request notification via email/SMS
     *
     * @param DriverCompanyRelation $relation
     * @param string $token
     * @return void
     */
    private function sendFeedbackRequestNotification(DriverCompanyRelation $relation, string $token): void
    {
        $company = $relation->company;
        $driver = $relation->driver;

        if (!$company || !$company->email) {
            Log::warning("Cannot send feedback request: Company email not found", [
                'company_id' => $relation->company_id,
                'relation_id' => $relation->id
            ]);
            return;
        }

        $feedbackUrl = route('employment-feedback.form', ['token' => $token]);

        // Send email notification
        try {
            Mail::send('emails.employment-feedback-request', [
                'company' => $company,
                'driver' => $driver,
                'feedbackUrl' => $feedbackUrl,
                'token' => $token,
            ], function ($message) use ($company, $driver) {
                $message->to($company->email)
                        ->subject('Employment Reference Request - ' . $driver->full_name);
            });

            Log::info("Feedback request email sent to company", [
                'company_id' => $company->id,
                'company_email' => $company->email,
                'driver_id' => $driver->id
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send feedback request email: " . $e->getMessage(), [
                'company_id' => $company->id,
                'driver_id' => $driver->id
            ]);
        }

        // Send SMS notification if enabled
        $this->sendSmsNotification($company, $feedbackUrl);
    }

    /**
     * Send SMS notification for feedback request
     *
     * @param Company $company
     * @param string $feedbackUrl
     * @return void
     */
    private function sendSmsNotification(Company $company, string $feedbackUrl): void
    {
        if (!config('services.twilio.sms_enabled') || !$company->phone) {
            return;
        }

        try {
            $twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $message = "Employment Reference Request: Please provide feedback for a former driver. Click: {$feedbackUrl}";

            $twilio->messages->create(
                $company->phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message
                ]
            );

            Log::info("Feedback request SMS sent to company", [
                'company_id' => $company->id,
                'company_phone' => $company->phone
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send feedback request SMS: " . $e->getMessage(), [
                'company_id' => $company->id
            ]);
        }
    }

    /**
     * Get feedback statistics for admin dashboard
     *
     * @return array
     */
    public function getFeedbackStats(): array
    {
        $totalRequested = DriverCompanyRelation::feedbackRequested()->count();
        $totalSubmitted = DriverCompanyRelation::feedbackSubmitted()->count();
        $totalPending = DriverCompanyRelation::pendingFeedback()->count();
        $totalFlagged = DriverCompanyRelation::flagged()->count();

        $responseRate = $totalRequested > 0 ? round(($totalSubmitted / $totalRequested) * 100, 2) : 0;

        return [
            'total_requested' => $totalRequested,
            'total_submitted' => $totalSubmitted,
            'total_pending' => $totalPending,
            'total_flagged' => $totalFlagged,
            'response_rate' => $responseRate,
        ];
    }

    /**
     * Get flagged drivers requiring review
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFlaggedDrivers()
    {
        return DriverCompanyRelation::with(['driver', 'company', 'feedbackRequestedBy'])
            ->flagged()
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Bulk request feedback for multiple relations
     *
     * @param array $relationIds
     * @param AdminUser $admin
     * @return array
     */
    public function bulkRequestFeedback(array $relationIds, AdminUser $admin): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($relationIds as $relationId) {
            $relation = DriverCompanyRelation::find($relationId);

            if (!$relation) {
                $results['failed']++;
                $results['errors'][] = "Relation {$relationId} not found";
                continue;
            }

            if ($this->requestFeedback($relation, $admin)) {
                $results['successful']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to request feedback for relation {$relationId}";
            }
        }

        return $results;
    }
}
