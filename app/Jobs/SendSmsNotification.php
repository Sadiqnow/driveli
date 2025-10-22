<?php

namespace App\Jobs;

use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logId;
    protected $content;
    protected $options;

    /**
     * Create a new job instance.
     */
    public function __construct(int $logId, array $content, array $options = [])
    {
        $this->logId = $logId;
        $this->content = $content;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationTemplateService $notificationService): void
    {
        try {
            // Here you would integrate with your SMS service (Twilio, AWS SNS, etc.)
            // For now, we'll simulate sending

            $recipient = $this->options['recipient'] ?? '';
            $body = $this->content['body'] ?? '';

            // Simulate SMS sending
            Log::info("Sending SMS notification", [
                'log_id' => $this->logId,
                'recipient' => $recipient,
                'body_length' => strlen($body)
            ]);

            // In real implementation, you would use:
            // $twilio->messages->create($recipient, ['from' => $from, 'body' => $body]);

            // Update log as sent
            $notificationService->logNotification($this->logId, 'sent');

            // Simulate delivery (in real app, this would be via webhook)
            sleep(1); // Simulate network delay
            $notificationService->logNotification($this->logId, 'delivered');

        } catch (\Exception $e) {
            Log::error("SMS notification failed for log {$this->logId}: " . $e->getMessage());
            $notificationService->logNotification($this->logId, 'failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
