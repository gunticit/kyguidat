<?php

namespace App\Http\Controllers;

use App\Models\Consignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicConsignmentController extends Controller
{
    /**
     * Get all public consignments (approved/selling status only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 100);
        $cacheKey = 'public_consignments_' . md5(json_encode($request->all()));

        // Cache for 5 minutes
        $consignments = Cache::remember($cacheKey, 300, function () use ($request, $perPage) {
            $query = Consignment::query()
                ->whereIn('status', [Consignment::STATUS_APPROVED, Consignment::STATUS_SELLING])
                ->with('user:id,name');

            // Filter by status
            if ($request->has('status') && in_array($request->status, [Consignment::STATUS_APPROVED, Consignment::STATUS_SELLING])) {
                $query->where('status', $request->status);
            }

            // Search by title, address, code
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            // Filter by price range
            if ($request->has('min_price') && is_numeric($request->min_price)) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price') && is_numeric($request->max_price)) {
                $query->where('price', '<=', $request->max_price);
            }

            // Sort
            $sortBy = in_array($request->get('sort_by'), ['price', 'created_at', 'title'])
                ? $request->get('sort_by')
                : 'created_at';
            $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';

            $query->orderBy($sortBy, $sortOrder);

            return $query->paginate($perPage);
        });

        return response()->json([
            'success' => true,
            'data' => $consignments->items(),
            'meta' => [
                'current_page' => $consignments->currentPage(),
                'from' => $consignments->firstItem(),
                'last_page' => $consignments->lastPage(),
                'per_page' => $consignments->perPage(),
                'to' => $consignments->lastItem(),
                'total' => $consignments->total(),
            ],
        ]);
    }

    /**
     * Get single public consignment details
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $consignment = Cache::remember("public_consignment_{$id}", 300, function () use ($id) {
            return Consignment::query()
                ->whereIn('status', [Consignment::STATUS_APPROVED, Consignment::STATUS_SELLING])
                ->with('user:id,name')
                ->find($id);
        });

        if (!$consignment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bất động sản',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $consignment,
        ]);
    }

    /**
     * Get single public consignment by SEO URL slug
     * 
     * @param string $slug
     * @return JsonResponse
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $consignment = Cache::remember("public_consignment_slug_{$slug}", 300, function () use ($slug) {
            return Consignment::query()
                ->whereIn('status', [Consignment::STATUS_APPROVED, Consignment::STATUS_SELLING])
                ->where('seo_url', $slug)
                ->with('user:id,name')
                ->first();
        });

        if (!$consignment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bất động sản',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $consignment,
        ]);
    }
}
