<?php

namespace App\Services;

use App\Models\Consignment;
use Illuminate\Support\Facades\Log;

class RAGRetrievalService
{
    public function __construct(
        private EmbeddingService $embedding,
        private VectorStoreService $vectorStore,
    ) {
    }

    /**
     * Retrieve top-K consignments relevant to the query.
     */
    public function retrieve(string $query, array $filters = [], ?int $topK = null): array
    {
        $topK = $topK ?? (int) config('rag.retrieval.top_k', 10);

        try {
            $queryVector = $this->embedding->embed($query);
            if (empty($queryVector)) {
                Log::warning('RAG: empty query vector, falling back to BM25 only');
                return $this->bm25OnlyFallback($query, $filters, $topK);
            }

            $results = $this->vectorStore->hybridSearch($queryVector, $query, $filters, $topK);
            $results = $this->filterByMinScore($results);
            $results = $this->diversify($results);

            return array_slice($results, 0, $topK);
        } catch (\Throwable $e) {
            Log::error('RAGRetrievalService error', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return $this->bm25OnlyFallback($query, $filters, $topK);
        }
    }

    /**
     * Build concatenated text (with labels) for embedding a single consignment.
     * Labels preserve semantic context — "Giá: 2 tỷ" means more than "2 tỷ" alone.
     */
    public function buildConsignmentText(Consignment $c): string
    {
        $landTypes      = $this->safeJsonDecodeAsList($c->land_types);
        $landDirections = $this->safeJsonDecodeAsList($c->land_directions);

        $parts = [
            'Tiêu đề: ' . ($c->title ?? ''),
            'Mô tả: ' . strip_tags((string) ($c->description ?? '')),
            'Địa chỉ: ' . trim(($c->address ?? '') . ', ' . ($c->ward ?? '') . ', ' . ($c->province ?? ''), ', '),
            'Loại BĐS: ' . ($c->category ?? '') . ($landTypes ? ' – ' . implode(', ', $landTypes) : ''),
            'Diện tích: ' . ($c->area_dimensions ?? ''),
            'Giá: ' . $this->formatPriceForEmbedding($c->price),
            'Hướng: ' . implode(', ', $landDirections),
            'Từ khoá: ' . ($c->keywords ?? ''),
        ];

        return implode("\n", array_filter($parts, fn($p) => trim(str_replace([':'], '', $p)) !== ''));
    }

    /**
     * Build an ES document from a Consignment + its vector.
     */
    public function buildConsignmentDocument(Consignment $c, array $vector): array
    {
        return [
            'consignment_id'   => $c->id,
            'code'             => $c->code,
            'title'            => $c->title,
            'description'      => strip_tags((string) ($c->description ?? '')),
            'address'          => $c->address,
            'province'         => $c->province,
            'ward'             => $c->ward,
            'status'           => $c->status,
            'category'         => $c->category,
            'price'            => (float) ($c->price ?? 0),
            'land_types'       => $this->safeJsonDecodeAsList($c->land_types),
            'land_directions'  => $this->safeJsonDecodeAsList($c->land_directions),
            'keywords'         => $c->keywords,
            'seo_url'          => $c->seo_url,
            'featured_image'   => $c->featured_image,
            'area_dimensions'  => $c->area_dimensions,
            'floor_area'       => (float) ($c->floor_area ?? 0),
            'embedding_vector' => $vector,
            'created_at'       => optional($c->created_at)->toIso8601String(),
            'updated_at'       => optional($c->updated_at)->toIso8601String(),
        ];
    }

    private function filterByMinScore(array $results): array
    {
        $min = (float) config('rag.retrieval.min_score', 0);
        if ($min <= 0) {
            return $results;
        }
        return array_values(array_filter($results, fn($r) => ($r['_score'] ?? 0) >= $min));
    }

    /**
     * Cap each (province, priceBucket) pair to max 2 results to avoid near-duplicates in top-K.
     */
    private function diversify(array $results): array
    {
        if (count($results) <= 3) {
            return $results;
        }

        $seen = [];
        $diverse = [];
        $overflow = [];

        foreach ($results as $r) {
            $key = ($r['province'] ?? '') . '|' . $this->priceBucket((float) ($r['price'] ?? 0));
            $seen[$key] = $seen[$key] ?? 0;

            if ($seen[$key] < 2) {
                $diverse[] = $r;
                $seen[$key]++;
            } else {
                $overflow[] = $r;
            }
        }

        return array_merge($diverse, $overflow);
    }

    private function priceBucket(float $price): int
    {
        if ($price < 500_000_000)   return 1;
        if ($price < 1_000_000_000) return 2;
        if ($price < 2_000_000_000) return 3;
        if ($price < 5_000_000_000) return 4;
        return 5;
    }

    private function bm25OnlyFallback(string $query, array $filters, int $topK): array
    {
        try {
            return $this->vectorStore->hybridSearch(
                array_fill(0, $this->embedding->getDimensions(), 0.0),
                $query,
                $filters,
                $topK
            );
        } catch (\Throwable $e) {
            Log::error('BM25 fallback also failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function safeJsonDecodeAsList($value): array
    {
        if (empty($value)) return [];
        if (is_array($value)) return $value;
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function formatPriceForEmbedding(?float $price): string
    {
        if (!$price) return 'Liên hệ';
        if ($price >= 1_000_000_000) return number_format($price / 1_000_000_000, 2) . ' tỷ';
        if ($price >= 1_000_000)     return number_format($price / 1_000_000, 0) . ' triệu';
        return number_format($price, 0) . ' đ';
    }
}
