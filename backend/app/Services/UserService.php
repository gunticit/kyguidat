<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get user profile with wallet info
     */
    public function getProfile(User $user): User
    {
        return $user->load('wallet');
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'] ?? $user->name,
            'phone' => $data['phone'] ?? $user->phone,
            'avatar' => $data['avatar'] ?? $user->avatar,
        ]);

        return $user->fresh()->load('wallet');
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
