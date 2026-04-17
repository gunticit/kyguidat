<?php

namespace App\Jobs;

use App\Models\Consignment;
use App\Services\EmbeddingService;
use App\Services\RAGRetrievalService;
use App\Services\VectorStoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateConsignmentEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public int $consignmentId)
    {
    }

    public function handle(
        EmbeddingService $embedding,
        VectorStoreService $vectorStore,
        RAGRetrievalService $retrieval
    ): void {
        if (!config('rag.enabled', true)) {
            return;
        }

        $consignment = Consignment::find($this->consignmentId);
        if (!$consignment) {
            Log::info("Consignment #{$this->consignmentId} deleted before embedding");
            $vectorStore->delete($this->consignmentId);
            return;
        }

        $publicStatuses = config('rag.retrieval.status_filter', ['approved', 'selling']);
        if (!in_array($consignment->status, $publicStatuses)) {
            $vectorStore->delete($this->consignmentId);
            $consignment->update(['embedding_hash' => null, 'embedded_at' => null]);
            return;
        }

        $text = $retrieval->buildConsignmentText($consignment);
        $newHash = hash('sha256', $text);

        // Skip if content unchanged since last embed.
        if ($consignment->embedding_hash === $newHash) {
            return;
        }

        $vector = $embedding->embed($text);
        $document = $retrieval->buildConsignmentDocument($consignment, $vector);
        $success = $vectorStore->upsert($consignment->id, $document);

        if ($success) {
            $consignment->update([
                'embedding_hash' => $newHash,
                'embedded_at'    => now(),
            ]);
            Log::info("Embedded consignment #{$consignment->id}");
        } else {
            throw new \RuntimeException("Failed to upsert consignment #{$consignment->id}");
        }
    }
}
