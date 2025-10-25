<?php

namespace App\Http\Controllers\API;

use App\Helpers\DrivelinkHelper;
use App\Http\Controllers\Controller;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request)
    {
        // List webhook logs or configurations
        // This is a placeholder for webhook management
        return DrivelinkHelper::respondJson('success', 'Webhook endpoint', ['message' => 'Webhook management endpoint']);
    }

    public function paystack(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Paystack webhook received', ['event' => $payload['event'] ?? 'unknown']);

            // Verify webhook signature (implement based on Paystack docs)
            // $this->verifyPaystackSignature($request);

            if ($payload['event'] === 'charge.success') {
                $data = $payload['data'];
                $webhookData = [
                    'transaction_reference' => $data['reference'],
                    'status' => 'success',
                    'payment_method' => 'paystack',
                    'amount' => $data['amount'] / 100, // Convert from kobo
                ];

                $processed = $this->billingService->processPaymentWebhook($webhookData);

                if ($processed) {
                    return response()->json(['status' => 'success'], 200);
                }
            }

            return response()->json(['status' => 'ignored'], 200);

        } catch (\Exception $e) {
            Log::error('Paystack webhook processing error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    public function flutterwave(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Flutterwave webhook received', ['event' => $payload['event'] ?? 'unknown']);

            // Verify webhook signature (implement based on Flutterwave docs)
            // $this->verifyFlutterwaveSignature($request);

            if ($payload['event'] === 'charge.completed' && $payload['data']['status'] === 'successful') {
                $data = $payload['data'];
                $webhookData = [
                    'transaction_reference' => $data['tx_ref'],
                    'status' => 'success',
                    'payment_method' => 'flutterwave',
                    'amount' => $data['amount'],
                ];

                $processed = $this->billingService->processPaymentWebhook($webhookData);

                if ($processed) {
                    return response()->json(['status' => 'success'], 200);
                }
            }

            return response()->json(['status' => 'ignored'], 200);

        } catch (\Exception $e) {
            Log::error('Flutterwave webhook processing error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    private function verifyPaystackSignature(Request $request)
    {
        // Implement Paystack webhook signature verification
        // $signature = $request->header('X-Paystack-Signature');
        // $secret = config('services.paystack.secret');
        // Implement HMAC verification
    }

    private function verifyFlutterwaveSignature(Request $request)
    {
        // Implement Flutterwave webhook signature verification
        // $signature = $request->header('verif-hash');
        // $secret = config('services.flutterwave.secret');
        // Implement verification
    }
}
