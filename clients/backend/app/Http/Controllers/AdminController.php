<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Consignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'total_consignments' => Consignment::count(),
            'pending_consignments' => Consignment::where('status', 'pending')->count(),
            'approved_consignments' => Consignment::where('status', 'approved')->count(),
            'total_transactions' => 0, // TODO: implement when Transaction model exists
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * List all users with pagination
     */
    public function users(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    /**
     * List consignments with filters
     */
    public function consignments(Request $request): JsonResponse
    {
        $query = Consignment::with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->input('per_page', 15);
        $consignments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $consignments->items(),
            'meta' => [
                'current_page' => $consignments->currentPage(),
                'last_page' => $consignments->lastPage(),
                'per_page' => $consignments->perPage(),
                'total' => $consignments->total(),
            ]
        ]);
    }

    /**
     * Show a single consignment
     */
    public function showConsignment($id): JsonResponse
    {
        $consignment = Consignment::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $consignment
        ]);
    }

    /**
     * Store a new consignment
     */
    public function storeConsignment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'order_number' => 'nullable|integer',
            'notification_date' => 'nullable|date',
            'description' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'images' => 'nullable|array',
            'notes' => 'nullable|string',
            'internal_note' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'land_directions' => 'nullable|array',
            'land_types' => 'nullable|array',
            'road_display' => 'nullable|string|max:50',
            'province' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'frontage_actual' => 'nullable|numeric',
            'frontage_range' => 'nullable|string|max:50',
            'area_range' => 'nullable|string|max:50',
            'has_house' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'residential_area' => 'nullable|numeric',
            'road' => 'nullable|string|max:255',
            'area_dimensions' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'google_map_link' => 'nullable|string|max:500',
            'consigner_name' => 'nullable|string|max:255',
            'consigner_phone' => 'nullable|string|max:50',
            'consigner_type' => 'nullable|string|max:50',
            'sheet_number' => 'nullable|string|max:50',
            'parcel_number' => 'nullable|string|max:50',
            'keywords' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'seo_url' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        // Convert empty strings to null for nullable fields
        $validated = $this->sanitizeData($validated);

        $validated['code'] = 'KG' . str_pad(Consignment::max('id') + 1, 6, '0', STR_PAD_LEFT);
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = $validated['status'] ?? 'pending';

        $consignment = Consignment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tạo ký gửi thành công',
            'data' => $consignment->load('user')
        ], 201);
    }

    /**
     * Update a consignment
     */
    public function updateConsignment(Request $request, $id): JsonResponse
    {
        $consignment = Consignment::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category_id' => 'nullable|integer',
            'order_number' => 'nullable|integer',
            'notification_date' => 'nullable|date',
            'description' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'images' => 'nullable|array',
            'notes' => 'nullable|string',
            'internal_note' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'land_directions' => 'nullable|array',
            'land_types' => 'nullable|array',
            'road_display' => 'nullable|string|max:50',
            'province' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'frontage_actual' => 'nullable|numeric',
            'frontage_range' => 'nullable|string|max:50',
            'area_range' => 'nullable|string|max:50',
            'has_house' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'residential_area' => 'nullable|numeric',
            'road' => 'nullable|string|max:255',
            'area_dimensions' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'google_map_link' => 'nullable|string|max:500',
            'consigner_name' => 'nullable|string|max:255',
            'consigner_phone' => 'nullable|string|max:50',
            'consigner_type' => 'nullable|string|max:50',
            'sheet_number' => 'nullable|string|max:50',
            'parcel_number' => 'nullable|string|max:50',
            'keywords' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'seo_url' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        // Convert empty strings to null for nullable fields
        $sanitized = $this->sanitizeData($validated);

        $consignment->update($sanitized);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật ký gửi thành công',
            'data' => $consignment->fresh()->load('user')
        ]);
    }

    /**
     * Delete a consignment
     */
    public function destroyConsignment($id): JsonResponse
    {
        $consignment = Consignment::findOrFail($id);
        $consignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa ký gửi thành công'
        ]);
    }

    /**
     * Approve a consignment
     */
    public function approveConsignment(Request $request, $id): JsonResponse
    {
        $consignment = Consignment::findOrFail($id);
        $consignment->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Duyệt thành công',
            'data' => $consignment
        ]);
    }

    /**
     * Reject a consignment
     */
    public function rejectConsignment(Request $request, $id): JsonResponse
    {
        $consignment = Consignment::findOrFail($id);
        $consignment->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
            'reject_reason' => $request->input('reason'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Từ chối thành công',
            'data' => $consignment
        ]);
    }

    /**
     * List transactions (placeholder)
     */
    public function transactions(Request $request): JsonResponse
    {
        // TODO: implement when Transaction model exists
        return response()->json([
            'success' => true,
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
            ]
        ]);
    }

    /**
     * Convert empty strings to null for nullable fields
     */
    private function sanitizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            // Convert empty strings to null
            if ($value === '' || $value === []) {
                $data[$key] = null;
            }
        }
        return $data;
    }
}
