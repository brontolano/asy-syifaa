<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code', 'type', 'name', 'bank_name', 'account_number',
        'account_holder', 'icon', 'qris_image_path', 'instructions',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
