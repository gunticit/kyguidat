<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthService
{
    /**
     * Handle social login from various providers
     */
    public function handleSocialLogin(string $provider, SocialiteUser $socialUser): array
    {
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (!$user) {
            // Check if email already exists (only if email is provided)
            $email = $socialUser->getEmail();
            $existingUser = $email ? User::where('email', $email)->first() : null;

            if ($existingUser) {
                // Link social account to existing user
                $existingUser->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar() ?? $existingUser->avatar,
                ]);
                $user = $existingUser;
            } else {
                // Create new user
                $userEmail = $email ?? ($provider . '_' . $socialUser->getId() . '@noreply.local');
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $userEmail,
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => null,
                ]);

                // Create wallet
                Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'frozen_balance' => 0,
                ]);
            }
        } else {
            // Update avatar if changed
            if ($socialUser->getAvatar()) {
                $user->update(['avatar' => $socialUser->getAvatar()]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('wallet'),
            'token' => $token,
        ];
    }
}
