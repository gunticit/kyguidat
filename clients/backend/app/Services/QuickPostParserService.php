<?php

namespace App\Services;

use App\Models\Province;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuickPostParserService
{
    private string $apiUrl;
    private string $model;
    private string $geminiApiKey;
    private string $geminiApiUrl;
    private string $geminiModel;

    public function __construct()
    {
        $this->apiUrl = config('services.ai_api.url', 'http://103.90.226.30:20128/v1/responses');
        $this->model = config('services.ai_api.model', 'cx/gpt-5-codex-mini');
        $this->geminiApiKey = config('services.gemini.api_key');
        $this->geminiModel = config('services.gemini.model', 'gemini-2.5-flash');
        
        $configuredUrl = config('services.gemini.api_url');
        if (empty($configuredUrl) || $configuredUrl === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent') {
            $this->geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->geminiModel . ':generateContent';
        } else {
            $this->geminiApiUrl = $configuredUrl;
        }
    }


    /**
     * Parse raw text from Zalo/Facebook post into structured consignment data
     */
    public function parse(string $text): array
    {
        $prompt = $this->buildPrompt($text);

        try {
            $data = $this->callAI($prompt);
            $parsed = $this->extractResponse($data);

            // Fallback: extract phone with regex if AI missed
            if (empty($parsed['seller_phone'])) {
                $parsed['seller_phone'] = $this->extractPhone($text);
            }

            // Fallback: extract Google Map link with regex if AI missed
            if (empty($parsed['google_map_link'])) {
                $parsed['google_map_link'] = $this->extractMapLink($text);
            }

            // Resolve Google Maps short URL to get lat/lng
            if (!empty($parsed['google_map_link'])) {
                $coords = $this->resolveGoogleMapsCoords($parsed['google_map_link']);
                $parsed['latitude'] = $coords['latitude'];
                $parsed['longitude'] = $coords['longitude'];
            }

            return $parsed;
        } catch (\Exception $e) {
            Log::error('AI Parse failed completely', ['error' => $e->getMessage()]);
            throw new \Exception('Không thể kết nối đến AI. Vui lòng thử lại.');
        }
    }

    /**
     * Call AI API: Gemini first, fallback to custom API when Gemini fails
     */
    private function callAI(string $prompt): array
    {
        // --- Try Gemini first (primary) ---
        if (!empty($this->geminiApiKey)) {
            try {
                Log::info('Using Gemini as primary API', ['model' => $this->geminiModel]);

                $url = $this->geminiApiUrl;
                if (!str_contains($url, 'key=')) {
                    $url .= '?key=' . $this->geminiApiKey;
                }

                $response = Http::timeout(60)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $prompt
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                            'temperature' => 0.1,
                        ]
                    ]);

                if ($response->status() === 429 || $response->serverError()) {
                    Log::warning('Gemini quota/error, switching to fallback API', [
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500),
                    ]);
                    return $this->callFallbackAPI($prompt);
                }

                if (!$response->successful()) {
                    Log::error('Gemini API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return $this->callFallbackAPI($prompt);
                }

                // Wrap Gemini response in unified format
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                return [
                    'output' => [
                        [
                            'type' => 'message',
                            'content' => [
                                [
                                    'type' => 'output_text',
                                    'text' => $text,
                                ],
                            ],
                        ],
                    ],
                    '_source' => 'gemini',
                ];
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning('Gemini connection failed, switching to fallback API', [
                    'error' => $e->getMessage(),
                ]);
                return $this->callFallbackAPI($prompt);
            }
        }

        // No Gemini key configured, use fallback directly
        return $this->callFallbackAPI($prompt);
    }

    /**
     * Call custom AI API as fallback
     */
    private function callFallbackAPI(string $prompt): array
    {
        Log::info('Using fallback API', ['url' => $this->apiUrl, 'model' => $this->model]);

        $response = Http::timeout(30)->post($this->apiUrl, [
            'model' => $this->model,
            'input' => $prompt,
        ]);

        if (!$response->successful()) {
            Log::error('Fallback API also failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Cả 2 AI API đều không khả dụng. Vui lòng thử lại sau.');
        }

        return $response->json();
    }

    /**
     * Get provinces and wards list from database (cached 1 hour)
     */
    private function getProvincesReference(): string
    {
        return Cache::remember('ai_provinces_reference', 3600, function () {
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
     * Build the AI prompt
     */
    private function buildPrompt(string $text): string
    {
        $provincesRef = $this->getProvincesReference();
        $provincesSection = '';

        if (!empty($provincesRef)) {
            $provincesSection = <<<SECTION

DANH SÁCH TỈNH/THÀNH VÀ XÃ/PHƯỜNG HIỆN TẠI (đã cập nhật sau sát nhập):
{$provincesRef}

LƯU Ý QUAN TRỌNG: Việt Nam đã sát nhập nhiều tỉnh thành. Khi extract trường "province" và "ward", BẮT BUỘC phải dùng đúng tên trong danh sách trên. Nếu bài đăng dùng tên cũ, hãy chuyển sang tên mới tương ứng.
SECTION;
        }

        return <<<PROMPT
Phân tích bài đăng bất động sản sau và trả về JSON thuần (không markdown, không code block, không giải thích).
Các trường cần extract:
- title: tóm tắt ngắn gọn làm tiêu đề bài rao bán đất (max 200 ký tự, viết tự nhiên, không emoji)
- description: nội dung đầy đủ bài đăng đã format lại sạch sẽ (bỏ emoji thừa, giữ thông tin quan trọng)
- address: địa chỉ cụ thể nhất có thể (đường, phường/xã, quận/huyện, tỉnh/thành phố)
- province: tên tỉnh/thành phố (phải khớp chính xác với danh sách bên dưới nếu có)
- ward: tên xã/phường/thị trấn (phải khớp chính xác với danh sách bên dưới nếu có)
- price: giá bán quy đổi sang đơn vị VNĐ dạng số nguyên (VD: "1tỉ 450tr" = 1450000000, "800 triệu" = 800000000, "2ty5" = 2500000000)
- seller_phone: số điện thoại liên hệ (chỉ số, VD: "0779502838")
- google_map_link: link Google Maps nếu có trong bài, giữ nguyên URL gốc
{$provincesSection}

Nếu không tìm thấy trường nào, để giá trị null.
Tất cả nội dung trả về PHẢI bằng tiếng Việt.
CHỈ trả về JSON object, không thêm bất kỳ text nào khác.

Bài đăng:
---
{$text}
---
PROMPT;
    }

    /**
     * Extract parsed JSON from AI response
     */
    private function extractResponse(array $response): array
    {
        $defaults = [
            'title' => null,
            'description' => null,
            'address' => null,
            'province' => null,
            'ward' => null,
            'price' => null,
            'seller_phone' => null,
            'google_map_link' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        // Find the message type output
        $messageText = null;
        foreach ($response['output'] ?? [] as $output) {
            if (($output['type'] ?? '') === 'message') {
                foreach ($output['content'] ?? [] as $content) {
                    if (($content['type'] ?? '') === 'output_text') {
                        $messageText = $content['text'] ?? null;
                        break 2;
                    }
                }
            }
        }

        if (!$messageText) {
            Log::warning('AI response has no message output', ['response' => $response]);
            return $defaults;
        }

        // Clean markdown code blocks if any
        $messageText = preg_replace('/^```(?:json)?\s*/m', '', $messageText);
        $messageText = preg_replace('/\s*```$/m', '', $messageText);
        $messageText = trim($messageText);

        // Parse JSON
        $parsed = json_decode($messageText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('AI response is not valid JSON', ['text' => $messageText]);
            return $defaults;
        }

        // Ensure price is numeric
        if (isset($parsed['price']) && is_string($parsed['price'])) {
            $parsed['price'] = $this->parseVietnamesePrice($parsed['price']);
        }

        // Clean phone number
        if (isset($parsed['seller_phone'])) {
            $parsed['seller_phone'] = preg_replace('/[^0-9]/', '', $parsed['seller_phone']);
        }

        return array_merge($defaults, array_intersect_key($parsed, $defaults));
    }

    /**
     * Parse Vietnamese price string to number
     * Examples: "1tỉ 450tr" => 1450000000, "800 triệu" => 800000000
     */
    private function parseVietnamesePrice(string $priceStr): ?int
    {
        $priceStr = mb_strtolower(trim($priceStr));
        $total = 0;

        // Match patterns like "1tỉ", "1 tỷ", "1ty"
        if (preg_match('/([\d.,]+)\s*(?:tỉ|tỷ|ty)/u', $priceStr, $m)) {
            $total += floatval(str_replace(',', '.', $m[1])) * 1000000000;
        }

        // Match patterns like "450tr", "450 triệu"
        if (preg_match('/([\d.,]+)\s*(?:tr(?:iệu)?|trieu)/u', $priceStr, $m)) {
            $total += floatval(str_replace(',', '.', $m[1])) * 1000000;
        }

        return $total > 0 ? (int) $total : null;
    }

    /**
     * Extract phone number from text using regex
     */
    private function extractPhone(string $text): ?string
    {
        if (preg_match('/(?:lh|liên hệ|sdt|số điện thoại|zalo|call|gọi)[:\s]*(\d{10,11})/iu', $text, $m)) {
            return $m[1];
        }
        if (preg_match('/\b(0\d{9,10})\b/', $text, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Extract Google Maps link from text using regex
     */
    private function extractMapLink(string $text): ?string
    {
        if (preg_match('/(https?:\/\/(?:maps\.app\.goo\.gl|goo\.gl\/maps|google\.com\/maps)[^\s\]"\'<>)]+)/i', $text, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Resolve Google Maps short URL and extract lat/lng coordinates
     */
    public function resolveGoogleMapsCoords(string $url): array
    {
        $result = ['latitude' => null, 'longitude' => null];

        // If URL already has @lat,lng, extract directly
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            $result['latitude'] = (float) $m[1];
            $result['longitude'] = (float) $m[2];
            return $result;
        }

        // For short URLs, follow redirects to get the full URL
        if (preg_match('/maps\.app\.goo\.gl|goo\.gl\/maps/i', $url)) {
            try {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_NOBODY => true,
                    CURLOPT_HEADER => false,
                ]);
                curl_exec($ch);
                $resolvedUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);

                if ($resolvedUrl && preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $resolvedUrl, $m)) {
                    $result['latitude'] = (float) $m[1];
                    $result['longitude'] = (float) $m[2];
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to resolve Google Maps URL', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }
}
