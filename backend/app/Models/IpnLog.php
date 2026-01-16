<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpnLog extends Model
{
    protected $fillable = [
        'ipn_configuration_id',
        'provider',
        'transaction_id',
        'order_id',
        'amount',
        'status',
        'response_code',
        'request_data',
        'response_data',
        'ip_address',
        'error_message',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    /**
     * Get configuration
     */
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(IpnConfiguration::class, 'ipn_configuration_id');
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by provider
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Create log entry
     */
    public static function createLog(array $data): self
    {
        return self::create([
            'ipn_configuration_id' => $data['ipn_configuration_id'] ?? null,
            'provider' => $data['provider'] ?? 'unknown',
            'transaction_id' => $data['transaction_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'status' => $data['status'] ?? self::STATUS_PENDING,
            'response_code' => $data['response_code'] ?? null,
            'request_data' => $data['request_data'] ?? null,
            'response_data' => $data['response_data'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'error_message' => $data['error_message'] ?? null,
        ]);
    }
}
