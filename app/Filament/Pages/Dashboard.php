<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        $user = auth('erp')->user();

        // Wali punya halaman sendiri (WaliPortal), tidak perlu akses Dashboard ERP
        // Pendaftar & Santri juga tidak perlu Dashboard ERP
        if (! $user) {
            return false;
        }

        return ! $user->hasAnyRole([
            'Pendaftar',
            'Santri',
            'Wali Santri',
            'wali_santri',
            'orang_tua',
            'wali',
        ]);
    }
}
