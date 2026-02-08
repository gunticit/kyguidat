<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard overview data
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getOverview($request->user());

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->dashboardService->getStats($request->user());

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get recent activities
     */
    public function recentActivities(Request $request): JsonResponse
    {
        $activities = $this->dashboardService->getRecentActivities(
            $request->user(),
            $request->get('limit', 10)
        );

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
}
