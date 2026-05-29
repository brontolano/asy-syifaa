<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityAttendance extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'dicatat_oleh');
    }

    public function getNamaKegiatanDisplayAttribute(): string
    {
        return $this->nama_kegiatan ?? $this->activity?->nama ?? '-';
    }
}
