<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class WaliPortal extends Page
{
    protected static string $view = 'filament.pages.wali-portal';

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationLabel = 'Portal Wali';

    protected static ?string $title = 'Portal Orang Tua / Wali';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'wali-portal';

    /**
     * Hanya wali yang bisa akses halaman ini.
     */
    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['wali_santri', 'orang_tua', 'wali', 'Wali Santri']);
    }

    /**
     * Wali tidak muncul di navigasi umum (sudah ada di atas sendiri).
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getViewData(): array
    {
        $user = auth('erp')->user();

        // Coba ambil data santri yang terkait dengan wali ini
        $santriList = collect();

        // Cek relasi via wali_account_id di tabel students (jika sudah ada)
        if (method_exists($user, 'santriAsWali')) {
            $santriList = $user->santriAsWali()->get();
        }

        // Cek juga dari ppdb_registrations (jika masih pendaftar)
        $ppdbList = collect();
        if (method_exists($user, 'ppdbRegistrations')) {
            $ppdbList = $user->ppdbRegistrations()->latest()->get();
        }

        return [
            'user'       => $user,
            'santriList' => $santriList,
            'ppdbList'   => $ppdbList,
            'pwaUrl'     => rtrim(config('app.pwa_url', 'https://app.asy-syifaa.com'), '/'),
            'ssoUrl'     => route('auth.sso-wali'),
        ];
    }
}
