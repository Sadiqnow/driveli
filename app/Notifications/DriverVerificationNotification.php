<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $verificationData;

    public function __construct($verificationData)
    {
        $this->verificationData = $verificationData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // TODO: Configure notification channels
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Driver verification completed.')
            ->action('View Details', url('/verification/' . $this->verificationData['id']))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Driver verification completed',
            'data' => $this->verificationData,
        ];
    }
}
