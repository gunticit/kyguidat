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
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AdministrativeDivisionController;
use App\Http\Controllers\PostingPackageController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\IpnConfigController;
use App\Http\Controllers\IpnHandlerController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request as HttpRequest;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes - Posting Packages (Gói đăng bài - public để xem giá)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/posting-packages', [PostingPackageController::class, 'index']);
    Route::get('/posting-packages/{id}', [PostingPackageController::class, 'show']);
});

// Public routes - Consignments (Xem danh sách bất động sản công khai)
Route::prefix('public')->middleware('throttle:60,1')->group(function () {
    Route::get('/consignments', [PublicConsignmentController::class, 'index']);
    Route::get('/consignments/by-slug/{slug}', [PublicConsignmentController::class, 'showBySlug']);
    Route::get('/consignments/{id}', [PublicConsignmentController::class, 'show']);
});

// Webhook routes - Consignment events
Route::prefix('webhooks')->group(function () {
    // Incoming webhook handler (from external systems)
    Route::post('/consignment', [ConsignmentWebhookController::class, 'handle']);
});

// Public routes - Auth (rate limited to prevent brute force)
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
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

    Route::get('/github', [SocialAuthController::class, 'redirectToGithub']);
    Route::get('/github/callback', [SocialAuthController::class, 'handleGithubCallback']);
});

// Payment callbacks (public, rate limited)
Route::prefix('payments')->middleware('throttle:30,1')->group(function () {
    Route::get('/vnpay/callback', [PaymentController::class, 'vnpayCallback']);
    Route::post('/momo/callback', [PaymentController::class, 'momoCallback']);
    Route::post('/momo/notify', [PaymentController::class, 'momoNotify']);
    Route::get('/bank-info', [PaymentController::class, 'getBankInfo']);
});

// Public articles (no auth required)
Route::prefix('public')->group(function () {
    Route::get('/articles', [ArticleController::class, 'publicIndex']);
    Route::get('/articles/{slug}', [ArticleController::class, 'publicShow']);

    // Administrative divisions
    Route::get('/provinces', [AdministrativeDivisionController::class, 'publicProvinces']);
    Route::get('/provinces/{slug}/wards', [AdministrativeDivisionController::class, 'publicWards']);
    Route::get('/featured-provinces', [AdministrativeDivisionController::class, 'featuredProvinces']);
});

// IPN Handler routes (public - called by payment gateways)
Route::prefix('ipn')->middleware('throttle:30,1')->group(function () {
    Route::post('/vnpay', [IpnHandlerController::class, 'vnpay']);
    Route::post('/momo', [IpnHandlerController::class, 'momo']);
    Route::post('/zalopay', [IpnHandlerController::class, 'zalopay']);
    Route::post('/bank', [IpnHandlerController::class, 'bank']);
    Route::post('/custom', [IpnHandlerController::class, 'custom']);
});

// IPN Configuration endpoints (public - for getting URL info)
Route::get('/ipn/endpoints', [IpnConfigController::class, 'endpoints']);

