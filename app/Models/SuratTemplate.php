<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratTemplate extends Model
{
    protected $fillable = [
        'code', 'name', 'category', 'letter_header_id',
        'body_template', 'paper_size', 'orientation',
        'available_variables', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'available_variables' => 'array',
        ];
    }

    public function letterHeader(): BelongsTo
    {
        return $this->belongsTo(LetterHeader::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
