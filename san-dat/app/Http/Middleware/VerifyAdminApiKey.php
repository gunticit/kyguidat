<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verify admin API key for cross-origin settings management.
 * 
 * Used by admin.khodat.com to manage san-dat settings via API
 * without requiring a full auth session (since it's cross-origin).
 * 
 * The API key is set via ADMIN_API_KEY env variable.
 */
class VerifyAdminApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Admin-Api-Key') 
                  ?? $request->header('Authorization');

        // Strip "Bearer " prefix if present
        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        $expectedKey = config('services.admin_api_key');

        if (empty($expectedKey)) {
            return response()->json([
                'message' => 'Admin API key not configured on server',
            ], 500);
        }

        if (empty($apiKey) || !hash_equals($expectedKey, $apiKey)) {
            return response()->json([
                'message' => 'Unauthorized: Invalid admin API key',
            ], 401);
        }

        return $next($request);
    }
}
