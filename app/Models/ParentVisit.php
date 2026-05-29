<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentVisit extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_rencana' => 'date',
            'tanggal_aktual'  => 'date',
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

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'diproses_oleh');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu'  => 'Menunggu Konfirmasi',
            'disetujui' => 'Disetujui',
            'selesai'   => 'Selesai',
            'batal'     => 'Dibatalkan',
            default     => $this->status,
        };
    }
}
