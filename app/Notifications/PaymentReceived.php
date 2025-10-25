<?php

namespace App\Notifications;

use App\Models\CompanyInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invoice;

    public function __construct(CompanyInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment Received - Invoice ' . $this->invoice->invoice_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We have successfully received your payment for the invoice.')
            ->line('**Payment Details:**')
            ->line('Invoice Number: ' . $this->invoice->invoice_number)
            ->line('Amount Paid: ₦' . number_format($this->invoice->amount, 2))
            ->line('Payment Date: ' . $this->invoice->paid_at->format('M d, Y H:i'))
            ->line('Reference: ' . $this->invoice->payment_reference)
            ->action('View Invoice', url('/company/invoices/' . $this->invoice->id))
            ->line('Thank you for using Drivelink services!')
            ->salutation('Best regards, Drivelink Team');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment_received',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'message' => 'Payment has been received for your invoice.',
            'action_url' => '/company/invoices/' . $this->invoice->id,
        ];
    }
}
