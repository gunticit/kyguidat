<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'group',
        'description',
    ];

    /**
     * Permission groups
     */
    const GROUP_USERS = 'users';
    const GROUP_CONSIGNMENTS = 'consignments';
    const GROUP_PAYMENTS = 'payments';
    const GROUP_TICKETS = 'tickets';
    const GROUP_SETTINGS = 'settings';
    const GROUP_ROLES = 'roles';

    /**
     * Get roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
