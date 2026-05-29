<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPeriod extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'period_id');
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }
}
