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
        ];

        $response = $this->apiService->getConsignments(array_filter($params));

        $consignments = $response['data'] ?? [];
        $meta = $response['meta'] ?? null;
        $categories = $this->apiService->getCategories();
        $locations = $this->apiService->getLocations();

        return view('consignments.index', compact('consignments', 'meta', 'categories', 'locations'));
    }

    /**
     * Display single consignment
     */
    public function show(int $id)
    {
        $response = $this->apiService->getConsignment($id);

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
}
