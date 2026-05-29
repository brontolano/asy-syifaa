<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal'      => 'date',
            'suhu_tubuh'   => 'decimal:1',
            'berat_badan'  => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'petugas_id');
    }
}
