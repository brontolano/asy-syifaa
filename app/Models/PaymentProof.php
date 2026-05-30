<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'type',
        'invoice_id',
        'student_id',
        'erp_account_id',
        'file_path',
        'nominal_transfer',
        'tanggal_transfer',
        'bank_pengirim',
        'nama_pengirim',
        'notes',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at'      => 'datetime',
            'tanggal_transfer' => 'date',
            'nominal_transfer' => 'decimal:2',
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'erp_account_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'reviewed_by');
    }
}
