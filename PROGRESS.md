# ERP Pesantren Asy-Syifaa — Progress Tracker

## Wave 1 (Selesai - 2026-05-26)
| Tanggal | Task | Status |
|---|---|---|
| 2026-05-26 | Inisialisasi Laravel 13 + Filament 5 + Spatie Permission | done |
| 2026-05-26 | Database schema: erp_accounts, ppdb_*, billing_*, invoices, payments | done |
| 2026-05-26 | Auth guard `erp` + 18 roles seeded | done |
| 2026-05-26 | Filament Resources: PendaftarResource, InvoiceResource, PaymentResource, BillingTypeResource, ErpAccountResource | done |
| 2026-05-26 | Widgets: SpmbStatsWidget, KeuanganStatsWidget | done |
| 2026-05-26 | API: POST /api/spmb/sync, GET /api/spmb/{ref}/status | done |
| 2026-05-26 | Fix Filament 5 breaking changes (Actions namespace, Schema, layout components) | done |
| 2026-05-26 | Fix PostgreSQL jsonb notifications | done |

## Wave 1.5 — Integrasi SPMB Full Workflow (2026-05-27)
| Tanggal | Task | Status |
|---|---|---|
| 2026-05-27 | Dokumentasi awal (PROGRESS.md, ARCHITECTURE.md, CHANGELOG.md) | done |
| 2026-05-27 | Migration: enhance_ppdb, exam_tables, webhook_logs, payment_proofs | done |
| 2026-05-27 | Config spmb.php | done |
| 2026-05-27 | Model updates + WebhookLog + PaymentProof | done |
| 2026-05-27 | Services: SpmbService, WebhookNotificationService | done |
| 2026-05-27 | Events & Listeners (6 events, 6 listeners) | done |
| 2026-05-27 | Notification classes (5 classes) | done |
| 2026-05-27 | API upgrade: SpmbRegisterController v1 | done |
| 2026-05-27 | ForcePasswordChange middleware + ChangePassword page | done |
| 2026-05-27 | Document workflow enhancement (approve/reject/preview) | done |
| 2026-05-27 | Portal Calon Santri: PendaftarDashboard, DokumenSaya, TagihanSaya | done |
| 2026-05-27 | Upload bukti transfer + staff review (via TagihanSaya) | done |
| 2026-05-27 | Kirim credential WA dari dashboard staff | done |
| 2026-05-27 | Broadcast notification page | done |
| 2026-05-27 | Resource access control (canAccess) | done |
| 2026-05-27 | Seeder: DocumentTypeSeeder | done |
| 2026-05-27 | Fix Filament 5 type declarations (navigationGroup, navigationIcon, view) | done |
| 2026-05-27 | Fix getPages() route registration | done |
| 2026-05-27 | Kartu Virtual: QR Code asli (chillerlan/php-qrcode), layout QR kanan besar | done |
| 2026-05-27 | Halaman publik verifikasi: /verifikasi/{registrationNumber} | done |
| 2026-05-27 | Detail Akun: reset password + ganti nomor HP via WA OTP | done |
| 2026-05-27 | Rename Profil Saya → Informasi Saya + auto-redirect ke form | done |
| 2026-05-27 | Hapus duplikat menu Dashboard untuk Pendaftar | done |

