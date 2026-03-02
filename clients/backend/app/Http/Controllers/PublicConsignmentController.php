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
        $perPage = min($request->get('per_page', $request->get('limit', 15)), 100);
        $cacheKey = 'public_consignments_' . md5(json_encode($request->all()));

        // Cache for 1 minute
        $consignments = Cache::remember($cacheKey, 60, function () use ($request, $perPage) {
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

            // Filter by price_range (e.g. "500-1000", "5000+")
            if ($request->has('price_range') && !empty($request->price_range)) {
                $pr = $request->price_range;
                if (str_ends_with($pr, '+')) {
                    $min = (float) str_replace('+', '', $pr) * 1000000;
                    $query->where('price', '>=', $min);
                } elseif (str_contains($pr, '-')) {
                    [$min, $max] = explode('-', $pr);
                    $query->where('price', '>=', (float) $min * 1000000)
                        ->where('price', '<=', (float) $max * 1000000);
                }
            }

            // Filter by province
            if ($request->has('province') && !empty($request->province)) {
                $query->where('province', $request->province);
            }

            // Filter by ward/district
            if ($request->has('district') && !empty($request->district)) {
                $query->where('ward', $request->district);
            }

            // Filter by phone or order_number
            if ($request->has('phone') && !empty($request->phone)) {
                $phone = $request->phone;
                $query->where(function ($q) use ($phone) {
                    $q->where('consigner_phone', 'like', "%{$phone}%")
                        ->orWhere('order_number', $phone);
                });
            }

            // Filter by property_type (land_types)
            if ($request->has('property_type') && !empty($request->property_type)) {
                $query->whereJsonContains('land_types', $request->property_type);
            }

            // Filter by house_on_land
            if ($request->has('house_on_land') && !empty($request->house_on_land)) {
                $val = $request->house_on_land === 'co' ? 'yes' : 'no';
                $query->where('has_house', $val);
            }

            // Filter by tho_cu (residential_type)
            if ($request->has('tho_cu') && !empty($request->tho_cu)) {
                $query->where('residential_type', $request->tho_cu);
            }

            // Filter by road_type
            if ($request->has('road_type') && !empty($request->road_type)) {
                $query->where('road_display', $request->road_type);
            }

            // Filter by frontage range
            if ($request->has('frontage') && !empty($request->frontage)) {
                $fr = $request->frontage;
                if (str_ends_with($fr, '+')) {
                    $min = (float) str_replace('+', '', $fr);
                    $query->where('frontage_actual', '>=', $min);
                } elseif (str_contains($fr, '-')) {
                    [$min, $max] = explode('-', $fr);
                    $query->where('frontage_actual', '>=', (float) $min)
                        ->where('frontage_actual', '<=', (float) $max);
                }
            }

            // Filter by area_range (total area in area_dimensions parsed or area_range field)
            if ($request->has('area_range') && !empty($request->area_range)) {
                $query->where('area_range', $request->area_range);
            }

            // Filter by floor_area_range
            if ($request->has('floor_area_range') && !empty($request->floor_area_range)) {
                $far = $request->floor_area_range;
                if (str_ends_with($far, '+')) {
                    $min = (float) str_replace('+', '', $far);
                    $query->where('floor_area', '>=', $min);
                } elseif (str_contains($far, '-')) {
                    [$min, $max] = explode('-', $far);
                    $query->where('floor_area', '>=', (float) $min)
                        ->where('floor_area', '<=', (float) $max);
                }
            }

            // Filter by direction
            if ($request->has('direction') && !empty($request->direction)) {
                $query->whereJsonContains('land_directions', $request->direction);
            }

            // Filter by so_to (sheet_number)
            if ($request->has('so_to') && !empty($request->so_to)) {
                $query->where('sheet_number', $request->so_to);
            }

            // Filter by so_thua (parcel_number)
            if ($request->has('so_thua') && !empty($request->so_thua)) {
                $query->where('parcel_number', $request->so_thua);
            }

            // Sort
            $sort = $request->get('sort', '');
            switch ($sort) {
                case 'newest':
                    $query->orderByDesc('created_at');
                    break;
                case 'oldest':
                    $query->orderBy('created_at');
                    break;
                case 'price_asc':
                    $query->orderBy('price');
                    break;
                case 'price_desc':
                    $query->orderByDesc('price');
                    break;
                case 'area_asc':
                    $query->orderBy('residential_area');
                    break;
                case 'area_desc':
                    $query->orderByDesc('residential_area');
                    break;
                default:
                    $query->orderBy('display_order')->orderByDesc('created_at');
                    break;
            }

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
