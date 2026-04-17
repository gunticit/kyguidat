<?php

namespace App\Services;

use App\Models\Province;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private string $openaiApiKey;
    private string $openaiApiUrl;
    private string $openaiModel;
    private string $siteUrl;
    private bool $ragEnabled;

    public function __construct(
        private RAGRetrievalService $retrieval,
    ) {
        $this->openaiApiKey = env('OPENAI_API_KEY', '');
        $this->openaiApiUrl = env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
        $this->openaiModel  = config('rag.llm.model', env('OPENAI_API_MODEL', 'gpt-4o-mini'));
        $this->siteUrl      = env('APP_URL_SANDAT', 'https://khodat.com');
        $this->ragEnabled   = (bool) config('rag.enabled', true);
    }

    /**
     * @return array{is_property_query: bool, reply: string|null, consignments: array, debug?: array}
     */
    public function handleMessage(string $text): array
    {
        try {
            // RAG kill switch → chỉ answer general, không gọi retrieval/embedding.
            if (!$this->ragEnabled) {
                return [
                    'is_property_query' => false,
                    'reply'             => $this->answerGeneralQuestion($text),
                    'consignments'      => [],
                ];
            }

            $intent = $this->extractIntent($text);

            if (!$intent['is_property_query']) {
                return [
                    'is_property_query' => false,
                    'reply'             => $this->answerGeneralQuestion($text),
                    'consignments'      => [],
                ];
            }

            $filters = $this->intentToFilters($intent);
            $retrieved = $this->retrieval->retrieve($text, $filters, (int) config('rag.retrieval.top_k'));

            $finalK = (int) config('rag.retrieval.final_k', 3);
            $recommended = array_slice($retrieved, 0, $finalK);

            $reply = $this->generateAnswer($text, $recommended, $intent);

            $response = [
                'is_property_query' => true,
                'reply'             => $reply,
                'consignments'      => array_map(fn($r) => $this->formatConsignmentForClient($r), $recommended),
            ];

            if (config('rag.debug')) {
                $response['debug'] = [
                    'intent'          => $intent,
                    'filters'         => $filters,
                    'retrieved_count' => count($retrieved),
                    'scores'          => array_map(fn($r) => $r['_score'] ?? 0, $retrieved),
                ];
            }

            return $response;
        } catch (\Throwable $e) {
            Log::error('Chatbot error', [
                'error' => $e->getMessage(),
                'text'  => $text,
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'is_property_query' => false,
                'reply'             => 'Dạ xin lỗi anh/chị, hệ thống đang gặp trục trặc. Anh/chị vui lòng thử lại sau hoặc gọi hotline 1900 8041 ạ.',
                'consignments'      => [],
            ];
        }
    }

    /**
     * LLM classify + extract filters + rewrite query for better vector retrieval.
     */
    private function extractIntent(string $text): array
    {
        $provincesRef = $this->getProvincesReference();

        $prompt = <<<PROMPT
Bạn là chatbot bất động sản Khodat.com. Phân tích tin nhắn khách hàng.

DANH SÁCH TỈNH/THÀNH VÀ XÃ/PHƯỜNG:
{$provincesRef}

Trả về JSON THUẦN, không markdown:
{
  "is_property_query": true/false,
  "province": "tên tỉnh/thành khớp danh sách" | null,
  "ward": "tên xã/phường" | null,
  "min_price": số nguyên VND | null,
  "max_price": số nguyên VND | null,
  "property_type": "đất nền" | "đất tái định cư" | "đất sào" | "đất rẫy" | "bất động sản nghỉ dưỡng" | "đất phân lô dự án" | "chung cư" | "đang sử dụng kinh doanh" | null,
  "direction": "Đông" | "Tây" | "Nam" | "Bắc" | "Đông Nam" | "Đông Bắc" | "Tây Nam" | "Tây Bắc" | null,
  "rewritten_query": "câu truy vấn được viết lại giàu ngữ nghĩa hơn để dùng cho vector search"
}

Quy đổi giá:
- "500 triệu" = 500000000
- "1 tỷ" = 1000000000
- "tầm X" → max_price = X * 1.2 (buffer 20%)
- "dưới X" → max_price = X
- "trên X" → min_price = X
- "X đến Y" → min_price = X, max_price = Y

Ví dụ rewritten_query:
- User: "đất nghỉ dưỡng tầm 2 tỷ Lâm Đồng view đẹp"
- rewritten_query: "bất động sản nghỉ dưỡng Lâm Đồng view đẹp có cảnh thiên nhiên thư giãn farmstay homestay"

is_property_query = true nếu khách đang TÌM, MUA, HỎI VỀ BĐS cụ thể.
is_property_query = false nếu chỉ chào hỏi / hỏi thông tin công ty / ký gửi bán / pháp lý chung.

Tin nhắn:
---
{$text}
---
PROMPT;

        $response = $this->callLLM([
            ['role' => 'user', 'content' => $prompt],
        ], temperature: 0.1, maxTokens: 400);

        $defaults = [
            'is_property_query' => false,
            'province'          => null,
            'ward'              => null,
            'min_price'         => null,
            'max_price'         => null,
            'property_type'     => null,
            'direction'         => null,
            'rewritten_query'   => $text,
        ];

        if (!$response) {
            return $defaults;
        }

        $response = preg_replace('/^```(?:json)?\s*/m', '', trim($response));
        $response = preg_replace('/\s*```$/m', '', $response);
        $parsed = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Chatbot: intent extraction JSON invalid', ['response' => $response]);
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($parsed, $defaults));
    }

    private function intentToFilters(array $intent): array
    {
        $filters = [];
        if (!empty($intent['province']))  $filters['province']  = $intent['province'];
        if (!empty($intent['ward']))      $filters['ward']      = $intent['ward'];
        if (!empty($intent['min_price'])) $filters['min_price'] = $intent['min_price'];
        if (!empty($intent['max_price'])) $filters['max_price'] = $intent['max_price'];

        if (!empty($intent['property_type'])) {
            $filters['land_types'] = [$intent['property_type']];
        }
        if (!empty($intent['direction'])) {
            $filters['land_directions'] = [$intent['direction']];
        }
        return $filters;
    }

    private function generateAnswer(string $originalQuery, array $recommendedConsignments, array $intent): string
    {
        $persona      = $this->loadPersona();
        $contextBlock = $this->buildContextBlock($recommendedConsignments);
        $hasResults   = !empty($recommendedConsignments);

        $userPrompt = $hasResults
            ? <<<PROMPT
Dưới đây là {$this->countWord($recommendedConsignments)} sản phẩm có trong kho Khodat.com phù hợp nhất với nhu cầu khách:

{$contextBlock}

Câu hỏi của khách: "{$originalQuery}"

Hãy trả lời tự nhiên theo persona. Chọn 2-3 sản phẩm phù hợp nhất từ danh sách TRÊN, mô tả ngắn gọn mỗi sản phẩm 1 câu tại sao phù hợp. LUÔN gắn link đúng từ trường `Link` đã cho. KHÔNG được bịa ra sản phẩm ngoài danh sách. Kết thúc bằng 1 câu hỏi gợi mở.
PROMPT
            : <<<PROMPT
Câu hỏi của khách: "{$originalQuery}"

Hiện tại trong kho Khodat.com chưa có sản phẩm nào khớp chính xác tiêu chí trên. Hãy trả lời chân thành theo persona: nói thẳng là chưa có tin phù hợp, hỏi khách có thể nới lỏng tiêu chí nào (ngân sách / khu vực / loại hình), và gợi ý để lại số điện thoại để được thông báo khi có tin mới. Gửi kèm link tìm kiếm chung: {$this->siteUrl}/tim-kiem
PROMPT;

        $reply = $this->callLLM([
            ['role' => 'system', 'content' => $persona],
            ['role' => 'user',   'content' => $userPrompt],
        ], temperature: (float) config('rag.llm.temperature', 0.4), maxTokens: (int) config('rag.llm.max_tokens', 800));

        return $reply ?? 'Dạ em xin lỗi, em chưa thể trả lời ngay lúc này. Anh/chị thử lại giúp em ạ.';
    }

    private function answerGeneralQuestion(string $text): string
    {
        $persona = $this->loadPersona();

        $reply = $this->callLLM([
            ['role' => 'system', 'content' => $persona],
            ['role' => 'user',   'content' => $text],
        ], temperature: 0.5, maxTokens: 500);

        return $reply ?? 'Dạ em xin lỗi, anh/chị vui lòng gọi 1900 8041 để được hỗ trợ nhanh nhất ạ.';
    }

    private function buildContextBlock(array $consignments): string
    {
        if (empty($consignments)) return '(không có sản phẩm)';

        $lines = [];
        foreach ($consignments as $i => $c) {
            $n       = $i + 1;
            $title   = $c['title'] ?? 'Không có tiêu đề';
            $price   = $this->formatPrice($c['price'] ?? 0);
            $address = $c['address'] ?? (($c['ward'] ?? '') . ', ' . ($c['province'] ?? ''));
            $area    = $c['area_dimensions'] ?? '';
            $types   = !empty($c['land_types']) ? implode(', ', (array) $c['land_types']) : '';
            $url     = $this->buildConsignmentUrl($c);

            $lines[] = "[{$n}] {$title}"
                . "\n    Giá: {$price} | Diện tích: {$area}"
                . "\n    Địa chỉ: {$address}"
                . ($types ? "\n    Loại: {$types}" : '')
                . "\n    Link: {$url}";
        }
        return implode("\n\n", $lines);
    }

    private function formatConsignmentForClient(array $c): array
    {
        return [
            'id'             => $c['consignment_id'] ?? null,
            'title'          => $c['title'] ?? '',
            'price'          => $c['price'] ?? 0,
            'price_display'  => $this->formatPrice($c['price'] ?? 0),
            'address'        => $c['address'] ?? '',
            'province'       => $c['province'] ?? '',
            'ward'           => $c['ward'] ?? '',
            'area'           => $c['area_dimensions'] ?? '',
            'featured_image' => $c['featured_image'] ?? '',
            'url'            => $this->buildConsignmentUrl($c),
            'score'          => round((float) ($c['_score'] ?? 0), 3),
        ];
    }

    private function buildConsignmentUrl(array $c): string
    {
        if (!empty($c['seo_url'])) {
            return rtrim($this->siteUrl, '/') . '/dat/' . $c['seo_url'];
        }
        if (!empty($c['consignment_id'])) {
            return rtrim($this->siteUrl, '/') . '/dat/' . $c['consignment_id'];
        }
        return $this->siteUrl;
    }

    private function formatPrice(?float $price): string
    {
        if (!$price) return 'Liên hệ';
        if ($price >= 1_000_000_000) {
            $ty = $price / 1_000_000_000;
            return ($ty == floor($ty)) ? number_format($ty) . ' tỷ' : number_format($ty, 1) . ' tỷ';
        }
        if ($price >= 1_000_000) {
            return number_format($price / 1_000_000) . ' triệu';
        }
        return number_format($price) . ' đ';
    }

    private function loadPersona(): string
    {
        $personaFile = storage_path('app/chatbot_prompt.php');
        if (file_exists($personaFile)) {
            ob_start();
            include $personaFile;
            $content = trim((string) ob_get_clean());
            if ($content !== '') {
                return $content;
            }
        }
        return 'Bạn là chuyên viên tư vấn bất động sản của Khodat.com. Trả lời lễ phép, xưng "em", gọi khách là "anh/chị". Ngắn gọn, thân thiện, tập trung hỗ trợ khách mua đất ký gửi.';
    }

    private function getProvincesReference(): string
    {
        return Cache::remember('chatbot_provinces_reference', 3600, function () {
            $provinces = Province::active()->ordered()->with('activeWards')->get();
            if ($provinces->isEmpty()) return '';

            $lines = [];
            foreach ($provinces as $p) {
                $wards = $p->activeWards->pluck('name')->toArray();
                $lines[] = "- {$p->name}" . (!empty($wards) ? ': ' . implode(', ', $wards) : '');
            }
            return implode("\n", $lines);
        });
    }

    private function callLLM(array $messages, float $temperature = 0.3, int $maxTokens = 800): ?string
    {
        if (empty($this->openaiApiKey)) {
            Log::warning('OPENAI_API_KEY is empty');
            return null;
        }
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->openaiApiUrl, [
                    'model'       => $this->openaiModel,
                    'messages'    => $messages,
                    'temperature' => $temperature,
                    'max_tokens'  => $maxTokens,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }
            Log::warning('LLM call failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('LLM exception', ['error' => $e->getMessage()]);
        }
        return null;
    }

    private function countWord(array $items): string
    {
        return count($items) . ' sản phẩm';
    }
}
