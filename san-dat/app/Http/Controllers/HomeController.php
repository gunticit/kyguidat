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
            'limit' => 8
        ]);

        $consignments = $response['data'] ?? [];
        $categories = $this->apiService->getCategories();
        $locations = $this->apiService->getLocations();

        return view('home', compact('consignments', 'categories', 'locations'));
    }
}
