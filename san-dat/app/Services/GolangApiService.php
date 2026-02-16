<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GolangApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.golang_api.url', 'http://api-gateway:8080');
    }

    /**
     * Get approved consignments
     */
    public function getConsignments(array $params = []): array
    {
        $response = Http::get("{$this->baseUrl}/api/consignments", $params);

        if ($response->successful()) {
            return $response->json();
        }

        return ['success' => false, 'data' => [], 'error' => 'Failed to fetch data'];
    }

    /**
     * Get single consignment by ID or slug
     */
    public function getConsignment(string $slugOrId): ?array
    {
        $response = Http::get("{$this->baseUrl}/api/consignments/{$slugOrId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Get categories
     */
    public function getCategories(): array
    {
        $response = Http::get("{$this->baseUrl}/api/categories");

        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }

        return [];
    }

    /**
     * Get locations
     */
    public function getLocations(): array
    {
        $response = Http::get("{$this->baseUrl}/api/locations");

        if ($response->successful()) {
            return $response->json()['data'] ?? [];
        }

        return [];
    }
}
