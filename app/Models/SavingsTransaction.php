<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransaction extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'nominal'       => 'decimal:2',
            'saldo_sesudah' => 'decimal:2',
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
}
