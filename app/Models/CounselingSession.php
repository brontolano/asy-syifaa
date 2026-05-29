<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingSession extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_preferensi' => 'date',
            'tanggal_aktual'     => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'wali_account_id');
    }

    public function konselor(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'konselor_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu'    => 'Menunggu',
            'dijadwalkan' => 'Dijadwalkan',
            'selesai'     => 'Selesai',
            'batal'       => 'Dibatalkan',
            default       => $this->status,
        };
    }
}
