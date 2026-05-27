<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'event',
        'payload',
        'response_body',
        'http_status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response_body' => 'array',
            'sent_at' => 'datetime',
        ];
    }
}
