<?php

namespace App\Services;

use App\Models\CompanyInvoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $paystackSecretKey;
    protected $paystackBaseUrl;

    public function __construct()
    {
        $this->paystackSecretKey = config('services.paystack.secret');
        $this->paystackBaseUrl = 'https://api.paystack.co';
    }

    public function initializePayment(CompanyInvoice $invoice, array $data = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->post($this->paystackBaseUrl . '/transaction/initialize', [
                'email' => $invoice->company->email,
                'amount' => $invoice->amount * 100, // Paystack expects amount in kobo
                'reference' => $invoice->invoice_number . '_' . time(),
                'callback_url' => config('app.url') . '/company/payment/callback',
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'company_id' => $invoice->company_id,
                    'custom_fields' => [
                        [
                            'display_name' => 'Invoice Number',
                            'variable_name' => 'invoice_number',
                            'value' => $invoice->invoice_number,
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();

                // Store payment reference
                $invoice->update([
                    'payment_reference' => $result['data']['reference'],
                ]);

                return [
                    'success' => true,
                    'authorization_url' => $result['data']['authorization_url'],
                    'reference' => $result['data']['reference'],
                ];
            }

            Log::error('Paystack payment initialization failed', [
                'invoice_id' => $invoice->id,
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack payment initialization error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment service temporarily unavailable',
            ];
        }
    }

    public function verifyPayment(string $reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            ])->get($this->paystackBaseUrl . '/transaction/verify/' . $reference);

            if ($response->successful()) {
                $result = $response->json();

                if ($result['data']['status'] === 'success') {
                    return [
                        'success' => true,
                        'data' => $result['data'],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Payment verification failed',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack payment verification error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification service temporarily unavailable',
            ];
        }
    }

    public function handleWebhook(array $payload)
    {
        try {
            // Verify webhook signature
            $signature = request()->header('X-Paystack-Signature');
            $computedSignature = hash_hmac('sha512', request()->getContent(), $this->paystackSecretKey);

            if (!hash_equals($computedSignature, $signature)) {
                Log::warning('Invalid Paystack webhook signature');
                return false;
            }

            $event = $payload['event'];

            if ($event === 'charge.success') {
                $reference = $payload['data']['reference'];

                // Extract invoice ID from reference
                $parts = explode('_', $reference);
                $invoiceNumber = $parts[0];

                $invoice = CompanyInvoice::where('invoice_number', $invoiceNumber)->first();

                if ($invoice && $invoice->status === 'pending') {
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_reference' => $reference,
                    ]);

                    // Send payment confirmation notification
                    $invoice->company->notify(new \App\Notifications\PaymentReceived($invoice));

                    Log::info('Invoice payment processed successfully', [
                        'invoice_id' => $invoice->id,
                        'reference' => $reference,
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Paystack webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }
    }

    public function getBanks()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            ])->get($this->paystackBaseUrl . '/bank');

            if ($response->successful()) {
                return $response->json()['data'];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching banks from Paystack', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function createTransferRecipient(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->post($this->paystackBaseUrl . '/transferrecipient', [
                'type' => 'nuban',
                'name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'bank_code' => $data['bank_code'],
                'currency' => 'NGN',
            ]);

            if ($response->successful()) {
                return $response->json()['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error creating transfer recipient', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return null;
        }
    }

    public function initiateTransfer(string $recipientCode, float $amount, string $reason = '')
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->post($this->paystackBaseUrl . '/transfer', [
                'source' => 'balance',
                'amount' => $amount * 100,
                'recipient' => $recipientCode,
                'reason' => $reason ?: 'Driver payment',
            ]);

            if ($response->successful()) {
                return $response->json()['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error initiating transfer', [
                'error' => $e->getMessage(),
                'recipient' => $recipientCode,
                'amount' => $amount,
            ]);

            return null;
        }
    }
}
