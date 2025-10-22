<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverMatchNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $driver;
    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($driver, $data)
    {
        $this->driver = $driver;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'New Job Match Assigned - ' . $this->data['match_id'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            view: 'emails.driver-match-notification',
            with: [
                'driver' => $this->driver,
                'data' => $this->data
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
}
