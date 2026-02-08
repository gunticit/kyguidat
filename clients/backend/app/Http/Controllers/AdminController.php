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
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'land_type' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

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
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'land_type' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        $consignment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật ký gửi thành công',
            'data' => $consignment->load('user')
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
}
