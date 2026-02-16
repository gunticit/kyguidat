<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'author_id',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Auto-generate slug from title if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
                // Ensure unique slug
                $count = static::where('slug', 'like', $article->slug . '%')->count();
                if ($count > 0) {
                    $article->slug .= '-' . ($count + 1);
                }
            }
        });
    }

    /**
     * Scope: only published articles
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Relationship: author
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
