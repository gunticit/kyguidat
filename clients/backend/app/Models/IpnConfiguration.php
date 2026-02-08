<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpnConfiguration extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'ipn_url',
        'secret_key',
        'merchant_id',
        'additional_config',
        'is_active',
        'description',
        'created_by',
        'last_triggered_at',
        'trigger_count',
    ];

    protected $casts = [
        'additional_config' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    protected $hidden = [
        'secret_key', // Ẩn secret key khi trả về API
    ];

    /**
     * Available providers
     */
    public const PROVIDERS = [
        'vnpay' => 'VNPay',
        'momo' => 'Momo',
        'zalopay' => 'ZaloPay',
        'bank' => 'Bank Transfer',
        'custom' => 'Custom',
    ];

    /**
     * Get creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(IpnLog::class);
    }

    /**
     * Scope active configurations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by provider
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Generate IPN URL with base
     */
    public function getFullIpnUrlAttribute(): string
    {
        $baseUrl = config('app.url');
        return rtrim($baseUrl, '/') . '/' . ltrim($this->ipn_url, '/');
    }

    /**
     * Increment trigger count
     */
    public function recordTrigger(): void
    {
        $this->increment('trigger_count');
        $this->update(['last_triggered_at' => now()]);
    }
}
