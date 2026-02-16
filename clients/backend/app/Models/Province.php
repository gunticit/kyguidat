<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Province extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($province) {
            if (empty($province->slug)) {
                $province->slug = Str::slug($province->name);
            }
        });
    }

    public function wards()
    {
        return $this->hasMany(Ward::class)->orderBy('sort_order')->orderBy('name');
    }

    public function activeWards()
    {
        return $this->hasMany(Ward::class)->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
