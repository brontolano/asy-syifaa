<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HijriBillingPeriod extends Model
{
    protected $fillable = [
        'hijri_month', 'hijri_year', 'hijri_month_name', 'label',
        'gregorian_start', 'gregorian_end', 'due_date', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gregorian_start' => 'date',
            'gregorian_end' => 'date',
            'due_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'hijri_billing_period_id');
    }

    public static function getHijriMonthName(int $month): string
    {
        return match ($month) {
            1 => 'Muharram',
            2 => 'Safar',
            3 => 'Rabiul Awal',
            4 => 'Rabiul Akhir',
            5 => 'Jumadil Awal',
            6 => 'Jumadil Akhir',
            7 => 'Rajab',
            8 => "Sya'ban",
            9 => 'Ramadhan',
            10 => 'Syawal',
            11 => 'Dzulqa\'dah',
            12 => 'Dzulhijjah',
            default => 'Unknown',
        };
    }
}
