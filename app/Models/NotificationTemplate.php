<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'slug', 'name', 'channel', 'subject', 'body_template', 'variables', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'template_id');
    }

    public function renderBody(array $vars): string
    {
        $body = $this->body_template;
        foreach ($vars as $key => $value) {
            $body = str_replace("{{$key}}", $value, $body);
        }
        return $body;
    }
}
