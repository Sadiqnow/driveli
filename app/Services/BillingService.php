<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyMatch;
use App\Models\CompanyInvoice;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BillingService
{
    public function createInvoice(Company $company, CompanyMatch $match, array $data = []): CompanyInvoice
    {
        Log::info('Creating invoice for company match', ['company_id' => $company->id, 'match_id' => $match->id]);

        $amount = $data['amount'] ?? $match->agreed_rate;
        $taxRate = 0.075; // 7.5% VAT
        $taxAmount = $amount * $taxRate;

        $invoice = $company->invoices()->create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'company_match_id' => $match->id,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'total_amount' => $amount + $taxAmount - ($data['discount_amount'] ?? 0),
            'currency' => 'NGN',
            'status' => 'draft',
            'due_date' => now()->addDays(30),
            'description' => $data['description'] ?? "Service for match #{$match->id}",
            'line_items' => $data['line_items'] ?? $this->generateLineItems($match),
            'notes' => $data['notes'] ?? null,
        ]);

        Log::info('Invoice created successfully', ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number]);

        return $invoice;
    }

    public function sendInvoice(CompanyInvoice $invoice): bool
    {
        $invoice->update(['status' => 'sent']);

        // TODO: Send email notification
        return true;
    }

    public function markAsPaid(CompanyInvoice $invoice, array $paymentData): bool
    {
        $invoice->markAsPaid(
            $paymentData['payment_method'] ?? null,
            $paymentData['transaction_reference'] ?? null
        );
        return true;
    }

    public function getOverdueInvoices(Company $company): \Illuminate\Database\Eloquent\Collection
    {
        return $company->invoices()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();
    }

    public function getCompanyBillingStats(Company $company): array
    {
        $invoices = $company->invoices;

        return [
            'total_invoiced' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->where('status', 'paid')->sum('total_amount'),
            'total_outstanding' => $invoices->where('status', '!=', 'paid')->sum('total_amount'),
            'overdue_amount' => $invoices->filter(function ($invoice) {
                return $invoice->isOverdue();
            })->sum('total_amount'),
            'invoice_count' => $invoices->count(),
            'paid_count' => $invoices->where('status', 'paid')->count(),
        ];
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . date('Y') . '-' . strtoupper(Str::random(8));
        } while (CompanyInvoice::where('invoice_number', $number)->exists());

        return $number;
    }

    private function generateLineItems(CompanyMatch $match): array
    {
        return [
            [
                'description' => 'Transportation Service',
                'quantity' => 1,
                'unit_price' => $match->agreed_rate,
                'total' => $match->agreed_rate,
            ]
        ];
    }

    public function processPaymentWebhook(array $webhookData): bool
    {
        Log::info('Processing payment webhook', ['transaction_reference' => $webhookData['transaction_reference'] ?? 'unknown']);

        // Handle payment gateway webhook
        $transactionRef = $webhookData['transaction_reference'];
        $invoice = CompanyInvoice::where('transaction_reference', $transactionRef)->first();

        if ($invoice && $webhookData['status'] === 'success') {
            $this->markAsPaid($invoice, [
                'payment_method' => $webhookData['payment_method'] ?? 'online',
                'transaction_reference' => $transactionRef,
            ]);
            Log::info('Payment processed successfully', ['invoice_id' => $invoice->id]);
            return true;
        }

        Log::warning('Payment webhook processing failed', ['transaction_reference' => $transactionRef, 'status' => $webhookData['status'] ?? 'unknown']);
        return false;
    }
}
