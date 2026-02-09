<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'provider',
        'provider_id',
        'email_verified_at',
        'status',
        'free_posts_remaining',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get user's roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')->withTimestamps();
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is Admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Check if user is Moderator (Kiểm duyệt)
     */
    public function isModerator(): bool
    {
        return $this->hasRole(Role::MODERATOR);
    }

    /**
     * Check if user is Publisher (Đăng bài)
     */
    public function isPublisher(): bool
    {
        return $this->hasRole(Role::PUBLISHER);
    }

    /**
     * Assign role to user
     */
    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching($role);
    }

    /**
     * Remove role from user
     */
    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }
        if ($role) {
            $this->roles()->detach($role);
        }
    }

    /**
     * Get all permissions for this user
     */
    public function getAllPermissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[$permission->name] = true;
            }
        }
        return array_keys($permissions);
    }

    /**
     * Get user's wallet
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get user's consignments
     */
    public function consignments()
    {
        return $this->hasMany(Consignment::class);
    }

    /**
     * Get user's payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get user's support tickets
     */
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Get user's posting packages
     */
    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }
}

