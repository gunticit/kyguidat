<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  Permission name to check
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - No user found',
            ], 401);
        }

        // Admin có toàn quyền
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Kiểm tra permission cụ thể
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You do not have permission to access this resource',
                'required_permission' => $permission,
            ], 403);
        }

        return $next($request);
    }
}
