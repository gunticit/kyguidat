<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_admin',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_admin' => 'boolean',
    ];

    /**
     * Get parent ticket
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * Get message author
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
