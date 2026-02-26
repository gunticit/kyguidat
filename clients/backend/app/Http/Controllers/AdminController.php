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
     * Delete a user
     */
    public function destroyUser(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng',
            ], 404);
        }

        // Prevent deleting yourself
        if ($request->user() && $request->user()->id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tài khoản của chính bạn',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa người dùng thành công',
        ]);
    }

    /**
     * Update a user
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng',
            ], 404);
        }

        $data = $request->only(['name', 'email', 'phone']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->input('password'));
        }

        $user->update($data);

        // Sync roles if provided
        if ($request->has('roles')) {
            $roleNames = $request->input('roles', []);
            $roleIds = \App\Models\Role::whereIn('name', $roleNames)->pluck('id')->toArray();
            $user->roles()->sync($roleIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật người dùng thành công',
            'data' => $user->load('roles'),
        ]);
    }

    /**
     * List customers (users registered from frontend, without admin roles)
     */
    public function customers(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $query = User::whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['admin', 'moderator', 'publisher']);
        })->with('consignments:id,user_id,title,status')
            ->withCount('consignments');

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
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

        if ($province = $request->input('province')) {
            $query->where('province', $province);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('consigner_phone', 'like', '%' . $search . '%');
            });
        }

        if ($consignerName = $request->input('consigner_name')) {
            $query->where('consigner_name', 'like', '%' . $consignerName . '%');
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
        // Sanitize data before validation to handle empty strings
        $data = $this->sanitizeData($request->all());
        $request->merge($data);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'order_number' => 'nullable|integer|unique:consignments,order_number',
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
            'seo_url' => 'nullable|string|max:500|unique:consignments,seo_url',
            'display_order' => 'nullable|integer',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        // Auto-generate seo_url from title if not provided
        if (empty($validated['seo_url']) && !empty($validated['title'])) {
            $validated['seo_url'] = \Illuminate\Support\Str::slug($validated['title']);
            // Ensure uniqueness by appending suffix if needed
            $original = $validated['seo_url'];
            $count = 1;
            while (Consignment::where('seo_url', $validated['seo_url'])->exists()) {
                $validated['seo_url'] = $original . '-' . $count;
                $count++;
            }
        }

        // Auto-assign order_number if not provided
        if (empty($validated['order_number'])) {
            $maxOrder = Consignment::max('order_number') ?? 0;
            $validated['order_number'] = $maxOrder + 1;
        }

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

        // Sanitize data before validation to handle empty strings
        $data = $this->sanitizeData($request->all());
        $request->merge($data);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'category_id' => 'nullable|integer',
            'order_number' => 'nullable|integer|unique:consignments,order_number,' . $id,
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
            'seo_url' => 'nullable|string|max:500|unique:consignments,seo_url,' . $id,
            'display_order' => 'nullable|integer',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        $consignment->update($validated);

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

    // =============================================
    // SUPPORT TICKET MANAGEMENT (Admin)
    // =============================================

    /**
     * List all support tickets (admin view - all users)
     */
    public function supportTickets(Request $request): JsonResponse
    {
        $query = \App\Models\SupportTicket::with(['user:id,name,email,avatar']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Count by status for badges
        $counts = [
            'open' => \App\Models\SupportTicket::where('status', 'open')->count(),
            'in_progress' => \App\Models\SupportTicket::where('status', 'in_progress')->count(),
            'waiting_reply' => \App\Models\SupportTicket::where('status', 'waiting_reply')->count(),
            'closed' => \App\Models\SupportTicket::where('status', 'closed')->count(),
            'total' => \App\Models\SupportTicket::count(),
        ];

        $tickets = $query->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'counts' => $counts,
        ]);
    }

    /**
     * Show support ticket details with messages
     */
    public function showSupportTicket($id): JsonResponse
    {
        $ticket = \App\Models\SupportTicket::with([
            'user:id,name,email,avatar,phone',
            'messages.user:id,name,avatar'
        ])->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hỗ trợ'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Admin reply to support ticket
     */
    public function replySupportTicket(Request $request, $id): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array',
        ]);

        $ticket = \App\Models\SupportTicket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hỗ trợ'
            ], 404);
        }

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'attachments' => $request->attachments ?? [],
            'is_admin' => true,
        ]);

        // Update ticket status to waiting_reply (waiting for user)
        if ($ticket->status === 'open' || $ticket->status === 'in_progress') {
            $ticket->update(['status' => 'waiting_reply']);
        }

        $message->load('user:id,name,avatar');

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi phản hồi',
            'data' => $message
        ]);
    }

    /**
     * Update support ticket status
     */
    public function updateTicketStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,waiting_reply,resolved,closed',
        ]);

        $ticket = \App\Models\SupportTicket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hỗ trợ'
            ], 404);
        }

        $updateData = ['status' => $request->status];
        if ($request->status === 'closed') {
            $updateData['closed_at'] = now();
        }

        $ticket->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái',
            'data' => $ticket->fresh()->load('user:id,name,email,avatar')
        ]);
    }

    /**
     * Close support ticket
     */
    public function closeSupportTicket(Request $request, $id): JsonResponse
    {
        $ticket = \App\Models\SupportTicket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hỗ trợ'
            ], 404);
        }

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đóng yêu cầu hỗ trợ',
            'data' => $ticket->fresh()
        ]);
    }
}
