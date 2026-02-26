<?php

namespace App\Http\Controllers;

use App\Services\GolangApiService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected GolangApiService $apiService;

    public function __construct(GolangApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function results(Request $request)
    {
        $params = [
            'page' => $request->get('page', 1),
            'limit' => 12,
            'search' => $request->get('q'),
            'province' => $request->get('province'),
            'district' => $request->get('district'),
            'phone' => $request->get('phone'),
            'property_type' => $request->get('property_type'),
            'house_on_land' => $request->get('house_on_land'),
            'price_range' => $request->get('price_range'),
            'tho_cu' => $request->get('tho_cu'),
            'road_type' => $request->get('road_type'),
            'frontage' => $request->get('frontage'),
            'area_range' => $request->get('area_range'),
            'direction' => $request->get('direction'),
            'so_to' => $request->get('so_to'),
            'so_thua' => $request->get('so_thua'),
            'sort' => $request->get('sort'),
        ];

        $response = $this->apiService->getConsignments(array_filter($params));

        $consignments = $response['data'] ?? [];
        $meta = $response['meta'] ?? null;
        $locations = $this->apiService->getLocations();
        $searchQuery = $request->get('q', '');

        return view('search.results', compact('consignments', 'meta', 'locations', 'searchQuery'));
    }
}
