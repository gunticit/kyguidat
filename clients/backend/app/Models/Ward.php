<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = ['province_id', 'name', 'type', 'slug', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPE_PHUONG = 'phuong';
    public const TYPE_XA = 'xa';
    public const TYPE_DAC_KHU = 'dac_khu';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($ward) {
            if (empty($ward->slug)) {
                $ward->slug = Str::slug($ward->name);
            }
        });
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PHUONG => 'Phường',
            self::TYPE_XA => 'Xã',
            self::TYPE_DAC_KHU => 'Đặc khu',
            default => $this->type,
        };
    }
}
