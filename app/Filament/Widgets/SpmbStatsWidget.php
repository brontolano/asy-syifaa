<?php

namespace App\Filament\Widgets;

use App\Models\PpdbRegistration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SpmbStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Kepala TU', 'Staf TU']);
    }

    protected function getStats(): array
    {
        $currentYear = date('Y') . '/' . (date('Y') + 1);
        $query = PpdbRegistration::where('academic_year', $currentYear);

        $total = (clone $query)->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $registered = (clone $query)->where('status', 'registered')->count();
        $lulus = (clone $query)->where('status', 'lulus')->count();
        $cadangan = (clone $query)->where('status', 'cadangan')->count();
        $tidakLulus = (clone $query)->where('status', 'rejected')->count();
        $enrolled = (clone $query)->where('status', 'enrolled')->count();
        $docComplete = (clone $query)->where('document_status', 'complete')->count();

        return [
            Stat::make('Total Pendaftar', $total)
                ->description('Tahun ajaran ' . $currentYear)
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),
            Stat::make('Menunggu Verifikasi', $pending + $registered)
                ->description('Perlu ditindaklanjuti')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Dokumen Lengkap', $docComplete)
                ->icon('heroicon-o-document-check')
                ->color('info'),
            Stat::make('Lulus Seleksi', $lulus)
                ->description("Cadangan: {$cadangan}")
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Santri Aktif', $enrolled)
                ->icon('heroicon-o-user-group')
                ->color('success'),
            Stat::make('Tidak Lulus', $tidakLulus)
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
