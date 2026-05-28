<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class CmsPost extends Model
{
    protected $fillable = [
        'category_id', 'slug', 'title', 'content', 'excerpt',
        'featured_image', 'status', 'published_at', 'author_id', 'views_count',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CmsPost $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title) . '-' . Str::random(6);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CmsCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(CmsTag::class, 'cms_post_tags', 'post_id', 'tag_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopeByCategory($query, string $categorySlug)
    {
        return $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
    }
}
