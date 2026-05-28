<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPermission extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_mulai'    => 'date',
            'tanggal_selesai'  => 'date',
            'diproses_at'      => 'datetime',
            'santri_keluar_at' => 'datetime',
            'santri_kembali_at'=> 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'pengaju_wali_id');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'diproses_oleh');
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['menunggu', 'disetujui']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu'   => 'Menunggu Persetujuan',
            'disetujui'  => 'Disetujui',
            'ditolak'    => 'Ditolak',
            'selesai'    => 'Selesai',
            default => $this->status,
        };
    }

    public function getJenisLabelAttribute(): string
    {
        return match($this->jenis_izin) {
            'pulang'         => 'Pulang ke Rumah',
            'keluar_area'    => 'Keluar Area Pondok',
            'kegiatan_luar'  => 'Kegiatan di Luar',
            'sakit'          => 'Izin Sakit',
            'lainnya'        => 'Lainnya',
            default => $this->jenis_izin,
        };
    }
}
