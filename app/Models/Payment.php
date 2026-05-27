<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'student_id',
        'payment_date',
        'amount',
        'payment_method',
        'payment_channel',
        'reference_number',
        'proof_image',
        'received_by',
        'notes',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'received_by');
    }

    protected static function booted(): void
    {
        static::saved(fn (self $payment) => $payment->invoice->recalculate());
        static::deleted(fn (self $payment) => $payment->invoice->recalculate());
    }
}
