<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handlePaystackWebhook(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Paystack webhook received', [
                'event' => $payload['event'] ?? 'unknown',
                'reference' => $payload['data']['reference'] ?? 'unknown',
            ]);

            $result = $this->paymentService->handleWebhook($payload);

            if ($result) {
                return response()->json(['status' => 'success'], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Webhook processing failed'], 400);
        } catch (\Exception $e) {
            Log::error('Paystack webhook processing exception', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    public function handleFlutterwaveWebhook(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Flutterwave webhook received', [
                'event' => $payload['event'] ?? 'unknown',
                'tx_ref' => $payload['data']['tx_ref'] ?? 'unknown',
            ]);

            // TODO: Implement Flutterwave webhook handling
            // For now, just log and return success

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Flutterwave webhook processing exception', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }
}
