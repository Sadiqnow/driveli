<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\AdminUser;
use App\Mail\AdminAlertNotification;

class AdminAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $message;
    protected $severity;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, string $severity = 'error', array $data = [])
    {
        $this->message = $message;
        $this->severity = $severity;
        $this->data = $data;
        $this->queue = 'alerts';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending admin alert', [
                'message' => $this->message,
                'severity' => $this->severity
            ]);

            // Get all super admin users
            $superAdmins = AdminUser::whereHas('roles', function($query) {
                $query->where('name', 'SuperAdmin');
            })->get();

            if ($superAdmins->isEmpty()) {
                Log::warning('No super admin users found for alert notification');
                return;
            }

            // Send email alerts to all super admins
            foreach ($superAdmins as $admin) {
                if ($admin->email) {
                    Mail::to($admin->email)
                        ->send(new AdminAlertNotification($admin, $this->message, $this->severity, $this->data));
                }
            }

            // TODO: Send SMS alerts if configured
            // TODO: Send push notifications if device tokens available

        } catch (\Exception $e) {
            Log::error('Admin alert sending failed: ' . $e->getMessage(), [
                'message' => $this->message,
                'severity' => $this->severity
            ]);

            throw $e;
        }
    }
}
