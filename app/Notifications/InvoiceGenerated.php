<?php

namespace App\Notifications;

use App\Models\CompanyInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceGenerated extends Notification implements ShouldQueue
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
        $request = $this->invoice->companyMatch->companyRequest;

        return (new MailMessage)
            ->subject('Invoice Generated - ' . $this->invoice->invoice_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('An invoice has been generated for your completed transport request.')
            ->line('**Invoice Details:**')
            ->line('Invoice Number: ' . $this->invoice->invoice_number)
            ->line('Request ID: ' . $request->request_id)
            ->line('Amount: ₦' . number_format($this->invoice->amount, 2))
            ->line('Due Date: ' . $this->invoice->due_date->format('M d, Y'))
            ->action('View Invoice', url('/company/invoices/' . $this->invoice->id))
            ->action('Download PDF', url('/company/invoices/' . $this->invoice->id . '/download'))
            ->line('Please ensure payment is made before the due date to avoid any penalties.')
            ->salutation('Best regards, Drivelink Team');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'invoice_generated',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'due_date' => $this->invoice->due_date->toDateString(),
            'message' => 'A new invoice has been generated for your transport request.',
            'action_url' => '/company/invoices/' . $this->invoice->id,
        ];
    }
}
