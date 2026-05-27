<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'invoice_type',
        'hijri_label',
        'student_name',
        'student_id',
        'student_id_fk',
        'ppdb_registration_id',
        'billing_period_id',
        'hijri_billing_period_id',
        'total_amount',
        'paid_amount',
        'status',
        'due_date',
        'issued_at',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(PpdbRegistration::class, 'ppdb_registration_id');
    }

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
            'issued_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id_fk');
    }

    public function billingPeriod(): BelongsTo
    {
        return $this->belongsTo(BillingPeriod::class);
    }

    public function hijriBillingPeriod(): BelongsTo
    {
        return $this->belongsTo(HijriBillingPeriod::class, 'hijri_billing_period_id');
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->total_amount - ($this->paid_amount ?? 0));
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculate(): void
    {
        $this->total_amount = $this->items()->sum('amount');
        $this->paid_amount = $this->payments()->sum('amount');

        $this->status = match (true) {
            $this->paid_amount >= $this->total_amount => 'paid',
            $this->paid_amount > 0 => 'partial',
            $this->due_date && $this->due_date->isPast() => 'overdue',
            default => $this->status,
        };

        $this->saveQuietly();
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->invoice_number)) {
                $count = static::whereYear('created_at', date('Y'))->count() + 1;
                $model->invoice_number = sprintf('INV-%s-%05d', date('Y'), $count);
            }
            $model->paid_amount ??= 0;
            $model->status ??= 'draft';
        });
    }
}
