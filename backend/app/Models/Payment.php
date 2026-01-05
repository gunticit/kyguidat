<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'method',
        'amount',
        'fee',
        'net_amount',
        'status',
        'gateway_transaction_id',
        'gateway_response',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    const METHOD_VNPAY = 'vnpay';
    const METHOD_MOMO = 'momo';
    const METHOD_BANK_TRANSFER = 'bank_transfer';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get payment owner
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
