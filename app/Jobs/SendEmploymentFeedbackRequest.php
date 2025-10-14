<?php

namespace App\Jobs;

use App\Models\DriverCompanyRelation;
use App\Models\AdminUser;
use App\Services\EmploymentFeedbackService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmploymentFeedbackRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // Retry after 1min, 5min, 10min

    protected $relationId;
    protected $adminId;

    /**
     * Create a new job instance.
     *
     * @param int $relationId
     * @param int $adminId
     */
    public function __construct(int $relationId, int $adminId)
    {
        $this->relationId = $relationId;
        $this->adminId = $adminId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EmploymentFeedbackService $feedbackService)
    {
        try {
            $relation = DriverCompanyRelation::find($this->relationId);
            $admin = AdminUser::find($this->adminId);

            if (!$relation || !$admin) {
                Log::error("SendEmploymentFeedbackRequest job failed: Relation or Admin not found", [
                    'relation_id' => $this->relationId,
                    'admin_id' => $this->adminId
                ]);
                return;
            }

            $success = $feedbackService->requestFeedback($relation, $admin);

            if (!$success) {
                Log::error("SendEmploymentFeedbackRequest job failed to send feedback request", [
                    'relation_id' => $this->relationId,
                    'admin_id' => $this->adminId
                ]);
            }

        } catch (\Exception $e) {
            Log::error("SendEmploymentFeedbackRequest job failed: " . $e->getMessage(), [
                'relation_id' => $this->relationId,
                'admin_id' => $this->adminId,
                'exception' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::critical("SendEmploymentFeedbackRequest job permanently failed", [
            'relation_id' => $this->relationId,
            'admin_id' => $this->adminId,
            'error' => $exception->getMessage()
        ]);

        // Could send notification to admin about failed feedback request
    }
}
