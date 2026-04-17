<?php

namespace App\Console\Commands;

use App\Models\Consignment;
use App\Services\EmbeddingService;
use App\Services\RAGRetrievalService;
use App\Services\VectorStoreService;
use Illuminate\Console\Command;

class EmbedConsignments extends Command
{
    protected $signature = 'chatbot:embed-all
        {--batch=50 : Số tin mỗi batch}
        {--force : Embed lại cả những tin đã embed}
        {--limit= : Giới hạn tổng số tin (dùng để test)}';

    protected $description = 'Backfill embedding cho tất cả consignments đã duyệt';

    public function handle(
        EmbeddingService $embedding,
        VectorStoreService $vectorStore,
        RAGRetrievalService $retrieval
    ): int {
        if (!$vectorStore->indexExists()) {
            $this->error('Index chưa tồn tại. Chạy: php artisan rag:create-index');
            return self::FAILURE;
        }

        $batch = (int) $this->option('batch');
        $force = (bool) $this->option('force');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $statuses = config('rag.retrieval.status_filter', ['approved', 'selling']);
        $query = Consignment::query()->whereIn('status', $statuses);

        if (!$force) {
            $query->whereNull('embedded_at');
        }
        if ($limit) {
            $query->limit($limit);
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('Không có consignment cần embed. (Dùng --force để embed lại.)');
            return self::SUCCESS;
        }

        $this->info("Sẽ embed {$total} consignments, batch size {$batch}...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        $query->chunk($batch, function ($consignments) use (
            &$success, &$failed, $bar, $embedding, $vectorStore, $retrieval
        ) {
            $texts = [];
            $items = [];
            foreach ($consignments as $c) {
                $text = $retrieval->buildConsignmentText($c);
                $texts[] = $text;
                $items[] = ['consignment' => $c, 'text' => $text];
            }

            try {
                $vectors = $embedding->embedBatch($texts);
                $bulkDocs = [];
                foreach ($items as $i => $item) {
                    $vector = $vectors[$i] ?? null;
                    if (!$vector) {
                        $failed++;
                        continue;
                    }

                    $c = $item['consignment'];
                    $bulkDocs[$c->id] = $retrieval->buildConsignmentDocument($c, $vector);
                }

                $result = $vectorStore->bulkUpsert($bulkDocs);
                $success += $result['indexed'];

                foreach ($items as $i => $item) {
                    if (!isset($vectors[$i])) {
                        continue;
                    }
                    $hash = hash('sha256', $item['text']);
                    $item['consignment']->update([
                        'embedding_hash' => $hash,
                        'embedded_at'    => now(),
                    ]);
                }

                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $err) {
                        $this->newLine();
                        $this->warn('  ES error: ' . json_encode($err));
                    }
                }
            } catch (\Throwable $e) {
                $failed += count($items);
                $this->newLine();
                $this->error('  Batch failed: ' . $e->getMessage());
            }

            $bar->advance(count($consignments));
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Indexed: {$success}");
        if ($failed > 0) {
            $this->warn("✗ Failed: {$failed}");
        }

        return self::SUCCESS;
    }
}
