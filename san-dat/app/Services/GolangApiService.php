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
     * Get single consignment
     */
    public function getConsignment(int $id): ?array
    {
        $response = Http::get("{$this->baseUrl}/api/consignments/{$id}");

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
