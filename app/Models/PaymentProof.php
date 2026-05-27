<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'invoice_id',
        'erp_account_id',
        'file_path',
        'notes',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
