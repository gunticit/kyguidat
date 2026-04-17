<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VectorStoreService
{
    private string $url;
    private string $index;
    private int $dimensions;

    public function __construct()
    {
        $this->url        = rtrim(config('rag.vector_store.url'), '/');
        $this->index      = config('rag.vector_store.index');
        $this->dimensions = (int) config('rag.embedding.dimensions');
    }

    public function createIndex(bool $force = false): array
    {
        if ($this->indexExists()) {
            if (!$force) {
                return ['status' => 'exists'];
            }
            $this->deleteIndex();
        }

        $mapping = [
            'settings' => [
                'number_of_shards'   => 1,
                'number_of_replicas' => 0,
                'analysis' => [
                    'analyzer' => [
                        'vi_standard' => [
                            'type'      => 'custom',
                            'tokenizer' => 'standard',
                            'filter'    => ['lowercase', 'asciifolding'],
                        ],
                    ],
                ],
            ],
            'mappings' => [
                'properties' => [
                    'consignment_id'   => ['type' => 'long'],
                    'code'             => ['type' => 'keyword'],
                    'title'            => ['type' => 'text', 'analyzer' => 'vi_standard'],
                    'description'      => ['type' => 'text', 'analyzer' => 'vi_standard'],
                    'address'          => ['type' => 'text', 'analyzer' => 'vi_standard'],
                    'keywords'         => ['type' => 'text', 'analyzer' => 'vi_standard'],

                    'province'         => ['type' => 'keyword'],
                    'ward'             => ['type' => 'keyword'],
                    'status'           => ['type' => 'keyword'],
                    'category'         => ['type' => 'keyword'],
                    'price'            => ['type' => 'double'],
                    'land_types'       => ['type' => 'keyword'],
                    'land_directions'  => ['type' => 'keyword'],

                    'seo_url'          => ['type' => 'keyword'],
                    'featured_image'   => ['type' => 'keyword'],
                    'area_dimensions'  => ['type' => 'keyword'],
                    'floor_area'       => ['type' => 'double'],

                    'embedding_vector' => [
                        'type'       => 'dense_vector',
                        'dims'       => $this->dimensions,
                        'index'      => true,
                        'similarity' => 'cosine',
                    ],

                    'created_at'       => ['type' => 'date'],
                    'updated_at'       => ['type' => 'date'],
                ],
            ],
        ];

        $response = Http::timeout(30)->put("{$this->url}/{$this->index}", $mapping);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to create index: ' . $response->body());
        }

        return ['status' => 'created', 'index' => $this->index];
    }

    public function indexExists(): bool
    {
        $response = Http::timeout(10)->head("{$this->url}/{$this->index}");
        return $response->status() === 200;
    }

    public function deleteIndex(): void
    {
        Http::timeout(30)->delete("{$this->url}/{$this->index}");
    }

    public function upsert(int $consignmentId, array $document): bool
    {
        $response = Http::timeout(30)
            ->put("{$this->url}/{$this->index}/_doc/{$consignmentId}", $document);

        if (!$response->successful()) {
            Log::warning('VectorStore upsert failed', [
                'id'    => $consignmentId,
                'error' => $response->body(),
            ]);
            return false;
        }
        return true;
    }

    public function bulkUpsert(array $documents): array
    {
        if (empty($documents)) {
            return ['indexed' => 0, 'errors' => []];
        }

        $body = '';
        foreach ($documents as $consignmentId => $doc) {
            $body .= json_encode(['index' => ['_index' => $this->index, '_id' => $consignmentId]]) . "\n";
            $body .= json_encode($doc) . "\n";
        }

        $response = Http::timeout(120)
            ->withHeaders(['Content-Type' => 'application/x-ndjson'])
            ->withBody($body, 'application/x-ndjson')
            ->post("{$this->url}/_bulk");

        if (!$response->successful()) {
            throw new \RuntimeException('Bulk indexing failed: ' . $response->body());
        }

        $result = $response->json();
        $indexed = 0;
        $errors = [];
        foreach ($result['items'] ?? [] as $item) {
            $op = $item['index'] ?? $item['update'] ?? [];
            if (isset($op['error'])) {
                $errors[] = $op['error'];
            } else {
                $indexed++;
            }
        }
        return ['indexed' => $indexed, 'errors' => $errors];
    }

    public function delete(int $consignmentId): bool
    {
        $response = Http::timeout(10)
            ->delete("{$this->url}/{$this->index}/_doc/{$consignmentId}");
        return in_array($response->status(), [200, 404]);
    }

    /**
     * Hybrid search: BM25 (multi_match) + kNN (dense_vector cosine) with shared pre-filter.
     *
     * @param array  $queryVector  Embedding of user query
     * @param string $queryText    Raw user text (for BM25)
     * @param array  $filters      Hard filters: province, ward, min_price, max_price, land_types, land_directions, category, status
     * @param int    $size         Results to return
     * @return array                Hits with _source + _score
     */
    public function hybridSearch(
        array $queryVector,
        string $queryText,
        array $filters = [],
        int $size = 10
    ): array {
        $boolFilter = $this->buildFilters($filters);

        $body = [
            'size' => $size,
            'query' => [
                'bool' => [
                    'filter' => $boolFilter,
                    'should' => [
                        [
                            'multi_match' => [
                                'query'     => $queryText,
                                'fields'    => ['title^3', 'keywords^2', 'description', 'address^2'],
                                'type'      => 'best_fields',
                                'fuzziness' => 'AUTO',
                            ],
                        ],
                    ],
                    'minimum_should_match' => 0,
                ],
            ],
            'knn' => [
                'field'          => 'embedding_vector',
                'query_vector'   => $queryVector,
                'k'              => $size,
                'num_candidates' => (int) config('rag.vector_store.num_candidates'),
                'filter'         => ['bool' => ['filter' => $boolFilter]],
            ],
            '_source' => [
                'consignment_id', 'code', 'title', 'price', 'address', 'province', 'ward',
                'seo_url', 'featured_image', 'area_dimensions', 'land_types', 'land_directions',
                'category', 'floor_area',
            ],
        ];

        $response = Http::timeout(30)
            ->post("{$this->url}/{$this->index}/_search", $body);

        if (!$response->successful()) {
            Log::error('VectorStore hybrid search failed', ['error' => $response->body()]);
            return [];
        }

        $hits = $response->json('hits.hits') ?? [];
        return array_map(function ($hit) {
            return array_merge(
                $hit['_source'] ?? [],
                ['_score' => $hit['_score'] ?? 0]
            );
        }, $hits);
    }

    private function buildFilters(array $filters): array
    {
        $boolFilter = [];

        $status = $filters['status'] ?? config('rag.retrieval.status_filter');
        if (!empty($status)) {
            $boolFilter[] = ['terms' => ['status' => (array) $status]];
        }

        if (!empty($filters['province'])) {
            $boolFilter[] = ['term' => ['province' => $filters['province']]];
        }
        if (!empty($filters['ward'])) {
            $boolFilter[] = ['term' => ['ward' => $filters['ward']]];
        }
        if (!empty($filters['category'])) {
            $boolFilter[] = ['term' => ['category' => $filters['category']]];
        }

        $priceRange = [];
        if (!empty($filters['min_price'])) {
            $priceRange['gte'] = (float) $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $priceRange['lte'] = (float) $filters['max_price'];
        }
        if (!empty($priceRange)) {
            $boolFilter[] = ['range' => ['price' => $priceRange]];
        }

        if (!empty($filters['land_types']) && is_array($filters['land_types'])) {
            $boolFilter[] = ['terms' => ['land_types' => $filters['land_types']]];
        }
        if (!empty($filters['land_directions']) && is_array($filters['land_directions'])) {
            $boolFilter[] = ['terms' => ['land_directions' => $filters['land_directions']]];
        }

        return $boolFilter;
    }
}
