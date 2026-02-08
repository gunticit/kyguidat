<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'consignment_id',
        'status',
        'note',
        'changed_by',
    ];

    /**
     * Get parent consignment
     */
    public function consignment()
    {
        return $this->belongsTo(Consignment::class);
    }

    /**
     * Get user who made the change
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
