<?php

namespace App\Filament\Pages;

use App\Models\PpdbRegistration;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class HasilSeleksi extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $title = 'Hasil Seleksi';
    protected static ?string $slug = 'hasil-seleksi';
    protected static string|\UnitEnum|null $navigationGroup = 'SPMB';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.hasil-seleksi';

    public Collection $registrations;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasRole('Pendaftar');
    }

    public function mount(): void
    {
        $user = auth('erp')->user();
        $this->registrations = $user?->registrations()
            ->orderBy('created_at', 'desc')
            ->get() ?? collect();
    }
}
