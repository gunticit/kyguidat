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
        'category_id',
        'code',
        'order_number',
        'notification_date',
        'title',
        'description',
        'featured_image',
        'images',
        'description_files',
        'notes',
        'note_to_admin',
        'internal_note',
        'type',
        'land_directions',
        'land_types',
        'road_display',
        'province',
        'ward',
        'frontage_actual',
        'frontage_range',
        'area_range',
        'has_house',
        'address',
        'residential_area',
        'road',
        'area_dimensions',
        'latitude',
        'longitude',
        'google_map_link',
        'consigner_name',
        'consigner_phone',
        'consigner_type',
        'sheet_number',
        'parcel_number',
        'keywords',
        'price',
        'min_price',
        'seller_phone',
        'seo_url',
        'display_order',
        'status',
        'admin_note',
        'reject_reason',
        'approved_at',
        'sold_at',
        'cancelled_at',
    ];

    protected $casts = [
        'price' => 'float',
        'min_price' => 'float',
        'frontage_actual' => 'float',
        'residential_area' => 'float',
        'images' => 'array',
        'description_files' => 'array',
        'land_directions' => 'array',
        'land_types' => 'array',
        'notification_date' => 'date',
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

