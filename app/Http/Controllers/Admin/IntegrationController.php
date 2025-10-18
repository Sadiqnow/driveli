<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IntegrationController extends Controller
{
    protected IntegrationService $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Display the integrations dashboard
     */
    public function index()
    {
        $integrations = $this->integrationService->getAvailableIntegrations();
        $health = $this->integrationService->getIntegrationHealth();

        return view('admin.integrations.index', compact('integrations', 'health'));
    }

    /**
     * Get integration data via AJAX
     */
    public function data(Request $request): JsonResponse
    {
        $integrations = $this->integrationService->getAvailableIntegrations();

        return response()->json($integrations);
    }

    /**
     * Configure an integration
     */
    public function configure(Request $request, string $integration): JsonResponse
    {
        try {
            $config = $request->validate([
                'enabled' => 'boolean',
                'api_key' => 'nullable|string',
                'secret_key' => 'nullable|string',
                'public_key' => 'nullable|string',
                'client_id' => 'nullable|string',
                'client_secret' => 'nullable|string',
                'access_token' => 'nullable|string',
                'refresh_token' => 'nullable|string',
                'webhook_url' => 'nullable|url',
                'from_number' => 'nullable|string',
                'domain' => 'nullable|string',
                'measurement_id' => 'nullable|string',
                'token' => 'nullable|string',
                'write_key' => 'nullable|string',
                'app_id' => 'nullable|string',
                'username' => 'nullable|string',
                'password' => 'nullable|string',
                'partner_id' => 'nullable|string',
                'sid' => 'nullable|string',
            ]);

            $success = $this->integrationService->configureIntegration($integration, $config);

            if ($success) {
                Log::info("Integration configured successfully", [
                    'integration' => $integration,
                    'user_id' => auth()->id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => ucfirst($integration) . ' integration configured successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to configure integration'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Integration configuration failed", [
                'integration' => $integration,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Configuration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test integration connectivity
     */
    public function test(Request $request, string $integration): JsonResponse
    {
        try {
            // Get current status
            $integrations = $this->integrationService->getAvailableIntegrations();
            $status = 'unknown';

            // Find the integration in the nested array
            foreach ($integrations as $category => $categoryIntegrations) {
                if (isset($categoryIntegrations[$integration])) {
                    $status = $categoryIntegrations[$integration]['status'];
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'status' => $status,
                'integration' => $integration,
                'tested_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error("Integration test failed", [
                'integration' => $integration,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get integration usage statistics
     */
    public function usage(Request $request, string $integration): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $usage = $this->integrationService->getIntegrationUsage($integration, $period);

            return response()->json([
                'success' => true,
                'integration' => $integration,
                'usage' => $usage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get usage data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync data with integration
     */
    public function sync(Request $request, string $integration): JsonResponse
    {
        try {
            $syncType = $request->get('sync_type', 'full');

            $result = $this->integrationService->syncWithIntegration($integration, $syncType);

            Log::info("Integration sync completed", [
                'integration' => $integration,
                'sync_type' => $syncType,
                'result' => $result,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ? 'Sync completed successfully' : 'Sync failed',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error("Integration sync failed", [
                'integration' => $integration,
                'sync_type' => $syncType ?? 'unknown',
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get integration health status
     */
    public function health(Request $request): JsonResponse
    {
        try {
            $health = $this->integrationService->getIntegrationHealth();

            return response()->json([
                'success' => true,
                'health' => $health,
                'checked_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get health status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get integration configuration template
     */
    public function configTemplate(Request $request, string $integration): JsonResponse
    {
        $templates = [
            'flutterwave' => [
                'enabled' => false,
                'public_key' => '',
                'secret_key' => '',
                'webhook_secret' => '',
            ],
            'paystack' => [
                'enabled' => false,
                'public_key' => '',
                'secret_key' => '',
                'webhook_secret' => '',
            ],
            'stripe' => [
                'enabled' => false,
                'public_key' => '',
                'secret_key' => '',
                'webhook_secret' => '',
            ],
            'twilio' => [
                'enabled' => false,
                'sid' => '',
                'token' => '',
                'from_number' => '',
            ],
            'sendchamp' => [
                'enabled' => false,
                'public_key' => '',
                'secret_key' => '',
            ],
            'mailgun' => [
                'enabled' => false,
                'domain' => '',
                'secret' => '',
                'endpoint' => '',
            ],
            'veriff' => [
                'enabled' => false,
                'api_key' => '',
                'secret_key' => '',
            ],
            'jumio' => [
                'enabled' => false,
                'api_token' => '',
                'api_secret' => '',
            ],
            'smile_identity' => [
                'enabled' => false,
                'partner_id' => '',
                'api_key' => '',
            ],
            'google_maps' => [
                'enabled' => false,
                'api_key' => '',
            ],
            'here_maps' => [
                'enabled' => false,
                'api_key' => '',
                'app_id' => '',
            ],
            'mapbox' => [
                'enabled' => false,
                'access_token' => '',
            ],
            'google_analytics' => [
                'enabled' => false,
                'measurement_id' => '',
            ],
            'mixpanel' => [
                'enabled' => false,
                'token' => '',
            ],
            'segment' => [
                'enabled' => false,
                'write_key' => '',
            ],
            'hubspot' => [
                'enabled' => false,
                'api_key' => '',
            ],
            'salesforce' => [
                'enabled' => false,
                'client_id' => '',
                'client_secret' => '',
                'username' => '',
                'password' => '',
            ],
            'zoho_crm' => [
                'enabled' => false,
                'client_id' => '',
                'client_secret' => '',
                'refresh_token' => '',
            ],
        ];

        return response()->json([
            'success' => true,
            'template' => $templates[$integration] ?? [],
            'integration' => $integration
        ]);
    }

    /**
     * Webhook endpoints for integrations
     */
    public function webhook(Request $request, string $integration)
    {
        try {
            Log::info("Webhook received for {$integration}", [
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
                'ip' => $request->ip(),
            ]);

            // Process webhook based on integration
            switch ($integration) {
                case 'flutterwave':
                    return $this->processFlutterwaveWebhook($request);
                case 'paystack':
                    return $this->processPaystackWebhook($request);
                case 'stripe':
                    return $this->processStripeWebhook($request);
                default:
                    Log::warning("Unhandled webhook for {$integration}");
                    return response()->json(['status' => 'ignored']);
            }

        } catch (\Exception $e) {
            Log::error("Webhook processing failed for {$integration}", [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Process Flutterwave webhook
     */
    private function processFlutterwaveWebhook(Request $request)
    {
        // Verify webhook signature
        $secret = config('services.flutterwave.webhook_secret');
        $signature = $request->header('verif-hash');

        if (!$signature || !hash_equals($secret, $signature)) {
            Log::warning('Invalid Flutterwave webhook signature');
            return response()->json(['status' => 'invalid_signature'], 401);
        }

        $payload = $request->all();

        // Process based on event type
        switch ($payload['event'] ?? '') {
            case 'charge.completed':
                // Handle successful payment
                Log::info('Flutterwave payment completed', ['data' => $payload['data']]);
                break;
            case 'transfer.completed':
                // Handle successful transfer
                Log::info('Flutterwave transfer completed', ['data' => $payload['data']]);
                break;
            default:
                Log::info('Unhandled Flutterwave webhook event', ['event' => $payload['event'] ?? 'unknown']);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Process Paystack webhook
     */
    private function processPaystackWebhook(Request $request)
    {
        // Paystack uses HMAC SHA512 for signature verification
        $payload = $request->getContent();
        $secret = config('services.paystack.secret_key');
        $signature = $request->header('x-paystack-signature');

        $computedSignature = hash_hmac('sha512', $payload, $secret);

        if (!hash_equals($computedSignature, $signature)) {
            Log::warning('Invalid Paystack webhook signature');
            return response()->json(['status' => 'invalid_signature'], 401);
        }

        $data = json_decode($payload, true);

        // Process based on event type
        switch ($data['event'] ?? '') {
            case 'charge.success':
                // Handle successful payment
                Log::info('Paystack payment successful', ['data' => $data['data']]);
                break;
            case 'transfer.success':
                // Handle successful transfer
                Log::info('Paystack transfer successful', ['data' => $data['data']]);
                break;
            default:
                Log::info('Unhandled Paystack webhook event', ['event' => $data['event'] ?? 'unknown']);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Process Stripe webhook
     */
    private function processStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Invalid Stripe payload');
            return response()->json(['status' => 'invalid_payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Invalid Stripe signature');
            return response()->json(['status' => 'invalid_signature'], 401);
        }

        // Process based on event type
        switch ($event->type) {
            case 'payment_intent.succeeded':
                // Handle successful payment
                Log::info('Stripe payment succeeded', ['data' => $event->data]);
                break;
            case 'transfer.created':
                // Handle transfer creation
                Log::info('Stripe transfer created', ['data' => $event->data]);
                break;
            default:
                Log::info('Unhandled Stripe webhook event', ['type' => $event->type]);
        }

        return response()->json(['status' => 'processed']);
    }
}
