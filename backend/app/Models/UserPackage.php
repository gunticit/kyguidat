<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'posting_package_id',
        'amount_paid',
        'started_at',
        'expires_at',
        'posts_used',
        'featured_posts_used',
        'status',
        'payment_status',
        'payment_method',
        'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:0',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the package
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the posting package
     */
    public function postingPackage()
    {
        return $this->belongsTo(PostingPackage::class);
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('payment_status', 'paid')
                     ->where('expires_at', '>', now());
    }

    /**
     * Check if package is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if package is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->payment_status === 'paid' 
            && !$this->isExpired();
    }

    /**
     * Get remaining days
     */
    public function getRemainingDaysAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Get remaining posts
     */
    public function getRemainingPostsAttribute(): int|string
    {
        $postLimit = $this->postingPackage->post_limit ?? -1;
        if ($postLimit === -1) {
            return 'Không giới hạn';
        }
        return max(0, $postLimit - $this->posts_used);
    }

    /**
     * Check if user can create more posts
     */
    public function canCreatePost(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        $postLimit = $this->postingPackage->post_limit ?? -1;
        if ($postLimit === -1) {
            return true;
        }
        
        return $this->posts_used < $postLimit;
    }

    /**
     * Increment posts used count
     */
    public function incrementPostsUsed(): void
    {
        $this->increment('posts_used');
    }

    /**
     * Mark package as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }
}
