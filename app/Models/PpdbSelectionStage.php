<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbSelectionStage extends Model
{
    protected $fillable = [
        'ppdb_registration_id',
        'stage_name',
        'result',
        'examiner_id',
        'score',
        'notes',
        'conducted_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'conducted_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(PpdbRegistration::class, 'ppdb_registration_id');
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'examiner_id');
    }
}
