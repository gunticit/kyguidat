<?php

namespace App\Http\Controllers;

use App\Models\Consignment;
use App\Events\ConsignmentCreated;
use App\Events\ConsignmentUpdated;
use App\Events\ConsignmentStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class ConsignmentWebhookController extends Controller
{
    /**
     * Secret key for webhook authentication
     */
    private string $webhookSecret;

    public function __construct()
    {
        $this->webhookSecret = config('services.webhook.consignment_secret', env('CONSIGNMENT_WEBHOOK_SECRET', ''));
    }

    /**
     * Handle incoming webhook for consignment events
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // Verify webhook signature
        if (!$this->verifySignature($request)) {
            Log::warning('Webhook signature verification failed', [
                'ip' => $request->ip(),
                'payload' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data', []);

        Log::info("Consignment webhook received: {$event}", $data);

        return match ($event) {
            'consignment.created' => $this->handleCreated($data),
            'consignment.updated' => $this->handleUpdated($data),
            'consignment.status_changed' => $this->handleStatusChanged($data),
            'consignment.approved' => $this->handleApproved($data),
            'consignment.rejected' => $this->handleRejected($data),
            'consignment.sold' => $this->handleSold($data),
            'consignment.cancelled' => $this->handleCancelled($data),
            default => response()->json([
                'success' => false,
                'message' => "Unknown event: {$event}",
            ], 400),
        };
    }

    /**
     * Register webhook endpoint
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'events' => 'required|array',
            'events.*' => 'string|in:consignment.created,consignment.updated,consignment.status_changed,consignment.approved,consignment.rejected,consignment.sold,consignment.cancelled',
            'secret' => 'nullable|string|min:16',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Store webhook configuration (in production, save to database)
        $webhookConfig = [
            'url' => $request->url,
            'events' => $request->events,
            'secret' => $request->secret ?? bin2hex(random_bytes(16)),
            'created_at' => now()->toIso8601String(),
        ];

        // For demo, store in cache. In production, use a webhooks table
        $webhooks = Cache::get('registered_webhooks', []);
        $webhookId = uniqid('wh_');
        $webhooks[$webhookId] = $webhookConfig;
        Cache::put('registered_webhooks', $webhooks, now()->addYear());

        return response()->json([
            'success' => true,
            'message' => 'Webhook registered successfully',
            'data' => [
                'webhook_id' => $webhookId,
                'url' => $webhookConfig['url'],
                'events' => $webhookConfig['events'],
                'secret' => $webhookConfig['secret'],
            ],
        ], 201);
    }

    /**
     * List registered webhooks
     * 
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $webhooks = Cache::get('registered_webhooks', []);

        return response()->json([
            'success' => true,
            'data' => array_map(function ($id, $config) {
                return [
                    'id' => $id,
                    'url' => $config['url'],
                    'events' => $config['events'],
                    'created_at' => $config['created_at'],
                ];
            }, array_keys($webhooks), array_values($webhooks)),
        ]);
    }

    /**
     * Delete a webhook
     * 
     * @param string $webhookId
     * @return JsonResponse
     */
    public function delete(string $webhookId): JsonResponse
    {
        $webhooks = Cache::get('registered_webhooks', []);

        if (!isset($webhooks[$webhookId])) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook not found',
            ], 404);
        }

        unset($webhooks[$webhookId]);
        Cache::put('registered_webhooks', $webhooks, now()->addYear());

        return response()->json([
            'success' => true,
            'message' => 'Webhook deleted successfully',
        ]);
    }

    /**
     * Test webhook endpoint
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function test(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'webhook_id' => 'required|string',
            'event' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $webhooks = Cache::get('registered_webhooks', []);
        $webhookId = $request->webhook_id;

        if (!isset($webhooks[$webhookId])) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook not found',
            ], 404);
        }

        $webhook = $webhooks[$webhookId];
        
        // Send test payload
        $testPayload = [
            'event' => $request->event,
            'data' => [
                'id' => 1,
                'code' => 'KG_TEST_001',
                'title' => 'Test Consignment',
                'status' => 'approved',
                'test' => true,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            $response = $this->sendWebhook($webhook['url'], $testPayload, $webhook['secret']);
            
            return response()->json([
                'success' => true,
                'message' => 'Test webhook sent successfully',
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test webhook: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle consignment created event
     */
    private function handleCreated(array $data): JsonResponse
    {
        // Process created event - could trigger notifications, sync with external systems, etc.
        Log::info('Processing consignment.created', $data);

        // Dispatch event if needed
        if (isset($data['consignment_id'])) {
            $consignment = Consignment::find($data['consignment_id']);
            if ($consignment) {
                // event(new ConsignmentCreated($consignment));
            }
        }

        // Clear cache
        Cache::forget('public_consignments_*');

        return response()->json([
            'success' => true,
            'message' => 'Consignment created event processed',
        ]);
    }

    /**
     * Handle consignment updated event
     */
    private function handleUpdated(array $data): JsonResponse
    {
        Log::info('Processing consignment.updated', $data);

        if (isset($data['consignment_id'])) {
            Cache::forget("public_consignment_{$data['consignment_id']}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Consignment updated event processed',
        ]);
    }

    /**
     * Handle consignment status changed event
     */
    private function handleStatusChanged(array $data): JsonResponse
    {
        Log::info('Processing consignment.status_changed', $data);

        if (isset($data['consignment_id'])) {
            Cache::forget("public_consignment_{$data['consignment_id']}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Consignment status changed event processed',
        ]);
    }

    /**
     * Handle consignment approved event
     */
    private function handleApproved(array $data): JsonResponse
    {
        Log::info('Processing consignment.approved', $data);

        // Could send notification to user, etc.

        return response()->json([
            'success' => true,
            'message' => 'Consignment approved event processed',
        ]);
    }

    /**
     * Handle consignment rejected event
     */
    private function handleRejected(array $data): JsonResponse
    {
        Log::info('Processing consignment.rejected', $data);

        return response()->json([
            'success' => true,
            'message' => 'Consignment rejected event processed',
        ]);
    }

    /**
     * Handle consignment sold event
     */
    private function handleSold(array $data): JsonResponse
    {
        Log::info('Processing consignment.sold', $data);

        if (isset($data['consignment_id'])) {
            Cache::forget("public_consignment_{$data['consignment_id']}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Consignment sold event processed',
        ]);
    }

    /**
     * Handle consignment cancelled event
     */
    private function handleCancelled(array $data): JsonResponse
    {
        Log::info('Processing consignment.cancelled', $data);

        if (isset($data['consignment_id'])) {
            Cache::forget("public_consignment_{$data['consignment_id']}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Consignment cancelled event processed',
        ]);
    }

    /**
     * Verify webhook signature
     */
    private function verifySignature(Request $request): bool
    {
        // Skip verification if no secret is configured
        if (empty($this->webhookSecret)) {
            return true;
        }

        $signature = $request->header('X-Webhook-Signature');
        if (!$signature) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Send webhook to registered URL
     */
    private function sendWebhook(string $url, array $payload, string $secret): array
    {
        $jsonPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonPayload, $secret);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Webhook-Signature: ' . $signature,
                'X-Webhook-Event: ' . ($payload['event'] ?? 'unknown'),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception($error);
        }

        return [
            'status_code' => $httpCode,
            'response' => json_decode($response, true) ?? $response,
        ];
    }
}
