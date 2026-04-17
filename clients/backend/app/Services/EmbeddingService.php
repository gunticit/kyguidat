<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $provider;
    private string $model;
    private int $dimensions;
    private array $providerConfig;

    public function __construct()
    {
        $this->provider       = config('rag.embedding.provider');
        $this->model          = config('rag.embedding.model');
        $this->dimensions     = (int) config('rag.embedding.dimensions');
        $this->providerConfig = config("rag.embedding.providers.{$this->provider}", []);
    }

    public function embed(string $text): array
    {
        $vectors = $this->embedBatch([$text]);
        return $vectors[0] ?? [];
    }

    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $texts = array_map(fn($t) => $this->normalizeText($t), $texts);

        try {
            return match ($this->provider) {
                'openai' => $this->embedWithOpenAI($texts),
                'voyage' => $this->embedWithVoyage($texts),
                'local'  => $this->embedWithLocal($texts),
                default  => throw new \InvalidArgumentException("Unknown embedding provider: {$this->provider}"),
            };
        } catch (\Throwable $e) {
            Log::error('EmbeddingService failed', [
                'provider' => $this->provider,
                'error'    => $e->getMessage(),
                'count'    => count($texts),
            ]);
            throw $e;
        }
    }

    public function getDimensions(): int
    {
        return $this->dimensions;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    private function normalizeText(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', trim($text));
        // OpenAI caps at 8191 tokens. 1 token ≈ 0.75 Vietnamese words. Cap at 6000 chars for safety.
        return mb_substr($text, 0, 6000);
    }

    private function embedWithOpenAI(array $texts): array
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer ' . ($this->providerConfig['api_key'] ?? ''),
                'Content-Type'  => 'application/json',
            ])
            ->post($this->providerConfig['url'], [
                'model' => $this->model,
                'input' => $texts,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI embedding failed: ' . $response->body());
        }

        $data = $response->json();
        return collect($data['data'] ?? [])
            ->sortBy('index')
            ->pluck('embedding')
            ->values()
            ->toArray();
    }

    private function embedWithVoyage(array $texts): array
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer ' . ($this->providerConfig['api_key'] ?? ''),
                'Content-Type'  => 'application/json',
            ])
            ->post($this->providerConfig['url'], [
                'model'      => $this->providerConfig['model'] ?? 'voyage-3',
                'input'      => $texts,
                'input_type' => 'document',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Voyage embedding failed: ' . $response->body());
        }

        $data = $response->json();
        return collect($data['data'] ?? [])
            ->sortBy('index')
            ->pluck('embedding')
            ->values()
            ->toArray();
    }

    private function embedWithLocal(array $texts): array
    {
        // HuggingFace Text Embeddings Inference API
        $response = Http::timeout(60)
            ->post($this->providerConfig['url'], [
                'inputs' => $texts,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Local embedding failed: ' . $response->body());
        }

        return $response->json();
    }
}
