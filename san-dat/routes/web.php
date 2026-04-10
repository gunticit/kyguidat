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

// API Provinces (proxy to API gateway for JS fetch)
Route::get('/api/public/provinces', function () {
    $apiService = app(\App\Services\GolangApiService::class);
    $provinces = $apiService->getProvinces();
    return response()->json(['data' => $provinces]);
});

// AI Chat proxy — dùng context tỉnh/xã thực tế từ DB
Route::post('/api/ai-chat', function (\Illuminate\Http\Request $request) {
    $message = $request->input('message', '');
    if (!$message) {
        return response()->json(['error' => 'Message is required'], 400);
    }

    try {
        // 1. Lấy danh sách tỉnh/xã thực tế từ API (cached 1 giờ)
        $provincesData = \Illuminate\Support\Facades\Cache::remember('ai_chat_provinces_v2', 3600, function () {
            try {
                $apiService = app(\App\Services\GolangApiService::class);
                $provinces = $apiService->getProvinces();
                if (empty($provinces)) return ['text' => '', 'count' => 0, 'names' => []];

                $lines = [];
                $names = [];
                foreach ($provinces as $p) {
                    $name = $p['name'] ?? '';
                    $names[] = $name;
                    $wards = array_column($p['wards'] ?? [], 'name');
                    if (!empty($wards)) {
                        $lines[] = "- {$name}: " . implode(', ', $wards);
                    } else {
                        $lines[] = "- {$name}";
                    }
                }
                return [
                    'text' => implode("\n", $lines),
                    'count' => count($provinces),
                    'names' => $names,
                ];
            } catch (\Throwable $e) {
                return ['text' => '', 'count' => 0, 'names' => []];
            }
        });

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
Bạn là trợ lý AI bất động sản của Khodat (khodat.com).

QUY TẮC BẮT BUỘC:
1. Trả lời HOÀN TOÀN bằng tiếng Việt. TUYỆT ĐỐI KHÔNG dùng tiếng Anh.
2. Việt Nam hiện tại có {$provinceCount} tỉnh/thành phố (ĐÃ SÁT NHẬP từ 2024-2025). KHÔNG BAO GIỜ nói "63 tỉnh thành". Dữ liệu cũ 63 tỉnh đã KHÔNG CÒN CHÍNH XÁC.
3. Khi khách hỏi về tỉnh thành, xã phường, CHỈ dùng danh sách bên dưới. KHÔNG dùng kiến thức cũ.
4. Nếu khách hỏi "bao nhiêu tỉnh thành" → trả lời: "Hiện tại Việt Nam có {$provinceCount} đơn vị hành chính cấp tỉnh (sau sát nhập 2024-2025)."
5. Thân thiện, chuyên nghiệp.

DANH SÁCH TỈNH/THÀNH VÀ XÃ/PHƯỜNG CHÍNH THỨC (dữ liệu thực từ hệ thống):
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
