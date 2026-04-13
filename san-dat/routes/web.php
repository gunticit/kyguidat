<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ConsignmentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Consignments
Route::get('/bat-dong-san', [ConsignmentController::class, 'index'])->name('consignments.index');
Route::get('/bat-dong-san/{slug}', [ConsignmentController::class, 'show'])->name('consignments.show');

// Search
Route::get('/tim-kiem', [SearchController::class, 'results'])->name('search.results');

// Articles (Tin tức)
Route::get('/tin-tuc', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/tin-tuc/{slug}', [ArticleController::class, 'show'])->name('articles.show');

// Contact (Liên hệ)
Route::get('/lien-he', [ArticleController::class, 'contact'])->name('contact');

// Policy pages (static fallbacks)
Route::get('/chinh-sach-bao-mat', function () {
    return view('pages.privacy-policy');
})->name('privacy-policy');

Route::get('/dieu-khoan-su-dung', function () {
    return view('pages.terms');
})->name('terms');

Route::get('/xoa-tai-khoan', function () {
    return view('pages.delete-account');
})->name('delete-account');

// Settings API (exempt from CSRF — called cross-origin from admin.khodat.com)
Route::get('/api/settings', [SettingsController::class, 'index']);
Route::post('/api/settings', [SettingsController::class, 'store'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
Route::post('/api/settings/upload', [SettingsController::class, 'upload'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
Route::post('/api/settings/api-keys', [SettingsController::class, 'storeApiKeys'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
Route::post('/api/settings/seo', [SettingsController::class, 'storeSeo'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// API Consignments (for AJAX pagination)
Route::get('/api/consignments', [ConsignmentController::class, 'apiIndex']);

// API Provinces (proxy to API gateway for JS fetch, with DB fallback)
Route::get('/api/public/provinces', function () {
    // Thử API Gateway trước
    try {
        $apiService = app(\App\Services\GolangApiService::class);
        $provinces = $apiService->getProvinces();
        if (!empty($provinces)) {
            return response()->json(['data' => $provinces]);
        }
    } catch (\Throwable $e) {
        // API Gateway fail
    }

    // Fallback: lấy trực tiếp từ DB
    try {
        $dbProvinces = \Illuminate\Support\Facades\DB::table('provinces')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $result = [];
        foreach ($dbProvinces as $p) {
            $wards = \Illuminate\Support\Facades\DB::table('wards')
                ->where('province_id', $p->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'slug'])
                ->map(fn($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                    'type' => $w->type,
                    'type_label' => match ($w->type) {
                        'phuong' => 'Phường',
                        'xa' => 'Xã',
                        'dac_khu' => 'Đặc khu',
                        default => $w->type,
                    },
                ])
                ->toArray();

            $result[] = [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'wards' => $wards,
            ];
        }
        return response()->json(['data' => $result]);
    } catch (\Throwable $e) {
        return response()->json(['data' => []]);
    }
});

// AI Chat proxy — dùng context tỉnh/xã thực tế từ DB
Route::post('/api/ai-chat', function (\Illuminate\Http\Request $request) {
    $message = $request->input('message', '');
    if (!$message) {
        return response()->json(['error' => 'Message is required'], 400);
    }

    try {
        // 1. Lấy danh sách tỉnh/xã thực tế (cached 1 giờ, chỉ cache khi có data)
        $provincesData = \Illuminate\Support\Facades\Cache::get('ai_chat_provinces_v3');

        if (!$provincesData) {
            $provincesData = ['text' => '', 'count' => 0, 'names' => []];

            // Thử lấy từ API Gateway
            $provinces = [];
            try {
                $apiService = app(\App\Services\GolangApiService::class);
                $provinces = $apiService->getProvinces();
            } catch (\Throwable $e) {
                // API Gateway fail
            }

            // Fallback: lấy trực tiếp từ DB nếu API fail
            if (empty($provinces)) {
                try {
                    $dbProvinces = \Illuminate\Support\Facades\DB::table('provinces')
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->get();

                    foreach ($dbProvinces as $p) {
                        $wards = \Illuminate\Support\Facades\DB::table('wards')
                            ->where('province_id', $p->id)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->pluck('name')
                            ->toArray();

                        $provinces[] = [
                            'name' => $p->name,
                            'wards' => array_map(fn($w) => ['name' => $w], $wards),
                        ];
                    }
                } catch (\Throwable $e) {
                    // DB fail
                }
            }

            if (!empty($provinces)) {
                $lines = [];
                $names = [];
                foreach ($provinces as $p) {
                    $name = $p['name'] ?? '';
                    $names[] = $name;
                    $wardNames = array_column($p['wards'] ?? [], 'name');
                    if (!empty($wardNames)) {
                        $lines[] = "- {$name}: " . implode(', ', $wardNames);
                    } else {
                        $lines[] = "- {$name}";
                    }
                }
                $provincesData = [
                    'text' => implode("\n", $lines),
                    'count' => count($provinces),
                    'names' => $names,
                ];
                // Chỉ cache khi có dữ liệu
                \Illuminate\Support\Facades\Cache::put('ai_chat_provinces_v3', $provincesData, 3600);
            }
        }

        $provinceCount = $provincesData['count'];
        $provincesText = $provincesData['text'];

        // 2. Tìm sản phẩm BĐS liên quan
        $productLines = [];
        $chatbotReply = null;
        try {
            $backendUrl = env('BACKEND_API_URL', 'http://backend-nginx:80');
            $chatbotRes = \Illuminate\Support\Facades\Http::timeout(30)->post("{$backendUrl}/api/public/chatbot", [
                'text' => $message,
            ]);
            if ($chatbotRes->successful()) {
                $chatbotData = $chatbotRes->json('data');
                if (!empty($chatbotData['is_property_query']) && !empty($chatbotData['consignments'])) {
                    foreach ($chatbotData['consignments'] as $c) {
                        $price = ($c['price'] ?? 0) >= 1000000000
                            ? number_format(($c['price'] ?? 0) / 1000000000, 1) . ' tỷ'
                            : number_format(($c['price'] ?? 0) / 1000000) . ' triệu';
                        $link = !empty($c['seo_url'])
                            ? "https://khodat.com/dat/{$c['seo_url']}"
                            : "https://khodat.com/dat/{$c['id']}";
                        $productLines[] = "- {$c['title']} - {$price}\n  Xem chi tiết: {$link}";
                    }
                }
                // Nếu chatbot đã có reply (kèm link), dùng trực tiếp
                if (!empty($chatbotData['is_property_query']) && !empty($chatbotData['reply'])) {
                    $chatbotReply = $chatbotData['reply'];
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }

        // 3. Nếu khách hỏi BĐS và có sản phẩm → trả trực tiếp (không cần AI)
        if (!empty($chatbotReply)) {
            return response()->json(['text' => $chatbotReply]);
        }

        // 4. Gọi AI cho các câu hỏi khác
        $productContext = '';
        if (!empty($productLines)) {
            $productContext = "\n\nSẢN PHẨM BĐS TÌM ĐƯỢC TỪ HỆ THỐNG (dữ liệu thực):\n" . implode("\n", $productLines) . "\n\nHãy giới thiệu các sản phẩm trên cho khách, GIỮ NGUYÊN link.";
        }

        $systemPrompt = <<<SYSTEM
Bạn là trợ lý AI của **KhoDat.Com** — chuyên viên tư vấn bất động sản 7 năm kinh nghiệm tại vùng ven TP.HCM.
Bạn là người thật, nói chuyện nhân tính hoá, giống người miền Nam thân thiện.
Dùng "mình/bạn". Thi thoảng dùng "nè", "á", "nha", "thật ra", "thú thật".
KHÔNG BAO GIỜ NÓI: "Là một AI...", "Tôi không có cảm xúc...", "Theo dữ liệu của tôi..."

QUY TẮC BẮT BUỘC:
1. TRẢ LỜI NGẮN GỌN CHO LỜI CHÀO: Nếu khách chỉ nhắn "hi", "xin chào" hoặc các câu giao tiếp cơ bản, BẠN CHỈ CẦN CHÀO LẠI NGẮN GỌN (1-2 câu). KHÔNG nói dài dòng.
2. Hỏi trước — tư vấn sau: Khi hỏi nhu cầu, khoan đưa danh sách sản phẩm ngay nếu chưa biết khu vực, ngân sách.
3. Câu ngắn — ý rõ: Mỗi tin nhắn tối đa 4-5 câu. Đừng liệt kê bullet point dài như làm báo cáo.
4. Trả lời HOÀN TOÀN bằng tiếng Việt. TUYỆT ĐỐI KHÔNG dùng tiếng Anh.
5. Việt Nam có {$provinceCount} tỉnh/thành phố (sau sát nhập 2024-2025). KHÔNG nói "63 tỉnh thành".
6. Mượn chuyện bên ngoài (ví dụ thực tế) hoặc hỏi ngược lại (Bridge) khi trò chuyện đi sâu vào tư vấn, nhưng KHÔNG LẠM DỤNG cho các câu hỏi đơn giản.

THÔNG TIN KHODAT (KhoDat.Com):
- Hotline: 1900 8041 | Email: adkhodat@gmail.com | 226 Ung Văn Khiêm, P. Thạnh Mỹ Tây, TP.HCM
- Khu vực: Đồng Nai (~19 tin), HCM, Lâm Đồng, Tây Ninh, Vĩnh Long.
- Loại: Đất nền, Đất sào, Chung cư, BĐS nghỉ dưỡng, Đất kinh doanh...

CÁCH GỢI Ý SẢN PHẨM:
Khi có sản phẩm phù hợp trong DỮ LIỆU THỰC TỪ HỆ THỐNG mà hệ thống tự động chèn bên dưới, hãy liệt kê chúng ra một cách TỰ NHIÊN cho khách.
VÍ DỤ: 
"- [Tên sản phẩm] - Giá: [Giá tiền]
 Xem chi tiết: [Link sản phẩm]"
GIỮ NGUYÊN ĐƯỜNG LINK. TUYỆT ĐỐI KHÔNG TRẢ VỀ JSON, CHỈ TRẢ VỀ ĐOẠN TEXT BÌNH THƯỜNG. CHỈ GỢI Ý NẾU CÓ. KHÔNG TỰ BỊA.

TRÁNH HOÀN TOÀN (CẤM KỴ):
- ❌ "Tôi hiểu rằng bạn đang tìm kiếm..."
- ❌ "Dựa trên thông tin bạn cung cấp..."
- ❌ "Theo như tôi được biết..."
- ❌ "Chắc chắn rồi! Tôi sẽ giúp bạn ngay!"

DANH SÁCH TỈNH/THÀNH VÀ XÃ/PHƯỜNG CHÍNH THỨC VÀ SẢN PHẨM TÌM ĐƯỢC:
{$provincesText}{$productContext}
SYSTEM;

        // OpenAI first
        $openaiKey = env('OPENAI_API_KEY', '');
        if (!empty($openaiKey)) {
            try {
                $aiResponse = \Illuminate\Support\Facades\Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $openaiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post(env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'), [
                        'model' => env('OPENAI_API_MODEL', 'gpt-5.4-mini'),
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $message],
                        ],
                        'temperature' => 0.3,
                    ]);

                if ($aiResponse->successful()) {
                    $text = $aiResponse->json('choices.0.message.content');
                    if (!empty($text)) {
                        return response()->json(['text' => $text]);
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('AI Chat: OpenAI failed, fallback', ['error' => $e->getMessage()]);
            }
        }

        // Fallback to custom API
        $fallbackResponse = \Illuminate\Support\Facades\Http::timeout(30)->post(
            env('AI_API_URL', 'http://103.90.226.30:20128/v1/responses'),
            [
                'model' => env('AI_API_MODEL', 'cx/gpt-5-codex-mini'),
                'input' => $systemPrompt . "\n\nKhách hỏi: " . $message,
            ]
        );

        if ($fallbackResponse->successful()) {
            $data = $fallbackResponse->json();
            $text = '';
            foreach (($data['output'] ?? []) as $output) {
                if (($output['type'] ?? '') === 'message') {
                    foreach (($output['content'] ?? []) as $content) {
                        if (($content['type'] ?? '') === 'output_text') {
                            $text = $content['text'] ?? '';
                            break 2;
                        }
                    }
                }
            }
            return response()->json(['text' => $text ?: 'Xin lỗi, tôi không thể trả lời lúc này.']);
        }

        return response()->json(['text' => 'Hệ thống AI đang bận, vui lòng thử lại sau.'], 500);
    } catch (\Exception $e) {
        return response()->json(['text' => 'Không thể kết nối tới AI. Vui lòng thử lại sau.'], 500);
    }
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// Dynamic CMS Pages (catch-all — must be last!)
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show')
    ->where('slug', '^(?!api|bat-dong-san|tim-kiem|tin-tuc|lien-he|chinh-sach-bao-mat|dieu-khoan-su-dung|xoa-tai-khoan).*$');
