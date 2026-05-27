<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbDocument extends Model
{
    protected $fillable = [
        'ppdb_registration_id',
        'document_type',
        'file_path',
        'status',
        'rejection_reason',
        'version',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(PpdbRegistration::class, 'ppdb_registration_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'verified_by');
    }
}
