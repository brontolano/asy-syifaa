<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsGalleryItem extends Model
{
    protected $fillable = [
        'gallery_id', 'image_path', 'caption', 'sort_order',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(CmsGallery::class, 'gallery_id');
    }
}