## Wave 2 — Enhanced Keuangan & Admin (2026-05-28)
| Tanggal | Task | Status |
|---|---|---|
| 2026-05-28 | Migration: wave2_enhancements (letter_headers, surat_templates, app_settings, proof_image, student fields) | done |
| 2026-05-28 | Model: LetterHeader, SuratTemplate, AppSetting | done |
| 2026-05-28 | PaymentMethodResource CRUD (Pengaturan > Metode Pembayaran) | done |
| 2026-05-28 | LetterHeaderResource CRUD (Pengaturan > Kop Surat) + logo upload | done |
| 2026-05-28 | POS Pembayaran revamp: UI/UX baru, nominal cepat, metode dinamis, upload bukti, cetak link | done |
| 2026-05-28 | PdfService (7 jenis output): Nota A4, Struk Thermal, Buku SPP, Tagihan Massal, Buku Setoran, Laporan Periode, Matrix Syahriyyah | done |
| 2026-05-28 | PdfController + 7 PDF routes (auth:erp) | done |
| 2026-05-28 | 7 Blade templates PDF + header partial (kop surat dinamis) | done |
| 2026-05-28 | Laporan Keuangan 6 tab: Dashboard KPI, Ringkasan, Daftar Tunggakan, Matrix Syahriyyah, Rekap Per Bulan, Buku Setoran | done |
| 2026-05-28 | Dashboard KPI: 4 KPI cards, filter periode, breakdown metode, Top Jenis, Top 10 Tunggakan, Tren 12 Bulan bar chart | done |
| 2026-05-28 | Matrix Syahriyyah: santri × 12 bulan Hijriah, filter tahun/kelas, sticky columns | done |
| 2026-05-28 | Pemisahan Santri Aktif vs Alumni (AlumniResource) | done |
| 2026-05-28 | StudentResource 7 tab form (Identitas, Pribadi, Keluarga, Ayah, Ibu, Wali, Catatan & Keuangan) | done |
| 2026-05-28 | Import/Export Santri (Excel .xlsx via PhpSpreadsheet) | done |
| 2026-05-28 | Backup & Restore (pg_dump, download, hapus) — Superadmin only | done |
| 2026-05-28 | User Manual in-app (12 seksi dokumentasi lengkap) | done |
| 2026-05-28 | Hapus "a.n PP Asy-Syifaa" dari template broadcast penagihan | done |
| 2026-05-28 | Install barryvdh/laravel-dompdf untuk PDF generation | done |
| 2026-05-28 | QC Browser Testing: semua halaman Wave 2 verified | done |
| 2026-05-28 | Halaman Lihat Modul: overview semua modul ERP dengan icon grid kompak (aktif berwarna, nonaktif abu-abu) | done |

## QC Browser Testing — Wave 2 (28 Mei 2026)
| Halaman | Status | Catatan |
|---|---|---|
| Dashboard | ✅ Pass | Navigation groups lengkap (Dashboard, Kepesantrenan, Keuangan, SPMB, Pengguna, Pengaturan) |
| POS Pembayaran | ✅ Pass | Search NIS, info santri, tabel tagihan, nominal cepat, breakdown cash/transfer, upload bukti, cetak link |
| Laporan - Dashboard KPI | ✅ Pass | 4 KPI cards, filter Bulan Ini, breakdown metode, Top 10 Tunggakan, Tren 12 Bulan chart |
| Laporan - Matrix Syahriyyah | ✅ Pass | Grid santri × bulan 1447 H, filter tahun/kelas, simbol warna (✓/~/✗) |
| Metode Pembayaran | ✅ Pass | CRUD 6 metode (cash, transfer_bsi, transfer_bca, qris, va_bsi, ewallet) |
| Kop Surat | ✅ Pass | CRUD, 1 template default "Kop Utama" |
| Backup & Restore | ✅ Pass | Stats: 1,239 santri, 1,067 invoice, 2 pembayaran, 558 akun, 13 MB DB |
| User Manual | ✅ Pass | 12 seksi TOC, semua konten lengkap |
| Import/Export | ✅ Pass | Halaman load OK |
| Alumni & Lainnya | ✅ Pass | Halaman load OK |

### Bug Minor (Non-blocking)
- POS layout kadang single-column saat sidebar terbuka — viewport width issue
- Breakdown Metode menampilkan "Lainnya 100%" pada data lama (payment_channel belum ter-set) — benar untuk transaksi baru

## Catatan Deploy (JANGAN LUPA)
| Item | Detail |
|---|---|
| **APP_URL** | Ubah `.env` dari `http://localhost:8000` → `https://asy-syifaa.com` agar QR code mengarah ke URL production |
| **SPMB_WEBHOOK_URL** | Set ke URL N8N production |
| **Storage link** | Jalankan `php artisan storage:link` di server |
| **pg_dump** | Pastikan `pg_dump` tersedia di PATH server untuk fitur Backup |

## Rencana Selanjutnya (Wave 3+)
- [ ] Ujian Online (tabel DB sudah dibuat di Wave 1.5)
- [ ] Payment Gateway (Midtrans/Xendit) — saat ini placeholder disabled
- [ ] Optimasi PDF rendering (caching, queue)
- [ ] Mobile responsive fine-tuning
- [ ] Automated testing (unit + feature tests)
