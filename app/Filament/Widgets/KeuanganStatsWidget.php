<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KeuanganStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Bendahara']);
    }

    protected function getStats(): array
    {
        $totalTagihan = Invoice::whereNotIn('status', ['cancelled', 'draft'])->sum('total_amount');
        $totalBayar = Payment::sum('amount');
        $tunggakan = $totalTagihan - $totalBayar;
        $santriAktif = Student::where('status', 'aktif')->count();
        $santriTunggakan = Student::where('status', 'aktif')->where('tunggakan_bulan', '>', 0)->count();
        $santriWaqof = Student::where('status', 'waqof')->count();

        return [
            Stat::make('Santri Aktif', number_format($santriAktif))
                ->description('Waqof: ' . $santriWaqof)
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),
            Stat::make('Total Tagihan SPP', 'Rp ' . number_format($totalTagihan, 0, ',', '.'))
                ->icon('heroicon-o-document-text')
                ->color('info'),
            Stat::make('Total Terbayar', 'Rp ' . number_format($totalBayar, 0, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Tunggakan', 'Rp ' . number_format(max(0, $tunggakan), 0, ',', '.'))
                ->description("{$santriTunggakan} santri menunggak")
                ->icon('heroicon-o-exclamation-triangle')
                ->color($tunggakan > 0 ? 'danger' : 'success'),
        ];
    }
}
