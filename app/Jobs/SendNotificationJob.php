<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notifiable;
    protected $notificationType;
    protected $data;

    public function __construct($notifiable, string $notificationType, array $data = [])
    {
        $this->notifiable = $notifiable;
        $this->notificationType = $notificationType;
        $this->data = $data;
    }

    public function handle()
    {
        try {
            Log::info("Sending {$this->notificationType} notification", [
                'notifiable_type' => get_class($this->notifiable),
                'notifiable_id' => $this->notifiable->id,
                'data' => $this->data,
            ]);

            $notification = $this->createNotification();

            if ($notification) {
                $this->notifiable->notify($notification);
            }

        } catch (\Exception $e) {
            Log::error("Error sending notification: " . $e->getMessage(), [
                'notification_type' => $this->notificationType,
                'notifiable_id' => $this->notifiable->id,
            ]);
            throw $e;
        }
    }

    protected function createNotification()
    {
        switch ($this->notificationType) {
            case 'request_created':
                return new \App\Notifications\CompanyRequestCreated($this->data['request']);

            case 'matches_found':
                return new \App\Notifications\DriverMatchFound(
                    $this->data['request'],
                    $this->data['matches_count']
                );

            case 'invoice_generated':
                return new \App\Notifications\InvoiceGenerated($this->data['invoice']);

            case 'payment_received':
                return new \App\Notifications\PaymentReceived($this->data['invoice']);

            case 'matching_failed':
                // Create a generic notification for matching failure
                return new \App\Notifications\GenericNotification(
                    'Matching Failed',
                    'Unable to find suitable drivers for your request at this time.',
                    $this->data
                );

            default:
                Log::warning("Unknown notification type: {$this->notificationType}");
                return null;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("SendNotificationJob failed: " . $exception->getMessage(), [
            'notification_type' => $this->notificationType,
            'notifiable_id' => $this->notifiable->id,
        ]);
    }
}
