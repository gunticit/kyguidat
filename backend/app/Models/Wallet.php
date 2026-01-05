<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'frozen_balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'frozen_balance' => 'decimal:2',
    ];

    /**
     * Get wallet owner
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get wallet transactions
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
