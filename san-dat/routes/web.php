<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ConsignmentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ArticleController;
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

// Policy pages
Route::get('/chinh-sach-bao-mat', function () {
    return view('pages.privacy-policy');
})->name('privacy-policy');

Route::get('/dieu-khoan-su-dung', function () {
    return view('pages.terms');
})->name('terms');

Route::get('/xoa-tai-khoan', function () {
    return view('pages.delete-account');
})->name('delete-account');

// Settings API
Route::get('/api/settings', [SettingsController::class, 'index']);
Route::post('/api/settings', [SettingsController::class, 'store']);
Route::post('/api/settings/upload', [SettingsController::class, 'upload']);
Route::post('/api/settings/api-keys', [SettingsController::class, 'storeApiKeys']);
Route::post('/api/settings/seo', [SettingsController::class, 'storeSeo']);

// API Consignments (for AJAX pagination)
Route::get('/api/consignments', [ConsignmentController::class, 'apiIndex']);

// API Provinces (proxy to API gateway for JS fetch)
Route::get('/api/public/provinces', function () {
    $apiService = app(\App\Services\GolangApiService::class);
    $provinces = $apiService->getProvinces();
    return response()->json(['data' => $provinces]);
});
