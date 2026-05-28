<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahfidzRecord extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_setor' => 'date',
            'jumlah_ayat'   => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function ustadz(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'ustadz_id');
    }

    public function getNilaiLabelAttribute(): string
    {
        return match($this->nilai) {
            'A' => 'Mumtaz (A)',
            'B' => 'Jayyid (B)',
            'C' => 'Maqbul (C)',
            'D' => 'Rajih (D)',
            default => '-',
        };
    }

    public function getJenisLabelAttribute(): string
    {
        return match($this->jenis_setor) {
            'ziyadah'  => 'Ziyadah (Hafalan Baru)',
            'murajaah' => "Muraja'ah (Ulangan)",
            'tasmi'    => "Tasmi' (Simaan)",
            default => $this->jenis_setor,
        };
    }
}
