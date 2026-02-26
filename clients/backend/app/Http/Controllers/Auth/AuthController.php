<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => $result
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

    /**
     * Get current user info
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load(['wallet'])
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendPasswordResetLink($request->email);

        return response()->json([
            'success' => true,
            'message' => 'Link đặt lại mật khẩu đã được gửi đến email của bạn'
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->resetPassword($request->validated());

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công'
        ]);
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        // Social login users (no password) - require confirmation text
        // Regular users - require password
        if ($user->password) {
            $request->validate([
                'password' => 'required|string',
            ]);

            if (!\Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu không đúng'
                ], 422);
            }
        } else {
            $request->validate([
                'confirm' => 'required|in:DELETE,XOA',
            ]);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete user's wallet
        if (method_exists($user, 'wallet') && $user->wallet) {
            $user->wallet()->delete();
        }

        // Delete user's consignments
        if (method_exists($user, 'consignments')) {
            $user->consignments()->delete();
        }

        // Delete user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tài khoản đã được xóa thành công'
        ]);
    }
}
