<?php

namespace App\Services;

use App\Models\User;
use App\Models\Consignment;
use App\Models\ConsignmentHistory;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ConsignmentService
{
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
     * Create new consignment
     */
    public function create(User $user, array $data): Consignment
    {
        $consignment = $user->consignments()->create([
            'code' => $this->generateCode(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'address' => $data['address'],
            'google_map_link' => $data['google_map_link'] ?? null,
            'price' => $data['price'],
            'min_price' => $data['min_price'] ?? null,
            'seller_phone' => $data['seller_phone'],
            'images' => $data['images'] ?? [],
            'description_files' => $data['description_files'] ?? [],
            'note_to_admin' => $data['note_to_admin'] ?? null,
            'status' => Consignment::STATUS_PENDING,
        ]);

        // Create history
        $this->createHistory($consignment, Consignment::STATUS_PENDING, 'Tạo yêu cầu ký gửi mới', $user->id);

        return $consignment;
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

        $consignment->update([
            'title' => $data['title'] ?? $consignment->title,
            'description' => $data['description'] ?? $consignment->description,
            'address' => $data['address'] ?? $consignment->address,
            'google_map_link' => $data['google_map_link'] ?? $consignment->google_map_link,
            'price' => $data['price'] ?? $consignment->price,
            'min_price' => $data['min_price'] ?? $consignment->min_price,
            'seller_phone' => $data['seller_phone'] ?? $consignment->seller_phone,
            'images' => $data['images'] ?? $consignment->images,
            'description_files' => $data['description_files'] ?? $consignment->description_files,
            'note_to_admin' => $data['note_to_admin'] ?? $consignment->note_to_admin,
        ]);

        $this->createHistory($consignment, $consignment->status, 'Cập nhật thông tin ký gửi', $user->id);

        return $consignment->fresh();
    }

    /**
     * Delete consignment
     */
    public function delete(User $user, int $id): bool
    {
        $consignment = $user->consignments()
            ->whereIn('status', [Consignment::STATUS_PENDING, Consignment::STATUS_REJECTED, Consignment::STATUS_CANCELLED])
            ->find($id);

        if (!$consignment) {
            return false;
        }

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

        $consignment->update([
            'status' => Consignment::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->createHistory($consignment, Consignment::STATUS_CANCELLED, 'Đã hủy yêu cầu ký gửi', $user->id);

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
