<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'code',
        'title',
        'description',
        'address',
        'google_map_link',
        'price',
        'min_price',
        'seller_phone',
        'images',
        'description_files',
        'note_to_admin',
        'status',
        'admin_note',
        'approved_at',
        'sold_at',
        'cancelled_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'images' => 'array',
        'description_files' => 'array',
        'approved_at' => 'datetime',
        'sold_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SELLING = 'selling';
    const STATUS_SOLD = 'sold';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get consignment owner
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get consignment history
     */
    public function histories()
    {
        return $this->hasMany(ConsignmentHistory::class);
    }
}
