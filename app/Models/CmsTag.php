<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CmsTag extends Model
{
    protected $fillable = ['slug', 'name'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(CmsPost::class, 'cms_post_tags', 'tag_id', 'post_id');
    }
}
