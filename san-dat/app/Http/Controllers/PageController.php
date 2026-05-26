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
        $page = null;
        try {
            $response = Http::get("{$this->apiUrl}/api/public/pages/{$slug}");
            if ($response->successful()) {
                $page = $response->json()['data'] ?? null;
            }
        } catch (\Throwable $e) {
            // Gracefully ignore API errors and let fallback handle it
        }

        if (!$page) {
            $staticViewMap = [
                'chinh-sach-bao-mat' => 'pages.privacy-policy',
                'dieu-khoan-su-dung' => 'pages.terms',
                'xoa-tai-khoan' => 'pages.delete-account',
            ];
            if (isset($staticViewMap[$slug]) && view()->exists($staticViewMap[$slug])) {
                return view($staticViewMap[$slug]);
            }
            abort(404);
        }

        return view('pages.dynamic', compact('page'));
    }
}
