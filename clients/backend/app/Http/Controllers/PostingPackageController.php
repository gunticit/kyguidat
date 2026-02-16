<?php

namespace App\Http\Controllers;

use App\Models\PostingPackage;
use App\Models\UserPackage;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PostingPackageController extends Controller
{
    /**
     * Lấy danh sách các gói đăng bài
     */
    public function index()
    {
        $packages = PostingPackage::active()
            ->ordered()
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'description' => $package->description,
                    'duration_months' => $package->duration_months,
                    'price' => $package->price,
                    'original_price' => $package->original_price,
                    'formatted_price' => $package->formatted_price,
                    'formatted_original_price' => $package->formatted_original_price,
                    'discount_percentage' => $package->discount_percentage,
                    'post_limit' => $package->post_limit,
                    'featured_posts' => $package->featured_posts,
                    'priority_support' => $package->priority_support,
                    'features' => $package->features,
                    'is_popular' => $package->is_popular,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    /**
     * Chi tiết gói đăng bài
     */
    public function show($id)
    {
        $package = PostingPackage::active()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $package->id,
                'name' => $package->name,
                'slug' => $package->slug,
                'description' => $package->description,
                'duration_months' => $package->duration_months,
                'price' => $package->price,
                'original_price' => $package->original_price,
                'formatted_price' => $package->formatted_price,
                'formatted_original_price' => $package->formatted_original_price,
                'discount_percentage' => $package->discount_percentage,
                'post_limit' => $package->post_limit,
                'featured_posts' => $package->featured_posts,
                'priority_support' => $package->priority_support,
                'features' => $package->features,
                'is_popular' => $package->is_popular,
            ],
        ]);
    }

    /**
     * Mua gói đăng bài bằng ví
     */
    public function purchaseWithWallet(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:posting_packages,id',
        ]);

        $user = $request->user();
        $package = PostingPackage::active()->findOrFail($request->package_id);
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $package->price) {
            return response()->json([
                'success' => false,
                'message' => 'Số dư ví không đủ để mua gói này',
                'required_amount' => $package->price,
                'current_balance' => $wallet ? $wallet->balance : 0,
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Trừ tiền trong ví
            $wallet->decrement('balance', $package->price);

            // Tạo giao dịch ví
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'purchase',
                'amount' => -$package->price,
                'balance_before' => $wallet->balance + $package->price,
                'balance_after' => $wallet->balance,
                'description' => "Mua {$package->name}",
                'reference_type' => 'user_package',
            ]);

            // Kiểm tra nếu user đang có gói active, gia hạn thêm
            $activePackage = $user->userPackages()
                ->where('posting_package_id', $package->id)
                ->active()
                ->first();

            if ($activePackage) {
                // Gia hạn gói hiện tại
                $newExpiresAt = Carbon::parse($activePackage->expires_at)
                    ->addMonths($package->duration_months);

                $activePackage->update([
                    'expires_at' => $newExpiresAt,
                    'amount_paid' => $activePackage->amount_paid + $package->price,
                ]);

                $userPackage = $activePackage;
            } else {
                // Tạo gói mới
                $userPackage = UserPackage::create([
                    'user_id' => $user->id,
                    'posting_package_id' => $package->id,
                    'amount_paid' => $package->price,
                    'started_at' => now(),
                    'expires_at' => now()->addMonths($package->duration_months),
                    'status' => 'active',
                    'payment_status' => 'paid',
                    'payment_method' => 'wallet',
                    'transaction_id' => 'WL' . time() . rand(1000, 9999),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mua gói thành công',
                'data' => [
                    'user_package' => $userPackage->load('postingPackage'),
                    'wallet_balance' => $wallet->balance,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi mua gói: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy gói đăng bài hiện tại của user
     */
    public function myPackages(Request $request)
    {
        $user = $request->user();

        $packages = $user->userPackages()
            ->with('postingPackage')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($userPackage) {
                return [
                    'id' => $userPackage->id,
                    'package_name' => $userPackage->postingPackage->name,
                    'duration_months' => $userPackage->postingPackage->duration_months,
                    'amount_paid' => $userPackage->amount_paid,
                    'started_at' => $userPackage->started_at->format('d/m/Y'),
                    'expires_at' => $userPackage->expires_at->format('d/m/Y'),
                    'remaining_days' => $userPackage->remaining_days,
                    'posts_used' => $userPackage->posts_used,
                    'remaining_posts' => $userPackage->remaining_posts,
                    'status' => $userPackage->status,
                    'payment_status' => $userPackage->payment_status,
                    'is_active' => $userPackage->isActive(),
                    'payment_method' => $userPackage->payment_method,
                ];
            });

        $activePackage = $packages->firstWhere('is_active', true);

        return response()->json([
            'success' => true,
            'data' => [
                'packages' => $packages,
                'active_package' => $activePackage,
                'has_active_package' => !is_null($activePackage),
            ],
        ]);
    }

    /**
     * Lấy gói active hiện tại + thông tin lượt đăng miễn phí
     */
    public function currentPackage(Request $request)
    {
        $user = $request->user();

        $activePackage = $user->userPackages()
            ->with('postingPackage')
            ->active()
            ->first();

        $packageData = null;
        if ($activePackage) {
            $packageData = [
                'id' => $activePackage->id,
                'package_name' => $activePackage->postingPackage->name,
                'duration_months' => $activePackage->postingPackage->duration_months,
                'started_at' => $activePackage->started_at->format('d/m/Y'),
                'expires_at' => $activePackage->expires_at->format('d/m/Y'),
                'remaining_days' => $activePackage->remaining_days,
                'remaining_posts' => $activePackage->remaining_posts,
                'can_create_post' => $activePackage->canCreatePost(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $packageData,
            'free_posts_remaining' => $user->free_posts_remaining ?? 0,
            'can_post' => ($user->free_posts_remaining > 0) || ($activePackage && $activePackage->canCreatePost()),
        ]);
    }
}
