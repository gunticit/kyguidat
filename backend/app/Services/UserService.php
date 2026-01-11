<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get user profile with wallet info
     */
    public function getProfile(User $user): array
    {
        $user->load('wallet');
        
        // Build social accounts status based on provider field
        $socialAccounts = [
            'google' => $user->provider === 'google',
            'facebook' => $user->provider === 'facebook',
            'zalo' => $user->provider === 'zalo',
        ];
        
        return array_merge($user->toArray(), [
            'social_accounts' => $socialAccounts,
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): array
    {
        $user->update([
            'name' => $data['name'] ?? $user->name,
            'phone' => $data['phone'] ?? $user->phone,
            'avatar' => $data['avatar'] ?? $user->avatar,
        ]);

        $user = $user->fresh()->load('wallet');
        
        // Build social accounts status based on provider field
        $socialAccounts = [
            'google' => $user->provider === 'google',
            'facebook' => $user->provider === 'facebook',
            'zalo' => $user->provider === 'zalo',
        ];
        
        return array_merge($user->toArray(), [
            'social_accounts' => $socialAccounts,
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(User $user, array $data): bool
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            return false;
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return true;
    }
}