// Protected routes (authenticated + rate limited)
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::delete('/auth/account', [AuthController::class, 'deleteAccount']);

    // Email verification
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return response()->json([
            'success' => true,
            'message' => 'Email đã được xác thực thành công'
        ]);
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/resend', function (HttpRequest $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email đã được xác thực rồi'
            ]);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json([
            'success' => true,
            'message' => 'Link xác thực đã được gửi lại'
        ]);
    })->middleware('throttle:3,1')->name('verification.send');

    // User Profile (accessible without email verification)
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
    Route::post('/consignments/{id}/reactivate', [ConsignmentController::class, 'reactivate']);
    Route::get('/consignments/{id}/history', [ConsignmentController::class, 'history']);
    Route::get('/posting-quota', [ConsignmentController::class, 'postingQuota']);

    // Payments (Nạp tiền)
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::post('/payments/vnpay/create', [PaymentController::class, 'createVnpay']);
    Route::post('/payments/momo/create', [PaymentController::class, 'createMomo']);
    Route::post('/payments/bank-transfer/create', [PaymentController::class, 'createBankTransfer']);

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
        Route::post('/image-optimized', [UploadController::class, 'uploadOptimized']);
        Route::post('/images-optimized', [UploadController::class, 'uploadMultipleOptimized']);
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

    // IPN Configuration Management (Quản lý cấu hình IPN)
    Route::prefix('ipn-config')->group(function () {
        // CRUD
        Route::get('/', [IpnConfigController::class, 'index']);
        Route::post('/', [IpnConfigController::class, 'store']);
        Route::get('/{id}', [IpnConfigController::class, 'show']);
        Route::put('/{id}', [IpnConfigController::class, 'update']);
        Route::delete('/{id}', [IpnConfigController::class, 'destroy']);

        // Actions
        Route::post('/{id}/toggle-active', [IpnConfigController::class, 'toggleActive']);
        Route::post('/{id}/test', [IpnConfigController::class, 'test']);

        // Logs
        Route::get('/logs/list', [IpnConfigController::class, 'logs']);
        Route::get('/logs/{id}', [IpnConfigController::class, 'logDetail']);

        // Statistics
        Route::get('/stats/overview', [IpnConfigController::class, 'statistics']);
    });

    // Admin Panel Routes (admin role required)
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard']);
        Route::get('/users', [App\Http\Controllers\AdminController::class, 'users']);
        Route::put('/users/{id}', [App\Http\Controllers\AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [App\Http\Controllers\AdminController::class, 'destroyUser']);

        // Customers (frontend-registered users)
        Route::get('/customers', [App\Http\Controllers\AdminController::class, 'customers']);

        // Consignments - CRUD
        Route::get('/consignments', [App\Http\Controllers\AdminController::class, 'consignments']);
        Route::get('/consignments/{id}', [App\Http\Controllers\AdminController::class, 'showConsignment']);
        Route::post('/consignments', [App\Http\Controllers\AdminController::class, 'storeConsignment']);
        Route::put('/consignments/{id}', [App\Http\Controllers\AdminController::class, 'updateConsignment']);
        Route::delete('/consignments/{id}', [App\Http\Controllers\AdminController::class, 'destroyConsignment']);
        Route::put('/consignments/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveConsignment']);
        Route::put('/consignments/{id}/reject', [App\Http\Controllers\AdminController::class, 'rejectConsignment']);

        // Support Tickets - Admin Management
        Route::get('/supports', [App\Http\Controllers\AdminController::class, 'supportTickets']);
        Route::get('/supports/{id}', [App\Http\Controllers\AdminController::class, 'showSupportTicket']);
        Route::post('/supports/{id}/reply', [App\Http\Controllers\AdminController::class, 'replySupportTicket']);
        Route::put('/supports/{id}/status', [App\Http\Controllers\AdminController::class, 'updateTicketStatus']);
        Route::post('/supports/{id}/close', [App\Http\Controllers\AdminController::class, 'closeSupportTicket']);

        Route::get('/transactions', [App\Http\Controllers\AdminController::class, 'transactions']);

        // Articles
        Route::get('/articles', [ArticleController::class, 'index']);
        Route::get('/articles/{id}', [ArticleController::class, 'show']);
        Route::post('/articles', [ArticleController::class, 'store']);
        Route::put('/articles/{id}', [ArticleController::class, 'update']);
        Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);

        // Administrative Divisions — Provinces
        Route::get('/provinces', [AdministrativeDivisionController::class, 'provinceIndex']);
        Route::post('/provinces', [AdministrativeDivisionController::class, 'provinceStore']);
        Route::put('/provinces/{id}', [AdministrativeDivisionController::class, 'provinceUpdate']);
        Route::delete('/provinces/{id}', [AdministrativeDivisionController::class, 'provinceDestroy']);

        // Administrative Divisions — Wards
        Route::get('/wards', [AdministrativeDivisionController::class, 'wardIndex']);
        Route::post('/wards', [AdministrativeDivisionController::class, 'wardStore']);
        Route::put('/wards/{id}', [AdministrativeDivisionController::class, 'wardUpdate']);
        Route::delete('/wards/{id}', [AdministrativeDivisionController::class, 'wardDestroy']);
    });
});

