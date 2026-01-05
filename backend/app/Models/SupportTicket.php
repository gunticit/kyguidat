<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ticket_number',
        'subject',
        'category',
        'priority',
        'status',
        'assigned_to',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    const CATEGORY_GENERAL = 'general';
    const CATEGORY_PAYMENT = 'payment';
    const CATEGORY_CONSIGNMENT = 'consignment';
    const CATEGORY_ACCOUNT = 'account';
    const CATEGORY_OTHER = 'other';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_WAITING_REPLY = 'waiting_reply';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    /**
     * Get ticket owner
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get assigned admin
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get ticket messages
     */
    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }
}
