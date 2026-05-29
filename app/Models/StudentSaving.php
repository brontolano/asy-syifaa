<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentSaving extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'saldo'        => 'decimal:2',
            'limit_harian' => 'decimal:2',
            'is_frozen'    => 'boolean',
            'last_transaction_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class, 'student_id', 'student_id');
    }
}
