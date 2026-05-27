<?php

namespace App\Filament\Pages\Pengaturan;

use Filament\Pages\Page;

class UserManual extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'User Manual';
    protected static ?string $title = 'Dokumentasi & Panduan Pengguna';
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.pages.pengaturan.user-manual';

    public string $section = 'overview';

    public static function canAccess(): bool
    {
        return auth('erp')->check();
    }
}
