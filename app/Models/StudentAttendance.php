<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'dicatat_oleh');
    }

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal', now()->month)
                     ->whereYear('tanggal', now()->year);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status_kehadiran) {
            'hadir' => 'Hadir',
            'sakit' => 'Sakit',
            'izin'  => 'Izin',
            'alfa'  => 'Tidak Hadir',
            default => $this->status_kehadiran,
        };
    }

    public function getKesehatanLabelAttribute(): string
    {
        return match($this->status_kesehatan) {
            'sehat'       => 'Sehat',
            'sakit_ringan'=> 'Sakit Ringan',
            'sakit_berat' => 'Sakit Berat',
            'rujukan'     => 'Dirujuk',
            default => $this->status_kesehatan,
        };
    }
}
