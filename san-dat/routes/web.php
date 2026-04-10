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
        // Lấy danh sách tỉnh/xã thực tế từ API (cached 1 giờ)
        $provincesContext = \Illuminate\Support\Facades\Cache::remember('ai_chat_provinces', 3600, function () {
            try {
                $apiService = app(\App\Services\GolangApiService::class);
                $provinces = $apiService->getProvinces();
                if (empty($provinces)) return '';

                $lines = [];
                foreach ($provinces as $p) {
                    $name = $p['name'] ?? '';
                    $wards = array_column($p['wards'] ?? [], 'name');
                    if (!empty($wards)) {
                        $lines[] = "- {$name}: " . implode(', ', $wards);
                    } else {
                        $lines[] = "- {$name}";
                    }
                }
                return implode("\n", $lines);
            } catch (\Throwable $e) {
                return '';
            }
        });

        $provincesSection = '';
        if (!empty($provincesContext)) {
            $provincesSection = "\n\nDANH SÁCH TỈNH/THÀNH VÀ XÃ/PHƯỜNG HIỆN TẠI (đã cập nhật sau sát nhập 2024-2025):\n{$provincesContext}\n\nLƯU Ý: Việt Nam đã sát nhập nhiều tỉnh thành từ 2024. Khi trả lời về tỉnh thành, BẮT BUỘC dùng đúng tên trong danh sách trên. KHÔNG dùng danh sách 63 tỉnh cũ.";
        }

        // Tìm sản phẩm liên quan nếu khách hỏi về BĐS
        $productContext = '';
        try {
            $backendUrl = config('services.backend.url', env('BACKEND_API_URL', 'http://backend-nginx:80'));
            $chatbotRes = \Illuminate\Support\Facades\Http::timeout(30)->post("{$backendUrl}/api/public/chatbot", [
                'text' => $message,
            ]);
            if ($chatbotRes->successful()) {
                $chatbotData = $chatbotRes->json('data');
                if (!empty($chatbotData['is_property_query']) && !empty($chatbotData['consignments'])) {
                    $productLines = [];
                    foreach ($chatbotData['consignments'] as $c) {
                        $price = ($c['price'] ?? 0) >= 1000000000
                            ? number_format(($c['price'] ?? 0) / 1000000000, 1) . ' tỷ'
                            : number_format(($c['price'] ?? 0) / 1000000) . ' triệu';
                        $link = !empty($c['seo_url'])
                            ? "https://khodat.com/dat/{$c['seo_url']}"
                            : "https://khodat.com/dat/{$c['id']}";
                        $productLines[] = "- {$c['title']} - {$price} → {$link}";
                    }
                    if (!empty($productLines)) {
                        $productContext = "\n\nSẢN PHẨM BĐS LIÊN QUAN TÌM ĐƯỢC TỪ HỆ THỐNG:\n" . implode("\n", $productLines) . "\nHãy giới thiệu những sản phẩm này cho khách nếu phù hợp, kèm link.";
                    }
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }

        $systemPrompt = "Bạn là trợ lý AI bất động sản của Khodat (khodat.com). Trả lời HOÀN TOÀN bằng tiếng Việt, thân thiện, chuyên nghiệp. Không dùng tiếng Anh.{$provincesSection}{$productContext}";

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
                        'temperature' => 0.7,
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
