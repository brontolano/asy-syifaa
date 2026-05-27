<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'ayah_tanggal_lahir' => 'date',
            'ibu_tanggal_lahir' => 'date',
            'wali_tanggal_lahir' => 'date',
            'waqof_at' => 'date',
            'spp_amount' => 'decimal:2',
            'tunggakan_bulan' => 'integer',
            'anak_ke' => 'integer',
            'jumlah_saudara' => 'integer',
        ];
    }

    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(ErpAccount::class, 'erp_account_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(PpdbRegistration::class, 'ppdb_registration_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'student_id_fk');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeWaqof($query)
    {
        return $query->where('status', 'waqof');
    }

    public function scopeHasTunggakan($query)
    {
        return $query->where('tunggakan_bulan', '>', 0);
    }

    // Helpers
    public function getPhoneAttribute(): ?string
    {
        return $this->ayah_no_telepon ?: $this->ibu_no_telepon ?: $this->wali_no_telepon;
    }

    public function getWaliNamaDisplayAttribute(): string
    {
        if ($this->wali_status === 'Ayah Kandung') return $this->ayah_nama ?? '-';
        if ($this->wali_status === 'Ibu Kandung') return $this->ibu_nama ?? '-';
        return $this->wali_nama ?? $this->ayah_nama ?? '-';
    }

    public function getTotalTunggakanAttribute(): float
    {
        return $this->invoices()
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->sum('total_amount') - $this->invoices()
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->sum('paid_amount');
    }

    public function recalculateTunggakan(): void
    {
        $this->tunggakan_bulan = $this->invoices()
            ->where('invoice_type', 'spp')
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->count();
        $this->saveQuietly();
    }
}
