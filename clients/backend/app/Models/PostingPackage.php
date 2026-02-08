<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostingPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'duration_months',
        'price',
        'original_price',
        'post_limit',
        'featured_posts',
        'priority_support',
        'features',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:0',
            'original_price' => 'decimal:0',
            'priority_support' => 'boolean',
            'features' => 'array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ];
    }

    /**
     * Get user packages for this posting package
     */
    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('duration_months');
    }

    /**
     * Get discount percentage if original price exists
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100);
        }
        return 0;
    }

    /**
     * Format price for display
     */
    public function getFormattedPriceAttribute()
    {
        return number_format((float) $this->price, 0, ',', '.') . ' đ';
    }

    /**
     * Format original price for display
     */
    public function getFormattedOriginalPriceAttribute()
    {
        if ($this->original_price) {
            return number_format((float) $this->original_price, 0, ',', '.') . ' đ';
        }
        return null;
    }
}
