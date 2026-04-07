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

// AI Chat proxy
Route::post('/api/ai-chat', function (\Illuminate\Http\Request $request) {
    $message = $request->input('message', '');
    if (!$message) {
        return response()->json(['error' => 'Message is required'], 400);
    }

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(30)->post('http://103.90.226.30:20128/v1/responses', [
            'model' => 'cx/gpt-5-codex-mini',
            'input' => $message,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            // Extract text from the response output
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
