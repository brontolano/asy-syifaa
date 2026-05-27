<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaqofLog extends Model
{
    protected $fillable = [
        'student_id', 'action', 'reason',
        'tunggakan_bulan', 'tunggakan_amount', 'actioned_by',
    ];

    protected function casts(): array
    {
        return [
            'tunggakan_amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'actioned_by');
    }
}
