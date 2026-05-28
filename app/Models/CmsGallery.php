<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CmsGallery extends Model
{
    protected $fillable = [
        'category_id', 'slug', 'title', 'description', 'cover_image',
        'status', 'published_at', 'author_id',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CmsGallery $gallery) {
            if (empty($gallery->slug)) {
                $gallery->slug = Str::slug($gallery->title) . '-' . Str::random(6);
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

    public function items(): HasMany
    {
        return $this->hasMany(CmsGalleryItem::class, 'gallery_id')->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
