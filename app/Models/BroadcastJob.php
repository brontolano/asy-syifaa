<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastJob extends Model
{
    protected $fillable = [
        'name', 'template_id', 'channel', 'filter_criteria',
        'total_recipients', 'sent_count', 'failed_count',
        'status', 'started_at', 'completed_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'filter_criteria' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'created_by');
    }
}
