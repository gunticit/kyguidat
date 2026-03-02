<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PageController extends Controller
{
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.golang_api.url', 'http://api-gateway:8080');
    }

    /**
     * Show page by slug
     */
    public function show(string $slug)
    {
        $response = Http::get("{$this->apiUrl}/api/public/pages/{$slug}");

        if (!$response->successful()) {
            abort(404);
        }

        $page = $response->json()['data'] ?? null;

        if (!$page) {
            abort(404);
        }

        return view('pages.dynamic', compact('page'));
    }
}
