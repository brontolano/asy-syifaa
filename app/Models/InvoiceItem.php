<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'billing_type_id',
        'description',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function billingType(): BelongsTo
    {
        return $this->belongsTo(BillingType::class);
    }

    protected static function booted(): void
    {
        static::saved(fn (self $item) => $item->invoice->recalculate());
        static::deleted(fn (self $item) => $item->invoice->recalculate());
    }
}
