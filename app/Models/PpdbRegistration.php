<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'ayah_tanggal_lahir' => 'date',
            'ibu_tanggal_lahir' => 'date',
            'wali_tanggal_lahir' => 'date',
            'profile_completed_at' => 'datetime',
            'anak_ke' => 'integer',
            'jumlah_saudara' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'erp_account_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PpdbDocument::class);
    }

    public function selectionStages(): HasMany
    {
        return $this->hasMany(PpdbSelectionStage::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->registration_number)) {
                $year = $model->academic_year ?? date('Y');
                $count = static::whereYear('created_at', date('Y'))->count() + 1;
                $yearShort = substr($year, 0, 4);
                $model->registration_number = sprintf('SPMB/%s/%04d', $yearShort, $count);
            }
        });
    }
}
