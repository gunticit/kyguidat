<?php

namespace App\Console\Commands;

use App\Services\VectorStoreService;
use Illuminate\Console\Command;

class CreateRagIndex extends Command
{
    protected $signature = 'rag:create-index {--force : Xoá và tạo lại index nếu đã tồn tại}';
    protected $description = 'Tạo Elasticsearch index cho RAG (consignments_rag)';

    public function handle(VectorStoreService $vectorStore): int
    {
        $force = (bool) $this->option('force');

        $this->info('Creating Elasticsearch index...');
        if ($force) {
            $this->warn('--force: index hiện tại (nếu có) sẽ bị xoá!');
        }

        try {
            $result = $vectorStore->createIndex($force);
            if ($result['status'] === 'exists') {
                $this->warn('Index đã tồn tại. Dùng --force để tạo lại.');
                return self::SUCCESS;
            }
            $this->info("✓ Created index '{$result['index']}' (dims=" . config('rag.embedding.dimensions') . ')');
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
