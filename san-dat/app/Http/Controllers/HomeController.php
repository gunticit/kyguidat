<?php

namespace App\Http\Controllers;

use App\Services\GolangApiService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected GolangApiService $apiService;

    public function __construct(GolangApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index()
    {
        $response = $this->apiService->getConsignments([
            'page' => 1,
            'limit' => 30,
            'sort' => 'latest',
        ]);

        $mapResponse = $this->apiService->getConsignments([
            'page' => 1,
            'limit' => 1000,
            'sort' => 'latest',
        ]);

        $consignments = $response['data'] ?? [];
        $mapConsignments = $mapResponse['data'] ?? [];
        $categories = $this->apiService->getCategories();
        $locations = $this->apiService->getLocations();
        $featuredProvinces = $this->apiService->getFeaturedProvinces();

        return view('home', compact('consignments', 'mapConsignments', 'categories', 'locations', 'featuredProvinces'));
    }
}
