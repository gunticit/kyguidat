<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private SocialAuthService $socialAuthService
    ) {
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $result = $this->socialAuthService->handleSocialLogin('google', $googleUser);

            return redirect()->to(
                config('app.frontend_url') . '/auth/callback?token=' . $result['token']
            );
        } catch (\Exception $e) {
            Log::error('Google auth failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback(): RedirectResponse
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            $result = $this->socialAuthService->handleSocialLogin('facebook', $facebookUser);

            return redirect()->to(
                config('app.frontend_url') . '/auth/callback?token=' . $result['token']
            );
        } catch (\Exception $e) {
            Log::error('Facebook auth failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->to(
                config('app.frontend_url') . '/login?error=facebook_auth_failed'
            );
        }
    }

    /**
     * Redirect to Zalo OAuth v4
     */
    public function redirectToZalo(): RedirectResponse
    {
        $appId = config('services.zalo.client_id');
        $redirectUri = config('services.zalo.redirect');

        // Generate code verifier and challenge for PKCE
        $codeVerifier = bin2hex(random_bytes(32));
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        // Store code_verifier in cache (keyed by a random state)
        $state = bin2hex(random_bytes(16));
        cache()->put('zalo_cv_' . $state, $codeVerifier, 600);

        $params = http_build_query([
            'app_id' => $appId,
            'redirect_uri' => $redirectUri,
            'code_challenge' => $codeChallenge,
            'state' => $state,
        ]);

        return redirect()->to("https://oauth.zaloapp.com/v4/permission?{$params}");
    }

    /**
     * Handle Zalo OAuth v4 callback
     */
    public function handleZaloCallback(): RedirectResponse
    {
        try {
            $code = request()->get('code');
            $state = request()->get('state');

            if (!$code) {
                throw new \Exception('No authorization code received from Zalo');
            }

            // Retrieve code_verifier from cache
            $codeVerifier = cache()->pull('zalo_cv_' . $state);
            if (!$codeVerifier) {
                throw new \Exception('Code verifier expired or not found');
            }

            $appId = config('services.zalo.client_id');
            $appSecret = config('services.zalo.client_secret');
            $redirectUri = config('services.zalo.redirect');

            // Exchange code for access token (Zalo v4)
            $tokenResponse = \Http::asForm()
                ->withHeaders(['secret_key' => $appSecret])
                ->post('https://oauth.zaloapp.com/v4/access_token', [
                    'code' => $code,
                    'app_id' => $appId,
                    'grant_type' => 'authorization_code',
                    'code_verifier' => $codeVerifier,
                ]);

            $tokenData = $tokenResponse->json();

            if (!isset($tokenData['access_token'])) {
                throw new \Exception('Failed to get Zalo access token: ' . json_encode($tokenData));
            }

            $accessToken = $tokenData['access_token'];

            // Get user info from Zalo
            $userResponse = \Http::withHeaders([
                'access_token' => $accessToken,
            ])->get('https://graph.zalo.me/v2.0/me', [
                        'fields' => 'id,name,picture',
                    ]);

            $userData = $userResponse->json();

            if (!isset($userData['id'])) {
                throw new \Exception('Failed to get Zalo user info: ' . json_encode($userData));
            }

            // Create a simple user object for SocialAuthService
            $zaloUser = new \Laravel\Socialite\Two\User();
            $zaloUser->id = $userData['id'];
            $zaloUser->name = $userData['name'] ?? 'Zalo User';
            $zaloUser->email = null; // Zalo doesn't provide email
            $zaloUser->avatar = $userData['picture']['data']['url'] ?? null;

            $result = $this->socialAuthService->handleSocialLogin('zalo', $zaloUser);

            return redirect()->to(
                config('app.frontend_url') . '/auth/callback?token=' . $result['token']
            );
        } catch (\Exception $e) {
            Log::error('Zalo auth failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->to(
                config('app.frontend_url') . '/login?error=zalo_auth_failed'
            );
        }
    }

    /**
     * Redirect to GitHub OAuth
     */
    public function redirectToGithub(): RedirectResponse
    {
        return Socialite::driver('github')->stateless()->scopes(['user:email'])->redirect();
    }

    /**
     * Handle GitHub OAuth callback
     */
    public function handleGithubCallback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->stateless()->user();
            $result = $this->socialAuthService->handleSocialLogin('github', $githubUser);

            return redirect()->to(
                config('app.frontend_url') . '/auth/callback?token=' . $result['token']
            );
        } catch (\Exception $e) {
            Log::error('GitHub auth failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->to(
                config('app.frontend_url') . '/login?error=github_auth_failed'
            );
        }
    }
}
