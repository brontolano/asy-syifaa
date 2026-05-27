<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'amount_default',
        'is_recurring',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount_default' => 'decimal:2',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
