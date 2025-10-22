<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyMatchNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($company, $data)
    {
        $this->company = $company;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Driver Assigned to Your Request - ' . $this->data['match_id'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new Content(
            view: 'emails.company-match-notification',
            with: [
                'company' => $this->company,
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
