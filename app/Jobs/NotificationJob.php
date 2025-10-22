<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\DriverMatchNotification;
use App\Mail\CompanyMatchNotification;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $recipient;
    protected $type;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($recipient, string $type, array $data = [])
    {
        $this->recipient = $recipient;
        $this->type = $type;
        $this->data = $data;
        $this->queue = 'notifications';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending notification', [
                'type' => $this->type,
                'recipient_type' => get_class($this->recipient),
                'recipient_id' => $this->recipient->id
            ]);

            switch ($this->type) {
                case 'match_assigned':
                    $this->sendDriverMatchNotification();
                    break;
                case 'driver_assigned':
                    $this->sendCompanyMatchNotification();
                    break;
                default:
                    Log::warning('Unknown notification type: ' . $this->type);
            }

        } catch (\Exception $e) {
            Log::error('Notification sending failed: ' . $e->getMessage(), [
                'type' => $this->type,
                'recipient_id' => $this->recipient->id ?? null
            ]);

            throw $e;
        }
    }

    /**
     * Send notification to driver about match assignment.
     */
    private function sendDriverMatchNotification(): void
    {
        if ($this->recipient->email) {
            Mail::to($this->recipient->email)
                ->send(new DriverMatchNotification($this->recipient, $this->data));
        }

        // TODO: Send SMS notification if phone number available
        // TODO: Send push notification if device token available
    }

    /**
     * Send notification to company about driver assignment.
     */
    private function sendCompanyMatchNotification(): void
    {
        if ($this->recipient->email) {
            Mail::to($this->recipient->email)
                ->send(new CompanyMatchNotification($this->recipient, $this->data));
        }

        // TODO: Send SMS notification if phone number available
        // TODO: Send push notification if device token available
    }
}
