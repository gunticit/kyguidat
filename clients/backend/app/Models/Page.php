<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'display_order',
    ];

    /**
     * Auto-generate slug from title if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
                $original = $page->slug;
                $count = 1;
                while (static::where('slug', $original)->exists()) {
                    $page->slug = $original . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Scope: only published pages
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
