<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_recurring' => 'boolean',
            'is_active'    => 'boolean',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ActivityAttendance::class);
    }
}
