<?php

return [
    'enabled' => env('RAG_ENABLED', true),
    'debug'   => env('RAG_DEBUG', false),

    'embedding' => [
        'provider'   => env('RAG_EMBEDDING_PROVIDER', 'openai'),
        'model'      => env('RAG_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'dimensions' => (int) env('RAG_EMBEDDING_DIMENSIONS', 1536),
        'batch_size' => (int) env('RAG_EMBEDDING_BATCH_SIZE', 50),

        'providers' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'url'     => env('OPENAI_EMBEDDING_URL', 'https://api.openai.com/v1/embeddings'),
            ],
            'voyage' => [
                'api_key' => env('VOYAGE_API_KEY'),
                'url'     => 'https://api.voyageai.com/v1/embeddings',
                'model'   => env('VOYAGE_MODEL', 'voyage-3'),
            ],
            'local' => [
                'url' => env('LOCAL_EMBEDDING_URL', 'http://embedding:8080/embed'),
            ],
        ],
    ],

    'vector_store' => [
        'driver'         => 'elasticsearch',
        'url'            => env('ELASTICSEARCH_URL', 'http://elasticsearch:9200'),
        'index'          => env('RAG_ES_INDEX', 'consignments_rag'),
        'k'              => (int) env('RAG_TOP_K', 10),
        'num_candidates' => (int) env('RAG_NUM_CANDIDATES', 100),
    ],

    'hybrid' => [
        'bm25_weight'   => (float) env('RAG_BM25_WEIGHT', 0.5),
        'vector_weight' => (float) env('RAG_VECTOR_WEIGHT', 0.5),
        'rrf_k'         => 60,
    ],

    'retrieval' => [
        'top_k'          => (int) env('RAG_TOP_K', 10),
        'final_k'        => (int) env('RAG_FINAL_K', 3),
        'min_score'      => (float) env('RAG_MIN_SCORE', 0.0),
        'status_filter'  => ['approved', 'selling'],
    ],

    'llm' => [
        'provider'    => env('OPENAI_API_KEY') ? 'openai' : 'fallback',
        'model'       => env('OPENAI_API_MODEL', 'gpt-4o-mini'),
        'temperature' => (float) env('RAG_LLM_TEMPERATURE', 0.4),
        'max_tokens'  => (int) env('RAG_LLM_MAX_TOKENS', 800),
    ],
];
