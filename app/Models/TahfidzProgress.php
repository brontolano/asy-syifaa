<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahfidzProgress extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'update_terakhir' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getPersenQuranAttribute(): float
    {
        if ($this->target_juz_quran === 0) return 0;
        return round(($this->total_juz_quran / $this->target_juz_quran) * 100, 1);
    }
}
