<?php

namespace App\Services;

use App\Models\User;
use App\Models\Consignment;
use App\Models\ConsignmentHistory;
use App\Models\UserPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ConsignmentService
{
    public function __construct(
        private ?ConsignmentWebhookService $webhookService = null
    ) {
        $this->webhookService = $webhookService ?? app(ConsignmentWebhookService::class);
    }

    /**
     * Get list of consignments
     */
    public function getList(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->consignments();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Check if user can create a new post (free or package)
     * Returns: ['allowed' => bool, 'source' => 'free'|'package'|null, 'remaining' => int]
     */
    public function checkPostingQuota(User $user): array
    {
        // 1. Check free posts
        if ($user->free_posts_remaining > 0) {
            return [
                'allowed' => true,
                'source' => 'free',
                'remaining' => $user->free_posts_remaining,
            ];
        }

        // 2. Check active package
        $activePackage = $user->userPackages()
            ->with('postingPackage')
            ->active()
            ->first();

        if ($activePackage && $activePackage->canCreatePost()) {
            return [
                'allowed' => true,
                'source' => 'package',
                'remaining' => $activePackage->remaining_posts,
                'package_name' => $activePackage->postingPackage->name,
            ];
        }

        return [
            'allowed' => false,
            'source' => null,
            'remaining' => 0,
        ];
    }

    /**
     * Get user's posting quota info
     */
    public function getPostingQuota(User $user): array
    {
        $freePosts = $user->free_posts_remaining;

        $activePackage = $user->userPackages()
            ->with('postingPackage')
            ->active()
            ->first();

        $packagePosts = 0;
        $packageInfo = null;

        if ($activePackage) {
            $packagePosts = $activePackage->remaining_posts;
            $packageInfo = [
                'name' => $activePackage->postingPackage->name,
                'expires_at' => $activePackage->expires_at->format('d/m/Y'),
                'remaining_days' => $activePackage->remaining_days,
                'posts_used' => $activePackage->posts_used,
                'post_limit' => $activePackage->postingPackage->post_limit,
            ];
        }

        $totalConsignments = $user->consignments()->count();

        return [
            'free_posts_remaining' => $freePosts,
            'package_posts_remaining' => is_numeric($packagePosts) ? $packagePosts : 0,
            'total_remaining' => $freePosts + (is_numeric($packagePosts) ? $packagePosts : 0),
            'can_post' => $freePosts > 0 || ($activePackage && $activePackage->canCreatePost()),
            'active_package' => $packageInfo,
            'total_consignments' => $totalConsignments,
        ];
    }

    /**
     * Create new consignment
     */
    public function create(User $user, array $data): Consignment
    {
        // Check posting quota
        $quota = $this->checkPostingQuota($user);

        if (!$quota['allowed']) {
            throw new \Exception('Bạn đã hết lượt đăng bài. Vui lòng mua gói để tiếp tục đăng.');
        }

        $images = $data['images'] ?? [];
        // Auto-set featured_image from first image if not provided
        $featuredImage = !empty($images) ? $images[0] : null;

        // Auto-extract lat/lng from google_map_link
        $latitude = null;
        $longitude = null;
        $mapLink = $data['google_map_link'] ?? null;
        if ($mapLink) {
            // Priority 1: !3d (lat) and !4d (lng) — exact pin coordinates
            if (preg_match('/!3d(-?\d+\.?\d*)/', $mapLink, $latM) && preg_match('/!4d(-?\d+\.?\d*)/', $mapLink, $lngM)) {
                $latitude = $latM[1];
                $longitude = $lngM[1];
            // Priority 2: ?q=lat,lng
            } elseif (preg_match('/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $mapLink, $matches)) {
                $latitude = $matches[1];
                $longitude = $matches[2];
            // Priority 3: @lat,lng (viewport center, less accurate)
            } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $mapLink, $matches)) {
                $latitude = $matches[1];
                $longitude = $matches[2];
            // Fallback: resolve short URL
            } elseif (preg_match('/maps\.app\.goo\.gl|goo\.gl\/maps/i', $mapLink)) {
                try {
                    $parser = app(QuickPostParserService::class);
                    $coords = $parser->resolveGoogleMapsCoords($mapLink);
                    $latitude = $coords['latitude'];
                    $longitude = $coords['longitude'];
                } catch (\Throwable $e) {
                    // Silently fail - coordinates are optional
                }
            }
        }

        $consignment = $user->consignments()->create([
            'code' => $this->generateCode(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'address' => $data['address'],
            'google_map_link' => $mapLink,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'price' => $data['price'],
            'min_price' => $data['min_price'] ?? null,
            'seller_phone' => $data['seller_phone'],
            'consigner_name' => $user->name,
            'consigner_phone' => $data['seller_phone'],
            'images' => $images,
            'featured_image' => $featuredImage,
            'description_files' => $data['description_files'] ?? [],
            'note_to_admin' => $data['note_to_admin'] ?? null,
            'order_number' => $this->generateOrderNumber(),
            'status' => Consignment::STATUS_PENDING,
        ]);

        // Deduct from quota
        if ($quota['source'] === 'free') {
            $user->decrement('free_posts_remaining');
        } elseif ($quota['source'] === 'package') {
            $activePackage = $user->userPackages()
                ->with('postingPackage')
                ->active()
                ->first();
            if ($activePackage) {
                $activePackage->incrementPostsUsed();
            }
        }

        // Create history
        $this->createHistory($consignment, Consignment::STATUS_PENDING, 'Tạo yêu cầu ký gửi mới', $user->id);

        // Dispatch webhook
        $this->webhookService?->dispatchCreated($consignment);

        return $consignment;
    }

    /**
     * Reactivate a deactivated consignment
     */
    public function reactivate(User $user, int $id): ?Consignment
    {
        $consignment = $user->consignments()
            ->where('status', Consignment::STATUS_DEACTIVATED)
            ->find($id);

        if (!$consignment) {
            return null;
        }

        $consignment->reactivate();

        $this->createHistory($consignment, Consignment::STATUS_SELLING, 'Mở lại sản phẩm đã tắt tự động', $user->id);

        return $consignment->fresh();
    }

    /**
     * Get consignment by ID
     */
    public function getById(User $user, int $id): ?Consignment
    {
        return $user->consignments()->with('histories.changedBy')->find($id);
    }

    /**
     * Update consignment
     */
    public function update(User $user, int $id, array $data): ?Consignment
    {
        $consignment = $user->consignments()
            ->whereIn('status', [Consignment::STATUS_PENDING, Consignment::STATUS_REJECTED])
            ->find($id);

        if (!$consignment) {
            return null;
        }

        $images = $data['images'] ?? $consignment->images;
        // Auto-update featured_image from first image if images changed
        $featuredImage = !empty($images) ? (is_array($images) ? $images[0] : $consignment->featured_image) : $consignment->featured_image;

        // Auto-extract lat/lng from google_map_link
        $mapLink = $data['google_map_link'] ?? $consignment->google_map_link;
        $latitude = $consignment->latitude;
        $longitude = $consignment->longitude;
        if ($mapLink && empty($latitude)) {
            // Priority 1: !3d (lat) and !4d (lng) — exact pin coordinates
            if (preg_match('/!3d(-?\d+\.?\d*)/', $mapLink, $latM) && preg_match('/!4d(-?\d+\.?\d*)/', $mapLink, $lngM)) {
                $latitude = $latM[1];
                $longitude = $lngM[1];
            // Priority 2: ?q=lat,lng
            } elseif (preg_match('/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $mapLink, $matches)) {
                $latitude = $matches[1];
                $longitude = $matches[2];
            // Priority 3: @lat,lng (viewport center, less accurate)
            } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $mapLink, $matches)) {
                $latitude = $matches[1];
                $longitude = $matches[2];
            }
        }

        $consignment->update([
            'title' => $data['title'] ?? $consignment->title,
            'description' => $data['description'] ?? $consignment->description,
            'address' => $data['address'] ?? $consignment->address,
            'google_map_link' => $mapLink,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'price' => $data['price'] ?? $consignment->price,
            'min_price' => $data['min_price'] ?? $consignment->min_price,
            'seller_phone' => $data['seller_phone'] ?? $consignment->seller_phone,
            'consigner_name' => $user->name,
            'consigner_phone' => $data['seller_phone'] ?? $consignment->consigner_phone,
            'images' => $images,
            'featured_image' => $featuredImage,
            'description_files' => $data['description_files'] ?? $consignment->description_files,
            'note_to_admin' => $data['note_to_admin'] ?? $consignment->note_to_admin,
        ]);

        $this->createHistory($consignment, $consignment->status, 'Cập nhật thông tin ký gửi', $user->id);

        // Dispatch webhook
        $updatedConsignment = $consignment->fresh();
        $this->webhookService?->dispatchUpdated($updatedConsignment, array_keys($data));

        return $updatedConsignment;
    }

    /**
     * Update only the price of a consignment (for approved/selling/deactivated)
     * Admin can update any consignment, regular users only their own
     */
    public function updatePrice(User $user, int $id, float $price): ?Consignment
    {
        $isAdmin = $user->hasRole('admin');

        $query = $isAdmin
            ? Consignment::query()
            : $user->consignments();

        $consignment = $query
            ->whereIn('status', [
                Consignment::STATUS_APPROVED,
                Consignment::STATUS_SELLING,
                Consignment::STATUS_DEACTIVATED,
            ])
            ->find($id);

        if (!$consignment) {
            return null;
        }

        $oldPrice = $consignment->price;
        $consignment->update(['price' => $price]);

        $this->createHistory(
            $consignment,
            $consignment->status,
            'Cập nhật giá: ' . number_format($oldPrice) . ' → ' . number_format($price),
            $user->id
        );

        // Dispatch webhook for ES sync
        $updatedConsignment = $consignment->fresh();
        $this->webhookService?->dispatchUpdated($updatedConsignment, ['price']);

        return $updatedConsignment;
    }

    /**
     * Delete consignment
     * Admin can delete any consignment, regular users only their own
     */
    public function delete(User $user, int $id): bool
    {
        $isAdmin = $user->hasRole('admin');

        $query = $isAdmin
            ? Consignment::query()
            : $user->consignments();

        $consignment = $query
            ->whereIn('status', [
                Consignment::STATUS_PENDING,
                Consignment::STATUS_REJECTED,
                Consignment::STATUS_CANCELLED,
                Consignment::STATUS_APPROVED,
                Consignment::STATUS_SELLING,
                Consignment::STATUS_DEACTIVATED,
            ])
            ->find($id);

        if (!$consignment) {
            return false;
        }

        $this->createHistory($consignment, 'deleted', 'Người dùng xóa bài đăng', $user->id);

        // Dispatch webhook for ES sync
        $this->webhookService?->dispatchStatusChanged($consignment, $consignment->status, 'deleted');

        return $consignment->delete();
    }

    /**
     * Cancel consignment
     */
    public function cancel(User $user, int $id): bool
    {
        $consignment = $user->consignments()
            ->whereIn('status', [Consignment::STATUS_PENDING, Consignment::STATUS_APPROVED, Consignment::STATUS_SELLING])
            ->find($id);

        if (!$consignment) {
            return false;
        }

        $oldStatus = $consignment->status;

        $consignment->update([
            'status' => Consignment::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->createHistory($consignment, Consignment::STATUS_CANCELLED, 'Đã hủy yêu cầu ký gửi', $user->id);

        // Dispatch webhook
        $this->webhookService?->dispatchStatusChanged($consignment, $oldStatus, Consignment::STATUS_CANCELLED);

        return true;
    }

    /**
     * Get consignment history
     */
    public function getHistory(User $user, int $id): array
    {
        $consignment = $user->consignments()->find($id);

        if (!$consignment) {
            return [];
        }

        return $consignment->histories()
            ->with('changedBy:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Generate unique order_number atomically.
     * Uses a transaction + SELECT FOR UPDATE to prevent race conditions.
     * Falls back to retry loop if unique constraint violation occurs.
     */
    private function generateOrderNumber(): int
    {
        $maxRetries = 5;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                return DB::transaction(function () {
                    // Lock all rows to prevent concurrent reads in this critical section
                    $max = DB::table('consignments')
                        ->whereNull('deleted_at')
                        ->lockForUpdate()
                        ->max('order_number') ?? 0;

                    return $max + 1;
                });
            } catch (\Illuminate\Database\QueryException $e) {
                // Unique constraint violation (code 23000 / 1062)
                if ($attempt < $maxRetries - 1 && str_contains($e->getMessage(), '1062')) {
                    usleep(10000 * ($attempt + 1)); // 10ms, 20ms, 30ms back-off
                    continue;
                }
                throw $e;
            }
        }

        throw new \RuntimeException('Không thể tạo số thứ tự duy nhất sau ' . $maxRetries . ' lần thử.');
    }

    /**
     * Generate unique consignment code
     */
    private function generateCode(): string
    {
        $code = 'KG' . date('Ymd') . strtoupper(Str::random(4));

        while (Consignment::where('code', $code)->exists()) {
            $code = 'KG' . date('Ymd') . strtoupper(Str::random(4));
        }

        return $code;
    }

    /**
     * Create consignment history record
     */
    private function createHistory(Consignment $consignment, string $status, string $note, int $changedBy): void
    {
        ConsignmentHistory::create([
            'consignment_id' => $consignment->id,
            'status' => $status,
            'note' => $note,
            'changed_by' => $changedBy,
        ]);
    }
}
