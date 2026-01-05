<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private SocialAuthService $socialAuthService
    ) {}

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $result = $this->socialAuthService->handleSocialLogin('google', $googleUser);
            
            return redirect()->to(
                config('app.frontend_url') . '/auth/callback?token=' . $result['token']
            );
        } catch (\Exception $e) {
            return redirect()->to(
                config('app.frontend_url') . '/login?error=google_auth_failed'
            );
        }
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook(): RedirectResponse
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback(): RedirectResponse
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            $result = $this->socialAuthService->handleSocialLogin('facebook', $facebookUser);
            
            return redirect()->to(
                config('app.frontend_url') . '/auth/callback?token=' . $result['token']
            );
        } catch (\Exception $e) {
            return redirect()->to(
                config('app.frontend_url') . '/login?error=facebook_auth_failed'
            );
        }
    }

    /**
     * Redirect to Zalo OAuth
     */
    public function redirectToZalo(): RedirectResponse
    {
        return Socialite::driver('zalo')->redirect();
    }

    /**
     * Handle Zalo OAuth callback
     */
    public function handleZaloCallback(): RedirectResponse
    {
        try {
            $zaloUser = Socialite::driver('zalo')->user();
            $result = $this->socialAuthService->handleSocialLogin('zalo', $zaloUser);
            
            return redirect()->to(
                config('app.frontend_url') . '/auth/callback?token=' . $result['token']
            );
        } catch (\Exception $e) {
            return redirect()->to(
                config('app.frontend_url') . '/login?error=zalo_auth_failed'
            );
        }
    }
}
