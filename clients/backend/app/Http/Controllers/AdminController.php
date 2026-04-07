<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Consignment;
use App\Models\PostingPackage;
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

        // Sync roles if provided (frontend sends role IDs)
        if ($request->has('roles')) {
            $user->roles()->sync($request->input('roles', []));
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
        // Only select fields needed for list view (exclude heavy fields like description, images, etc.)
        $query = Consignment::select([
            'id',
            'code',
            'order_number',
            'title',
            'category',
            'price',
            'status',
            'consigner_name',
            'province',
            'user_id',
            'featured_image',
            'reject_reason',
            'published_at',
            'deactivated_at',
            'auto_deactivated',
            'created_at',
            'updated_at'
        ])->with('user:id,name,email');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($province = $request->input('province')) {
            $query->where('province', $province);
        }

        if ($search = $request->input('search')) {
            // Support comma-separated search terms
            $terms = array_filter(array_map('trim', explode(',', $search)));

            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhere(function ($sq) use ($term) {
                        $sq->where('title', 'like', '%' . $term . '%')
                            ->orWhere('code', 'like', '%' . $term . '%')
                            ->orWhere('address', 'like', '%' . $term . '%')
                            ->orWhere('consigner_phone', 'like', '%' . $term . '%')
                            ->orWhere('seller_phone', 'like', '%' . $term . '%')
                            ->orWhere('keywords', 'like', '%' . $term . '%')
                            ->orWhere('order_number', 'like', '%' . $term . '%');
                    });
                }
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
            'category' => 'nullable|string|max:255',
            'order_number' => ['nullable', 'integer', \Illuminate\Validation\Rule::unique('consignments', 'order_number')->whereNull('deleted_at')],
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
            'residential_type' => 'nullable|string|in:full,partial,none',
            'road' => 'nullable|string|max:255',
            'area_dimensions' => 'nullable|string|max:100',
            'floor_area' => 'nullable|numeric',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'google_map_link' => 'nullable|string|max:2000',
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
            while (Consignment::where('seo_url', $validated['seo_url'])->exists() || \App\Models\Article::where('slug', $validated['seo_url'])->exists()) {
                $validated['seo_url'] = $original . '-' . $count;
                $count++;
            }
        } elseif (!empty($validated['seo_url'])) {
            // Check cross-table uniqueness
            if (\App\Models\Article::where('slug', $validated['seo_url'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'SEO URL đã được sử dụng bởi một bài viết',
                    'errors' => ['seo_url' => ['SEO URL đã được sử dụng bởi một bài viết']],
                ], 422);
            }
        }

        // Auto-assign order_number if not provided
        if (empty($validated['order_number'])) {
            $maxOrder = Consignment::max('order_number') ?? 0;
            $validated['order_number'] = $maxOrder + 1;
        }

        // Generate unique code (same format as ConsignmentService)
        $code = 'KG' . date('Ymd') . strtoupper(\Illuminate\Support\Str::random(4));
        while (Consignment::withTrashed()->where('code', $code)->exists()) {
            $code = 'KG' . date('Ymd') . strtoupper(\Illuminate\Support\Str::random(4));
        }
        $validated['code'] = $code;
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = $validated['status'] ?? 'pending';

        // Auto-extract lat/lng from google_map_link if not provided
        if (!empty($validated['google_map_link']) && (empty($validated['latitude']) || empty($validated['longitude']))) {
            if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $validated['google_map_link'], $matches)) {
                $validated['latitude'] = $matches[1];
                $validated['longitude'] = $matches[2];
            }
        }

        $consignment = Consignment::create($validated);

        // Trigger ES sync
        $this->triggerEsSync();

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
            'category' => 'nullable|string|max:255',
            'order_number' => ['nullable', 'integer', \Illuminate\Validation\Rule::unique('consignments', 'order_number')->ignore($id)->whereNull('deleted_at')],
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
            'residential_type' => 'nullable|string|in:full,partial,none',
            'road' => 'nullable|string|max:255',
            'area_dimensions' => 'nullable|string|max:100',
            'floor_area' => 'nullable|numeric',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'google_map_link' => 'nullable|string|max:2000',
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

        // Check cross-table uniqueness for seo_url
        if (!empty($validated['seo_url']) && \App\Models\Article::where('slug', $validated['seo_url'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'SEO URL đã được sử dụng bởi một bài viết',
                'errors' => ['seo_url' => ['SEO URL đã được sử dụng bởi một bài viết']],
            ], 422);
        }

        // Auto-extract lat/lng from google_map_link if not provided
        if (!empty($validated['google_map_link']) && (empty($validated['latitude']) || empty($validated['longitude']))) {
            if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $validated['google_map_link'], $matches)) {
                $validated['latitude'] = $matches[1];
                $validated['longitude'] = $matches[2];
            }
        }

        $consignment->update($validated);

        // Trigger ES sync
        $this->triggerEsSync();

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

        // Trigger ES sync
        $this->triggerEsSync();

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

        // Trigger ES sync
        $this->triggerEsSync();

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

        // Trigger ES sync
        $this->triggerEsSync();

        return response()->json([
            'success' => true,
            'message' => 'Từ chối thành công',
            'data' => $consignment
        ]);
    }

    /**
     * Reactivate a deactivated consignment (Admin)
     */
    public function reactivateConsignment(Request $request, $id): JsonResponse
    {
        $consignment = Consignment::findOrFail($id);

        if ($consignment->status !== 'deactivated') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể bật lại bài đã tắt'
            ], 400);
        }

        $consignment->update([
            'status' => 'selling',
            'auto_deactivated' => false,
            'deactivated_at' => null,
            'published_at' => now(),
        ]);

        // Create history
        \App\Models\ConsignmentHistory::create([
            'consignment_id' => $consignment->id,
            'status' => 'selling',
            'note' => 'Admin bật lại bài đăng',
            'changed_by' => $request->user()->id,
        ]);

        // Trigger ES sync
        $this->triggerEsSync();

        return response()->json([
            'success' => true,
            'message' => 'Đã bật lại bài đăng',
            'data' => $consignment->fresh()
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
     * Resolve a shortened Google Maps URL to its full URL with coordinates
     */
    public function resolveMapUrl(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url|max:2000',
        ]);

        $url = $request->input('url');

        // Only allow Google Maps short URLs
        if (!preg_match('/^https?:\/\/(maps\.app\.goo\.gl|goo\.gl\/maps|g\.co\/maps)/', $url)) {
            return response()->json([
                'success' => false,
                'message' => 'URL không phải link rút gọn Google Maps',
            ], 422);
        }

        try {
            // Follow redirects using cURL to get the final URL
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_NOBODY => true, // HEAD request only (faster)
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ]);

            curl_exec($ch);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể resolve URL: ' . $error,
                ], 500);
            }

            // Extract lat/lng from the final URL
            $latitude = null;
            $longitude = null;

            if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $finalUrl, $matches)) {
                $latitude = $matches[1];
                $longitude = $matches[2];
            } elseif (preg_match('/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $finalUrl, $matches)) {
                $latitude = $matches[1];
                $longitude = $matches[2];
            } elseif (preg_match('/!3d(-?\d+\.?\d*)/', $finalUrl, $latMatch) &&
                       preg_match('/!4d(-?\d+\.?\d*)/', $finalUrl, $lngMatch)) {
                $latitude = $latMatch[1];
                $longitude = $lngMatch[1];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'original_url' => $url,
                    'resolved_url' => $finalUrl,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi resolve URL: ' . $e->getMessage(),
            ], 500);
        }
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

    /**
     * Trigger Elasticsearch sync via Go API Gateway webhook
    // ==========================================
    // Posting Packages Management
    // ==========================================

    /**
     * List all posting packages (admin)
     */
    public function postingPackages(Request $request): JsonResponse
    {
        $query = PostingPackage::query()->orderBy('sort_order')->orderBy('id');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active') && $request->get('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $packages = $query->get()->map(function ($pkg) {
            return [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'slug' => $pkg->slug,
                'description' => $pkg->description,
                'duration_months' => $pkg->duration_months,
                'price' => $pkg->price,
                'original_price' => $pkg->original_price,
                'formatted_price' => $pkg->formatted_price,
                'post_limit' => $pkg->post_limit,
                'featured_posts' => $pkg->featured_posts,
                'priority_support' => $pkg->priority_support,
                'features' => $pkg->features,
                'is_active' => $pkg->is_active,
                'is_popular' => $pkg->is_popular,
                'sort_order' => $pkg->sort_order,
                'subscribers' => $pkg->userPackages()->count(),
                'created_at' => $pkg->created_at,
                'updated_at' => $pkg->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    /**
     * Show a posting package
     */
    public function showPostingPackage($id): JsonResponse
    {
        $pkg = PostingPackage::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pkg,
        ]);
    }

    /**
     * Create a posting package
     */
    public function storePostingPackage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posting_packages,slug',
            'description' => 'nullable|string',
            'duration_months' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'post_limit' => 'required|integer|min:-1',
            'featured_posts' => 'nullable|integer|min:0',
            'priority_support' => 'boolean',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
            // Ensure unique
            $originalSlug = $validated['slug'];
            $i = 1;
            while (PostingPackage::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $i++;
            }
        }

        $package = PostingPackage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tạo gói thành công',
            'data' => $package,
        ], 201);
    }

    /**
     * Update a posting package
     */
    public function updatePostingPackage(Request $request, $id): JsonResponse
    {
        $package = PostingPackage::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:posting_packages,slug,' . $id,
            'description' => 'nullable|string',
            'duration_months' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'post_limit' => 'sometimes|integer|min:-1',
            'featured_posts' => 'nullable|integer|min:0',
            'priority_support' => 'boolean',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $package->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật gói thành công',
            'data' => $package->fresh(),
        ]);
    }

    /**
     * Delete a posting package
     */
    public function destroyPostingPackage($id): JsonResponse
    {
        $package = PostingPackage::findOrFail($id);

        // Check if any users are using this package
        $activeCount = $package->userPackages()->where('status', 'active')->count();
        if ($activeCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa gói đang có {$activeCount} người dùng đang sử dụng. Hãy tắt gói (is_active = false) thay vì xóa.",
            ], 400);
        }

        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa gói thành công',
        ]);
    }

    /**
     * Trigger Elasticsearch resync
     */
    private function triggerEsSync(): void
    {
        try {
            $apiGatewayUrl = config('services.golang_api.url', 'http://api-gateway:8080');
            \Illuminate\Support\Facades\Http::timeout(2)
                ->post("{$apiGatewayUrl}/internal/es-sync");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('ES sync trigger failed: ' . $e->getMessage());
        }
    }
}
