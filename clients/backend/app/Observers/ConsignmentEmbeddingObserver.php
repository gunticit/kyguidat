<?php

namespace App\Observers;

use App\Jobs\GenerateConsignmentEmbedding;
use App\Models\Consignment;
use App\Services\VectorStoreService;
use Illuminate\Support\Facades\App;

class ConsignmentEmbeddingObserver
{
    public function created(Consignment $consignment): void
    {
        $this->dispatchIfEnabled($consignment);
    }

    public function updated(Consignment $consignment): void
    {
        // Only re-embed when a field that contributes to the text actually changed.
        $watchedFields = [
            'title', 'description', 'address', 'province', 'ward',
            'price', 'status', 'land_types', 'land_directions', 'keywords',
        ];

        if ($consignment->wasChanged($watchedFields)) {
            $this->dispatchIfEnabled($consignment);
        }
    }

    public function deleted(Consignment $consignment): void
    {
        App::make(VectorStoreService::class)->delete($consignment->id);
    }

    private function dispatchIfEnabled(Consignment $consignment): void
    {
        if (!config('rag.enabled', true)) {
            return;
        }

        GenerateConsignmentEmbedding::dispatch($consignment->id)->onQueue('rag');
    }
}
