<?php

namespace App\Services;

use App\Models\Consignment;
use App\Models\Province;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private string $openaiApiKey;
    private string $openaiApiUrl;
    private string $openaiModel;
    private string $fallbackApiUrl;
    private string $fallbackModel;
    private string $siteUrl;

    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY', '');
        $this->openaiApiUrl = env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
        $this->openaiModel = env('OPENAI_API_MODEL', 'gpt-5.4-mini');
        $this->fallbackApiUrl = env('AI_API_URL', 'http://103.90.226.30:20128/v1/responses');
        $this->fallbackModel = env('AI_API_MODEL', 'cx/gpt-5-codex-mini');
        $this->siteUrl = env('APP_URL_SANDAT', 'https://khodat.com');
    }

    /**
     * Handle incoming chat message and return auto-reply if relevant
     *
     * @return array{is_property_query: bool, reply: string|null, consignments: array}
     */
    public function handleMessage(string $text): array
    {
        try {
            // Step 1: AI extract search intent
            $intent = $this->extractSearchIntent($text);

            if (!$intent['is_property_query']) {
                return [
                    'is_property_query' => false,
                    'reply' => null,
                    'consignments' => [],
                ];
            }

            // Step 2: Search consignments
            $results = $this->searchConsignments($intent);

            // Step 3: Format response
            $reply = $this->formatResponse($results, $intent, $text);

            return [
                'is_property_query' => true,
                'reply' => $reply,
                'consignments' => $results->map(fn($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'price' => $c->price,
                    'address' => $c->address,
                    'seo_url' => $c->seo_url,
                    'featured_image' => $c->featured_image,
                ])->values()->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::error('Chatbot error', ['error' => $e->getMessage(), 'text' => $text]);
            return [
                'is_property_query' => false,
                'reply' => null,
                'consignments' => [],
            ];
        }
    }

    /**
     * Use AI to extract search criteria from natural language
     */
    private function extractSearchIntent(string $text): array
    {
        $provincesRef = $this->getProvincesReference();

        $prompt = <<<PROMPT
Bạn là chatbot bất động sản. Phân tích tin nhắn khách hàng và xác định xem họ có đang hỏi về bất động sản/đất đai không.

Nếu CÓ, extract các tiêu chí tìm kiếm. Nếu KHÔNG (ví dụ: chào hỏi, hỏi giờ mở cửa, v.v.), trả is_property_query = false.

DANH SÁCH TỈNH/THÀNH VÀ XÃ/PHƯỜNG:
{$provincesRef}

Trả về JSON thuần (không markdown, không code block):
{
  "is_property_query": true/false,
  "province": "tên tỉnh/thành (khớp chính xác danh sách trên)" | null,
  "ward": "tên xã/phường" | null,
  "min_price": số nguyên VNĐ | null,
  "max_price": số nguyên VNĐ | null,
  "direction": "hướng (Đông/Tây/Nam/Bắc/Đông Nam/...)" | null,
  "property_type": "loại đất (đất vườn/đất thổ cư/đất nông nghiệp/...)" | null,
  "search_keywords": "từ khóa tìm kiếm bổ sung" | null
}

Lưu ý quy đổi giá:
- "500 triệu" = 500000000
- "1 tỷ" = 1000000000
- "tầm 500 triệu" → max_price = 600000000 (thêm buffer 20%)
- "từ 500 triệu đến 1 tỷ" → min_price = 500000000, max_price = 1000000000
- "dưới 1 tỷ" → max_price = 1000000000
- "trên 2 tỷ" → min_price = 2000000000

Tin nhắn khách:
---
{$text}
---
PROMPT;

        $response = $this->callAI($prompt);

        $defaults = [
            'is_property_query' => false,
            'province' => null,
            'ward' => null,
            'min_price' => null,
            'max_price' => null,
            'direction' => null,
            'property_type' => null,
            'search_keywords' => null,
        ];

        if (!$response) {
            return $defaults;
        }

        // Clean markdown
        $response = preg_replace('/^```(?:json)?\s*/m', '', $response);
        $response = preg_replace('/\s*```$/m', '', $response);
        $response = trim($response);

        $parsed = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Chatbot: AI response not valid JSON', ['response' => $response]);
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($parsed, $defaults));
    }

    /**
     * Search consignments in database based on extracted criteria
     */
    private function searchConsignments(array $criteria): Collection
    {
        $query = Consignment::query()
            ->whereIn('status', [Consignment::STATUS_APPROVED, Consignment::STATUS_SELLING])
            ->with('user:id,name');

        if (!empty($criteria['province'])) {
            $query->where('province', $criteria['province']);
        }

        if (!empty($criteria['ward'])) {
            $query->where('ward', $criteria['ward']);
        }

        if (!empty($criteria['min_price'])) {
            $query->where('price', '>=', $criteria['min_price']);
        }

        if (!empty($criteria['max_price'])) {
            $query->where('price', '<=', $criteria['max_price']);
        }

        if (!empty($criteria['direction'])) {
            $query->whereJsonContains('land_directions', $criteria['direction']);
        }

        if (!empty($criteria['property_type'])) {
            $query->whereJsonContains('land_types', $criteria['property_type']);
        }

        if (!empty($criteria['search_keywords'])) {
            $keywords = $criteria['search_keywords'];
            $query->where(function ($q) use ($keywords) {
                $q->where('title', 'like', "%{$keywords}%")
                    ->orWhere('address', 'like', "%{$keywords}%")
                    ->orWhere('description', 'like', "%{$keywords}%");
            });
        }

        return $query
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    /**
     * Format friendly response with consignment links
     */
    private function formatResponse(Collection $results, array $criteria, string $originalText): string
    {
        $locationParts = [];
        if (!empty($criteria['ward'])) {
            $locationParts[] = $criteria['ward'];
        }
        if (!empty($criteria['province'])) {
            $locationParts[] = $criteria['province'];
        }
        $location = !empty($locationParts) ? implode(', ', $locationParts) : 'khu vực bạn tìm';

        if ($results->isEmpty()) {
            return "Xin lỗi, hiện tại mình chưa có sản phẩm nào phù hợp ở {$location}. "
                . "Bạn có thể để lại số điện thoại, khi có đất phù hợp mình sẽ liên hệ ngay ạ! 😊";
        }

        $count = $results->count();
        $lines = ["Mình tìm được {$count} sản phẩm phù hợp ở {$location}:\n"];

        foreach ($results as $index => $consignment) {
            $num = $index + 1;
            $title = $consignment->title;
            $price = $this->formatPrice($consignment->price);
            $link = $this->buildConsignmentLink($consignment);
            $lines[] = "{$num}. {$title} - {$price}\n   👉 {$link}";
        }

        $lines[] = "\nBạn muốn xem chi tiết sản phẩm nào ạ? 😊";

        return implode("\n", $lines);
    }

    /**
     * Format price in Vietnamese
     */
    private function formatPrice(?float $price): string
    {
        if (!$price) return 'Liên hệ';

        if ($price >= 1000000000) {
            $ty = $price / 1000000000;
            if ($ty == floor($ty)) {
                return number_format($ty) . ' tỷ';
            }
            return number_format($ty, 1) . ' tỷ';
        }

        if ($price >= 1000000) {
            $trieu = $price / 1000000;
            return number_format($trieu) . ' triệu';
        }

        return number_format($price) . ' đ';
    }

    /**
     * Build public URL for a consignment
     */
    private function buildConsignmentLink(Consignment $consignment): string
    {
        if ($consignment->seo_url) {
            return "{$this->siteUrl}/dat/{$consignment->seo_url}";
        }
        return "{$this->siteUrl}/dat/{$consignment->id}";
    }

    /**
     * Get provinces reference for AI context (cached 1 hour)
     */
    private function getProvincesReference(): string
    {
        return Cache::remember('chatbot_provinces_reference', 3600, function () {
            $provinces = Province::active()
                ->ordered()
                ->with('activeWards')
                ->get();

            if ($provinces->isEmpty()) {
                return '';
            }

            $lines = [];
            foreach ($provinces as $province) {
                $wards = $province->activeWards->pluck('name')->toArray();
                if (!empty($wards)) {
                    $lines[] = "- {$province->name}: " . implode(', ', $wards);
                } else {
                    $lines[] = "- {$province->name}";
                }
            }

            return implode("\n", $lines);
        });
    }

    /**
     * Call AI: OpenAI first, fallback to custom API
     */
    private function callAI(string $prompt): ?string
    {
        // Try OpenAI first
        if (!empty($this->openaiApiKey)) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->openaiApiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->openaiApiUrl, [
                        'model' => $this->openaiModel,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.1,
                    ]);

                if ($response->successful()) {
                    return $response->json('choices.0.message.content');
                }

                Log::warning('Chatbot: OpenAI failed, trying fallback', [
                    'status' => $response->status(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Chatbot: OpenAI connection failed', ['error' => $e->getMessage()]);
            }
        }

        // Fallback to custom API
        try {
            $response = Http::timeout(30)->post($this->fallbackApiUrl, [
                'model' => $this->fallbackModel,
                'input' => $prompt,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                foreach ($data['output'] ?? [] as $output) {
                    if (($output['type'] ?? '') === 'message') {
                        foreach ($output['content'] ?? [] as $content) {
                            if (($content['type'] ?? '') === 'output_text') {
                                return $content['text'] ?? null;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Chatbot: Both APIs failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
