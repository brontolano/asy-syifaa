<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterHeader extends Model
{
    protected $fillable = [
        'name', 'institution_name', 'address', 'phone', 'email',
        'website', 'logo_path', 'secondary_logo_path', 'tagline',
        'is_default', 'is_active', 'extra_fields',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'extra_fields' => 'array',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first() ?? static::first();
    }
}
