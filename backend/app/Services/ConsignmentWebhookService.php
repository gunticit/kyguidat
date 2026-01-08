<?php

namespace App\Services;

use App\Models\Consignment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsignmentWebhookService
{
    /**
     * Available webhook events
     */
    public const EVENTS = [
        'consignment.created',
        'consignment.updated',
        'consignment.status_changed',
        'consignment.approved',
        'consignment.rejected',
        'consignment.sold',
        'consignment.cancelled',
    ];

    /**
     * Dispatch webhook for a consignment event
     * 
     * @param string $event
     * @param Consignment $consignment
     * @param array $additionalData
     * @return void
     */
    public function dispatch(string $event, Consignment $consignment, array $additionalData = []): void
    {
        if (!in_array($event, self::EVENTS)) {
            Log::warning("Unknown webhook event: {$event}");
            return;
        }

        $webhooks = $this->getRegisteredWebhooks();
        
        foreach ($webhooks as $webhookId => $webhook) {
            if (!in_array($event, $webhook['events'])) {
                continue;
            }

            $payload = [
                'event' => $event,
                'data' => array_merge([
                    'consignment_id' => $consignment->id,
                    'code' => $consignment->code,
                    'title' => $consignment->title,
                    'status' => $consignment->status,
                    'price' => $consignment->price,
                    'address' => $consignment->address,
                    'user_id' => $consignment->user_id,
                    'created_at' => $consignment->created_at?->toIso8601String(),
                    'updated_at' => $consignment->updated_at?->toIso8601String(),
                ], $additionalData),
                'timestamp' => now()->toIso8601String(),
                'webhook_id' => $webhookId,
            ];

            $this->sendAsync($webhook['url'], $payload, $webhook['secret'] ?? '');
        }
    }

    /**
     * Dispatch event when consignment is created
     */
    public function dispatchCreated(Consignment $consignment): void
    {
        $this->dispatch('consignment.created', $consignment);
    }

    /**
     * Dispatch event when consignment is updated
     */
    public function dispatchUpdated(Consignment $consignment, array $changedFields = []): void
    {
        $this->dispatch('consignment.updated', $consignment, [
            'changed_fields' => $changedFields,
        ]);
    }

    /**
     * Dispatch event when consignment status changes
     */
    public function dispatchStatusChanged(Consignment $consignment, string $oldStatus, string $newStatus): void
    {
        $eventMap = [
            Consignment::STATUS_APPROVED => 'consignment.approved',
            Consignment::STATUS_REJECTED => 'consignment.rejected',
            Consignment::STATUS_SOLD => 'consignment.sold',
            Consignment::STATUS_CANCELLED => 'consignment.cancelled',
        ];

        // Dispatch general status changed event
        $this->dispatch('consignment.status_changed', $consignment, [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        // Dispatch specific status event
        if (isset($eventMap[$newStatus])) {
            $this->dispatch($eventMap[$newStatus], $consignment, [
                'old_status' => $oldStatus,
            ]);
        }
    }

    /**
     * Get all registered webhooks
     */
    public function getRegisteredWebhooks(): array
    {
        return Cache::get('registered_webhooks', []);
    }

    /**
     * Send webhook asynchronously
     * In production, this should use a queue job
     */
    private function sendAsync(string $url, array $payload, string $secret): void
    {
        // In production, dispatch this to a queue
        // dispatch(new SendConsignmentWebhook($url, $payload, $secret));

        // For now, send synchronously in a try-catch to avoid blocking
        try {
            $this->send($url, $payload, $secret);
        } catch (\Exception $e) {
            Log::error("Failed to send webhook to {$url}: " . $e->getMessage(), [
                'payload' => $payload,
            ]);
        }
    }

    /**
     * Send webhook request
     */
    private function send(string $url, array $payload, string $secret): array
    {
        $jsonPayload = json_encode($payload);
        $signature = $secret ? hash_hmac('sha256', $jsonPayload, $secret) : '';

        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $payload['event'] ?? 'unknown',
            'X-Webhook-Timestamp' => $payload['timestamp'] ?? now()->toIso8601String(),
        ];

        if ($signature) {
            $headers['X-Webhook-Signature'] = $signature;
        }

        Log::info("Sending webhook to {$url}", [
            'event' => $payload['event'] ?? 'unknown',
            'consignment_id' => $payload['data']['consignment_id'] ?? null,
        ]);

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($url, $payload);

        $result = [
            'success' => $response->successful(),
            'status_code' => $response->status(),
            'response' => $response->json() ?? $response->body(),
        ];

        if (!$response->successful()) {
            Log::warning("Webhook failed: {$url}", $result);
        }

        return $result;
    }
}
