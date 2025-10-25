<?php

namespace App\Notifications;

use App\Models\CompanyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyRequestCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $companyRequest;

    public function __construct(CompanyRequest $companyRequest)
    {
        $this->companyRequest = $companyRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'sms'];
    }

    public function toSms($notifiable)
    {
        return "Hello {$notifiable->name}! Your transport request has been created successfully. Request ID: {$this->companyRequest->request_id}. We'll notify you when drivers are matched.";
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Transport Request Created - ' . $this->companyRequest->request_id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your transport request has been successfully created.')
            ->line('**Request Details:**')
            ->line('Request ID: ' . $this->companyRequest->request_id)
            ->line('Pickup: ' . $this->companyRequest->pickup_location)
            ->line('Drop-off: ' . ($this->companyRequest->dropoff_location ?: 'Not specified'))
            ->line('Vehicle Type: ' . ucfirst($this->companyRequest->vehicle_type))
            ->line('Pickup Date: ' . $this->companyRequest->pickup_date->format('M d, Y H:i'))
            ->action('View Request', url('/company/requests/' . $this->companyRequest->id))
            ->line('We will notify you when drivers are matched to your request.')
            ->salutation('Best regards, Drivelink Team');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'request_created',
            'request_id' => $this->companyRequest->id,
            'request_number' => $this->companyRequest->request_id,
            'message' => 'Your transport request has been created successfully.',
            'action_url' => '/company/requests/' . $this->companyRequest->id,
        ];
    }
}
