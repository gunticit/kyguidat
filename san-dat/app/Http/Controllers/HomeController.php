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

        $consignments = $response['data'] ?? [];
        $categories = $this->apiService->getCategories();
        $locations = $this->apiService->getLocations();
        $featuredProvinces = $this->apiService->getFeaturedProvinces();

        return view('home', compact('consignments', 'categories', 'locations', 'featuredProvinces'));
    }
}
