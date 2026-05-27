<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LihatModul extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string|\UnitEnum|null $navigationGroup = 'Dashboard';
    protected static ?string $navigationLabel = 'Lihat Modul';
    protected static ?string $title = 'Modul ERP Pesantren';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.lihat-modul';

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && !$user->hasAnyRole(['Pendaftar', 'Santri', 'Wali Santri']);
    }

    public function getModulesProperty(): array
    {
        $user = auth('erp')->user();

        return [
            // === KEPESANTRENAN ===
            [
                'group' => 'Kepesantrenan',
                'modules' => [
                    [
                        'name' => 'Santri Aktif',
                        'description' => 'Data santri berstatus aktif',
                        'icon' => 'heroicon-o-academic-cap',
                        'url' => '/kepesantrenan/students',
                        'color' => 'emerald',
                        'active' => true,
                    ],
                    [
                        'name' => 'Alumni & Lainnya',
                        'description' => 'Alumni, waqof, mutasi, dll',
                        'icon' => 'heroicon-o-user-group',
                        'url' => '/kepesantrenan/alumni',
                        'color' => 'emerald',
                        'active' => true,
                    ],
                    [
                        'name' => 'Import / Export',
                        'description' => 'Import & export data santri Excel',
                        'icon' => 'heroicon-o-arrow-up-tray',
                        'url' => '/import-export-santri',
                        'color' => 'emerald',
                        'active' => true,
                    ],
                ],
            ],

            // === KEUANGAN ===
            [
                'group' => 'Keuangan',
                'modules' => [
                    [
                        'name' => 'POS Pembayaran',
                        'description' => 'Kasir pembayaran SPP santri',
                        'icon' => 'heroicon-o-banknotes',
                        'url' => '/bayar-tagihan',
                        'color' => 'sky',
                        'active' => true,
                    ],
                    [
                        'name' => 'Jenis Biaya',
                        'description' => 'Kelola jenis tagihan & nominal',
                        'icon' => 'heroicon-o-tag',
                        'url' => '/keuangan/billing-types',
                        'color' => 'sky',
                        'active' => true,
                    ],
                    [
                        'name' => 'Tagihan',
                        'description' => 'Daftar invoice santri',
                        'icon' => 'heroicon-o-document-text',
                        'url' => '/keuangan/invoices',
                        'color' => 'sky',
                        'active' => true,
                    ],
                    [
                        'name' => 'Pembayaran',
                        'description' => 'Riwayat transaksi pembayaran',
                        'icon' => 'heroicon-o-receipt-percent',
                        'url' => '/keuangan/payments',
                        'color' => 'sky',
                        'active' => true,
                    ],
                    [
                        'name' => 'Laporan Keuangan',
                        'description' => 'KPI, matrix, rekap, setoran',
                        'icon' => 'heroicon-o-chart-bar',
                        'url' => '/laporan-keuangan',
                        'color' => 'sky',
                        'active' => true,
                    ],
                    [
                        'name' => 'Broadcast Penagihan',
                        'description' => 'Kirim tagihan massal via WA',
                        'icon' => 'heroicon-o-megaphone',
                        'url' => '/broadcast-penagihan',
                        'color' => 'sky',
                        'active' => true,
                    ],
                ],
            ],

            // === SPMB ===
            [
                'group' => 'SPMB',
                'modules' => [
                    [
                        'name' => 'Data Pendaftar',
                        'description' => 'Kelola calon santri baru',
                        'icon' => 'heroicon-o-clipboard-document-list',
                        'url' => '/spmb/pendaftar',
                        'color' => 'violet',
                        'active' => true,
                    ],
                    [
                        'name' => 'Broadcast Notifikasi',
                        'description' => 'Kirim pengumuman ke pendaftar',
                        'icon' => 'heroicon-o-bell-alert',
                        'url' => '/broadcast-notifikasi',
                        'color' => 'violet',
                        'active' => true,
                    ],
                    [
                        'name' => 'Ujian Online',
                        'description' => 'Ujian masuk calon santri',
                        'icon' => 'heroicon-o-pencil-square',
                        'url' => '#',
                        'color' => 'violet',
                        'active' => false,
                    ],
                    [
                        'name' => 'Payment Gateway',
                        'description' => 'Bayar online (Midtrans/Xendit)',
                        'icon' => 'heroicon-o-credit-card',
                        'url' => '#',
                        'color' => 'violet',
                        'active' => false,
                    ],
                ],
            ],

            // === PENGGUNA ===
            [
                'group' => 'Pengguna',
                'modules' => [
                    [
                        'name' => 'Akun Pengguna',
                        'description' => 'Kelola akun staff, wali, santri',
                        'icon' => 'heroicon-o-users',
                        'url' => '/user-management/erp-accounts',
                        'color' => 'amber',
                        'active' => true,
                    ],
                ],
            ],

            // === PENGATURAN ===
            [
                'group' => 'Pengaturan',
                'modules' => [
                    [
                        'name' => 'Metode Pembayaran',
                        'description' => 'Rekening bank & channel bayar',
                        'icon' => 'heroicon-o-credit-card',
                        'url' => '/pengaturan/payment-methods',
                        'color' => 'rose',
                        'active' => true,
                    ],
                    [
                        'name' => 'Kop Surat',
                        'description' => 'Template header surat resmi',
                        'icon' => 'heroicon-o-document-duplicate',
                        'url' => '/pengaturan/letter-headers',
                        'color' => 'rose',
                        'active' => true,
                    ],
                    [
                        'name' => 'Backup & Restore',
                        'description' => 'Backup database PostgreSQL',
                        'icon' => 'heroicon-o-server-stack',
                        'url' => '/backup-restore',
                        'color' => 'rose',
                        'active' => $user && $user->hasRole('Superadmin'),
                    ],
                    [
                        'name' => 'User Manual',
                        'description' => 'Panduan penggunaan sistem',
                        'icon' => 'heroicon-o-book-open',
                        'url' => '/user-manual',
                        'color' => 'rose',
                        'active' => true,
                    ],
                ],
            ],

            // === COMING SOON ===
            [
                'group' => 'Segera Hadir',
                'modules' => [
                    [
                        'name' => 'E-Rapor',
                        'description' => 'Rapor digital santri',
                        'icon' => 'heroicon-o-clipboard-document-check',
                        'url' => '#',
                        'color' => 'gray',
                        'active' => false,
                    ],
                    [
                        'name' => 'Presensi',
                        'description' => 'Absensi harian santri',
                        'icon' => 'heroicon-o-clock',
                        'url' => '#',
                        'color' => 'gray',
                        'active' => false,
                    ],
                    [
                        'name' => 'Inventaris',
                        'description' => 'Kelola aset & inventaris',
                        'icon' => 'heroicon-o-cube',
                        'url' => '#',
                        'color' => 'gray',
                        'active' => false,
                    ],
                    [
                        'name' => 'Perpustakaan',
                        'description' => 'Katalog & peminjaman buku',
                        'icon' => 'heroicon-o-building-library',
                        'url' => '#',
                        'color' => 'gray',
                        'active' => false,
                    ],
                    [
                        'name' => 'Kesehatan',
                        'description' => 'Rekam medis santri',
                        'icon' => 'heroicon-o-heart',
                        'url' => '#',
                        'color' => 'gray',
                        'active' => false,
                    ],
                    [
                        'name' => 'Asrama',
                        'description' => 'Manajemen kamar & penghuni',
                        'icon' => 'heroicon-o-home-modern',
                        'url' => '#',
                        'color' => 'gray',
                        'active' => false,
                    ],
                ],
            ],
        ];
    }
}
