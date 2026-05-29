<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGrade extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kkm'          => 'integer',
            'nilai_harian' => 'decimal:2',
            'nilai_uts'    => 'decimal:2',
            'nilai_uas'    => 'decimal:2',
            'nilai_akhir'  => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'period_id');
    }

    public function getLulusAttribute(): bool
    {
        return $this->nilai_akhir !== null && $this->nilai_akhir >= $this->kkm;
    }
}
