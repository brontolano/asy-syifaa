<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class UjianMasuk extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $title = 'Tes / Ujian Masuk';
    protected static ?string $slug = 'ujian-masuk';
    protected static string|\UnitEnum|null $navigationGroup = 'SPMB';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.ujian-masuk';

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasRole('Pendaftar');
    }
}
