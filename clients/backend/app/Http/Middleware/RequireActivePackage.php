<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chặn user thao tác với consignment khi không có gói đăng ký còn hạn.
 * - Bypass cho admin / moderator / auditor (staff) — họ thao tác qua admin panel.
 * - User chỉ pass khi có ít nhất một UserPackage payment_status='paid' và expires_at > now().
 */
class RequireActivePackage
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Staff không bị chặn — họ vận hành qua admin routes có middleware role riêng.
        if ($user->hasRole('admin') || $user->hasRole('moderator') || $user->hasRole('auditor')) {
            return $next($request);
        }

        $hasActive = $user->userPackages()
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', now())
            ->exists();

        if (!$hasActive) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đã hết gói đăng ký. Vui lòng mua gói mới để tiếp tục thao tác với bài đăng.',
                'code' => 'no_active_package',
            ], 403);
        }

        return $next($request);
    }
}
