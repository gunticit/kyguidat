<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConsignmentController;
use App\Http\Controllers\PublicConsignmentController;
use App\Http\Controllers\ConsignmentWebhookController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostingPackageController;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes - Posting Packages (Gói đăng bài - public để xem giá)
Route::get('/posting-packages', [PostingPackageController::class, 'index']);
Route::get('/posting-packages/{id}', [PostingPackageController::class, 'show']);

// Public routes - Consignments (Xem danh sách bất động sản công khai)
Route::prefix('public')->group(function () {
    Route::get('/consignments', [PublicConsignmentController::class, 'index']);
    Route::get('/consignments/{id}', [PublicConsignmentController::class, 'show']);
});

// Webhook routes - Consignment events
Route::prefix('webhooks')->group(function () {
    // Incoming webhook handler (from external systems)
    Route::post('/consignment', [ConsignmentWebhookController::class, 'handle']);
});

// Public routes
Route::prefix('auth')->group(function () {
    // Regular authentication
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Social authentication - requires session for OAuth state
Route::prefix('auth')->middleware('api.session')->group(function () {
    Route::get('/google', [SocialAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
    
    Route::get('/facebook', [SocialAuthController::class, 'redirectToFacebook']);
    Route::get('/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback']);
    
    Route::get('/zalo', [SocialAuthController::class, 'redirectToZalo']);
    Route::get('/zalo/callback', [SocialAuthController::class, 'handleZaloCallback']);
});

// Payment callbacks (public)
Route::prefix('payments')->group(function () {
    Route::get('/vnpay/callback', [PaymentController::class, 'vnpayCallback']);
    Route::post('/momo/callback', [PaymentController::class, 'momoCallback']);
    Route::post('/momo/notify', [PaymentController::class, 'momoNotify']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // User Profile
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-activities', [DashboardController::class, 'recentActivities']);
    
    // Consignments (Ký gửi)
    Route::apiResource('/consignments', ConsignmentController::class);
    Route::post('/consignments/{id}/cancel', [ConsignmentController::class, 'cancel']);
    Route::get('/consignments/{id}/history', [ConsignmentController::class, 'history']);
    
    // Payments (Nạp tiền)
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::post('/payments/vnpay/create', [PaymentController::class, 'createVnpay']);
    Route::post('/payments/momo/create', [PaymentController::class, 'createMomo']);
    Route::post('/payments/bank-transfer/create', [PaymentController::class, 'createBankTransfer']);
    Route::get('/payments/bank-info', [PaymentController::class, 'getBankInfo']);
    
    // Support (Hỗ trợ)
    Route::apiResource('/supports', SupportController::class);
    Route::post('/supports/{id}/messages', [SupportController::class, 'addMessage']);
    Route::get('/supports/{id}/messages', [SupportController::class, 'getMessages']);
    Route::post('/supports/{id}/close', [SupportController::class, 'close']);

    // Posting Packages (Gói đăng bài - protected routes)
    Route::post('/posting-packages/purchase', [PostingPackageController::class, 'purchaseWithWallet']);
    Route::get('/my-packages', [PostingPackageController::class, 'myPackages']);
    Route::get('/my-packages/current', [PostingPackageController::class, 'currentPackage']);

    // Upload (File upload endpoints)
    Route::prefix('upload')->group(function () {
        Route::post('/', [UploadController::class, 'upload']);
        Route::post('/multiple', [UploadController::class, 'uploadMultiple']);
        Route::post('/image', [UploadController::class, 'uploadImageHandler']);
        Route::post('/base64', [UploadController::class, 'uploadBase64']);
        Route::delete('/', [UploadController::class, 'delete']);
        Route::get('/info', [UploadController::class, 'info']);
    });

    // Webhook Management (Quản lý webhooks)
    Route::prefix('webhooks')->group(function () {
        Route::post('/register', [ConsignmentWebhookController::class, 'register']);
        Route::get('/', [ConsignmentWebhookController::class, 'list']);
        Route::delete('/{webhookId}', [ConsignmentWebhookController::class, 'delete']);
        Route::post('/test', [ConsignmentWebhookController::class, 'test']);
    });
});
