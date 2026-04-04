<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.golang_api.url', 'http://api-gateway:8080');
    }

    /**
     * List published articles
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);

        $response = Http::get("{$this->apiUrl}/api/public/articles", [
            'page' => $page,
            'per_page' => 12,
        ]);

        $data = $response->successful() ? $response->json() : ['data' => [], 'total' => 0];
        $articles = $data['data'] ?? [];
        $total = $data['total'] ?? 0;

        return view('articles.index', compact('articles', 'total', 'page'));
    }

    /**
     * Show single article by slug
     */
    public function show(string $slug)
    {
        $response = Http::get("{$this->apiUrl}/api/public/articles/{$slug}");

        if (!$response->successful()) {
            abort(404);
        }

        $article = $response->json()['data'] ?? null;

        if (!$article) {
            abort(404);
        }

        // Get recent articles for sidebar
        $recentResponse = Http::get("{$this->apiUrl}/api/public/articles", ['per_page' => 5]);
        $recentArticles = $recentResponse->successful() ? ($recentResponse->json()['data'] ?? []) : [];

        // Load site settings for OG meta
        $settings = [];
        if (Storage::exists('settings.json')) {
            $settings = json_decode(Storage::get('settings.json'), true) ?? [];
        }

        return view('articles.show', compact('article', 'recentArticles', 'settings'));
    }

    /**
     * Contact page
     */
    public function contact()
    {
        $settings = [];
        if (Storage::exists('settings.json')) {
            $settings = json_decode(Storage::get('settings.json'), true) ?? [];
        }

        return view('contact', compact('settings'));
    }
}
