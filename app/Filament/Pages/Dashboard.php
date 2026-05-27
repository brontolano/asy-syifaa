<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && !$user->hasAnyRole(['Pendaftar', 'Santri', 'Wali Santri']);
    }
}
