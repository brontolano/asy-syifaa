<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsCategory extends Model
{
    protected $fillable = [
        'slug', 'name', 'type', 'description', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CmsPost::class, 'category_id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(CmsGallery::class, 'category_id');
    }
}
