<?php

namespace App\Http\Controllers;

use App\Services\GolangApiService;
use Illuminate\Http\Request;

class ConsignmentController extends Controller
{
    protected GolangApiService $apiService;

    public function __construct(GolangApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display listing of consignments
     */
    public function index(Request $request)
    {
        $params = [
            'page' => $request->get('page', 1),
            'limit' => 12,
            'search' => $request->get('search'),
            'province' => $request->get('province'),
            'lat' => $request->get('lat'),
            'lng' => $request->get('lng'),
            'max_distance' => $request->get('max_distance'),
        ];

        $response = $this->apiService->getConsignments(array_filter($params));

        $consignments = $response['data'] ?? [];
        $meta = $response['meta'] ?? null;
        $categories = $this->apiService->getCategories();
        $provinces = $this->apiService->getProvinces();

        $userLat = $request->get('lat');
        $userLng = $request->get('lng');

        return view('consignments.index', compact('consignments', 'meta', 'categories', 'provinces', 'userLat', 'userLng'));
    }

    /**
     * Display single consignment
     */
    public function show(string $slug)
    {
        $response = $this->apiService->getConsignment($slug);

        if (!$response || !isset($response['data'])) {
            abort(404);
        }

        $consignment = $response['data'];

        // Get related consignments
        $relatedResponse = $this->apiService->getConsignments([
            'province' => $consignment['province'] ?? '',
            'limit' => 4
        ]);
        $relatedConsignments = $relatedResponse['data'] ?? [];

        return view('consignments.show', compact('consignment', 'relatedConsignments'));
    }

    /**
     * API endpoint for AJAX pagination
     */
    public function apiIndex(Request $request)
    {
        $params = [
            'page' => $request->get('page', 1),
            'limit' => $request->get('limit', 30),
            'search' => $request->get('search'),
            'province' => $request->get('province'),
            'lat' => $request->get('lat'),
            'lng' => $request->get('lng'),
            'max_distance' => $request->get('max_distance'),
            'sort' => $request->get('sort', 'latest'),
        ];

        $response = $this->apiService->getConsignments(array_filter($params));

        $data = $response['data'] ?? [];
        $data = collect($data)->map(function ($item) {
            $createdAt = $item['created_at'] ?? null;
            $status = 'Chưa bán';
            if ($createdAt) {
                try {
                    $createdDate = \Carbon\Carbon::parse($createdAt);
                    if ($createdDate->diffInDays(now()) < 5) {
                        $status = $createdDate->locale('vi')->diffForHumans(now(), [
                            'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
                            'options' => \Carbon\Carbon::JUST_NOW | \Carbon\Carbon::ONE_DAY_WORDS
                        ]);
                    }
                } catch (\Exception $e) {
                }
            }
            $item['statusText'] = $status;
            return $item;
        })->toArray();

        return response()->json([
            'data' => $data,
            'total' => $response['meta']['total'] ?? 0,
            'page' => $params['page'],
            'limit' => $params['limit'],
        ]);
    }
}
