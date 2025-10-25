<?php

namespace App\Notifications;

use App\Models\CompanyMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverMatchFound extends Notification implements ShouldQueue
{
    use Queueable;

    protected $match;

    public function __construct(CompanyMatch $match)
    {
        $this->match = $match;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'sms'];
    }

    public function toSms($notifiable)
    {
        $request = $this->match->companyRequest;
        $driver = $this->match->driver;
        return "Hello {$notifiable->name}! Driver {$driver->name} matched to your request {$request->request_id}. Rate: ₦" . number_format($this->match->proposed_rate, 2) . ". Review now.";
    }

    public function toMail($notifiable)
    {
        $request = $this->match->companyRequest;
        $driver = $this->match->driver;

        return (new MailMessage)
            ->subject('Driver Match Found - ' . $request->request_id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! We found a driver match for your transport request.')
            ->line('**Match Details:**')
            ->line('Request ID: ' . $request->request_id)
            ->line('Driver: ' . $driver->name)
            ->line('Rating: ' . number_format($driver->rating ?? 0, 1) . ' stars')
            ->line('Experience: ' . ($driver->experience_years ?? 0) . ' years')
            ->line('Proposed Rate: ₦' . number_format($this->match->proposed_rate, 2))
            ->line('Match Score: ' . $this->match->match_score . '%')
            ->action('Review Match', url('/company/matches/' . $this->match->id))
            ->line('You can accept, reject, or negotiate the terms with the driver.')
            ->salutation('Best regards, Drivelink Team');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'driver_match',
            'match_id' => $this->match->id,
            'request_id' => $this->match->companyRequest->id,
            'request_number' => $this->match->companyRequest->request_id,
            'driver_name' => $this->match->driver->name,
            'proposed_rate' => $this->match->proposed_rate,
            'message' => 'A driver has been matched to your request.',
            'action_url' => '/company/matches/' . $this->match->id,
        ];
    }
}
