<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Sidebar Navigation --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Daftar Isi</h3>
            <nav class="space-y-1">
                @foreach([
                    'overview' => 'Gambaran Umum',
                    'login' => 'Login & Akun',
                    'santri' => 'Kelola Data Santri',
                    'pembayaran' => 'POS Pembayaran',
                    'tagihan' => 'Tagihan & Invoice',
                    'laporan' => 'Laporan Keuangan',
                    'matrix' => 'Matrix Syahriyyah',
                    'broadcast' => 'Broadcast Penagihan',
                    'surat' => 'Kop Surat & Template',
                    'import_export' => 'Import / Export',
                    'backup' => 'Backup & Restore',
                    'pengaturan' => 'Pengaturan Lainnya',
                ] as $key => $label)
                <button wire:click="$set('section', '{{ $key }}')"
                    class="w-full text-left px-3 py-2 rounded-lg text-sm transition
                    {{ $section === $key ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $label }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- Content --}}
        <div class="lg:col-span-3 fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            @if($section === 'overview')
            <h2 class="text-2xl font-bold mb-4">ERP Pesantren Asy-Syifaa Wal Mahmuudiyyah</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Sistem informasi manajemen terpadu untuk mengelola data santri, keuangan, dan administrasi Pondok Pesantren Asy-Syifaa Wal Mahmuudiyyah.</p>

            <h3 class="text-lg font-semibold mt-6 mb-3">Modul yang Tersedia</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['Kepesantrenan', 'Data santri aktif, alumni, import/export data', 'heroicon-o-academic-cap'],
                    ['Keuangan', 'POS pembayaran, tagihan, laporan, broadcast', 'heroicon-o-banknotes'],
                    ['SPMB', 'Pendaftaran santri baru, dokumen, seleksi', 'heroicon-o-clipboard-document-list'],
                    ['Pengaturan', 'Metode pembayaran, kop surat, backup, manual', 'heroicon-o-cog-6-tooth'],
                ] as $mod)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="font-semibold mb-1">{{ $mod[0] }}</h4>
                    <p class="text-sm text-gray-500">{{ $mod[1] }}</p>
                </div>
                @endforeach
            </div>

            <h3 class="text-lg font-semibold mt-6 mb-3">Hak Akses (Role)</h3>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800"><tr><th class="px-3 py-2 text-left">Role</th><th class="px-3 py-2 text-left">Akses</th></tr></thead>
                <tbody>
                    <tr class="border-t"><td class="px-3 py-2 font-semibold">Superadmin</td><td class="px-3 py-2">Semua modul termasuk backup & pengaturan</td></tr>
                    <tr class="border-t"><td class="px-3 py-2 font-semibold">Admin</td><td class="px-3 py-2">Semua modul kecuali backup</td></tr>
                    <tr class="border-t"><td class="px-3 py-2 font-semibold">Mudir</td><td class="px-3 py-2">Laporan, data santri, broadcast</td></tr>
                    <tr class="border-t"><td class="px-3 py-2 font-semibold">Bendahara</td><td class="px-3 py-2">POS, tagihan, laporan keuangan, metode pembayaran</td></tr>
                    <tr class="border-t"><td class="px-3 py-2 font-semibold">Kepala TU</td><td class="px-3 py-2">Data santri, tagihan, laporan</td></tr>
                    <tr class="border-t"><td class="px-3 py-2 font-semibold">Staf TU</td><td class="px-3 py-2">POS pembayaran, data santri</td></tr>
                    <tr class="border-t"><td class="px-3 py-2 font-semibold">Wali Santri</td><td class="px-3 py-2">Lihat tagihan, upload bukti transfer</td></tr>
                </tbody>
            </table>
            @endif

            @if($section === 'login')
            <h2 class="text-2xl font-bold mb-4">Login & Akun</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <h3>Cara Login</h3>
                <ol>
                    <li>Buka halaman ERP di browser</li>
                    <li>Masukkan username dan password</li>
                    <li>Klik tombol <strong>Masuk</strong></li>
                </ol>
                <h3>Ganti Password</h3>
                <p>Klik nama akun di pojok kanan atas, lalu pilih <strong>Detail Akun</strong>. Isi password baru dan konfirmasi.</p>
                <h3>Lupa Password</h3>
                <p>Hubungi admin/Superadmin untuk mereset password melalui menu <strong>Pengguna > Akun ERP</strong>.</p>
            </div>
            @endif

            @if($section === 'santri')
            <h2 class="text-2xl font-bold mb-4">Kelola Data Santri</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <h3>Melihat Data Santri</h3>
                <p>Menu <strong>Kepesantrenan > Santri Aktif</strong> menampilkan semua santri berstatus aktif. Gunakan filter untuk menyaring berdasarkan kelas, jenjang, atau status tunggakan.</p>
                <h3>Alumni & Non-Aktif</h3>
                <p>Santri alumni, waqof, mutasi, dan status lainnya terpisah di menu <strong>Kepesantrenan > Alumni & Lainnya</strong>.</p>
                <h3>Edit Data Santri</h3>
                <p>Klik ikon Edit pada baris santri. Form terdiri dari 7 tab: Identitas Akademik, Data Pribadi, Keluarga & Domisili, Ayah, Ibu, Wali, Catatan & Keuangan.</p>
                <h3>Cetak Buku SPP</h3>
                <p>Dari halaman POS Pembayaran, setelah memilih santri, klik link <strong>Cetak Buku SPP</strong> di bawah tabel tagihan.</p>
            </div>
            @endif

            @if($section === 'pembayaran')
            <h2 class="text-2xl font-bold mb-4">POS Pembayaran</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <h3>Alur Pembayaran</h3>
                <ol>
                    <li>Buka <strong>Keuangan > POS Pembayaran</strong></li>
                    <li>Cari santri dengan NIS atau nama</li>
                    <li>Lihat daftar tagihan belum lunas (otomatis urut dari yang terlama)</li>
                    <li>Pilih nominal: gunakan tombol cepat (1 bulan, 2 bulan, dst) atau ketik manual</li>
                    <li>Pilih metode pembayaran (Cash / Transfer BSI / Transfer BCA)</li>
                    <li>Jika transfer: isi nomor referensi dan upload bukti transfer</li>
                    <li>Klik <strong>Proses Pembayaran</strong></li>
                    <li>Cetak nota/struk dari tombol yang muncul setelah berhasil</li>
                </ol>
                <h3>Logika Pembayaran</h3>
                <ul>
                    <li><strong>Otomatis alokasi</strong>: pembayaran dialokasikan ke tunggakan terlama dulu</li>
                    <li><strong>Cicilan</strong>: jika nominal kurang dari tagihan, sisa tetap tercatat</li>
                    <li><strong>Lunas</strong>: jika nominal pas dengan tagihan</li>
                    <li><strong>Kelebihan</strong>: jika nominal lebih, otomatis bayar tagihan berikutnya</li>
                </ul>
                <h3>Cetak</h3>
                <ul>
                    <li><strong>Nota A4</strong>: format formal dengan kop surat</li>
                    <li><strong>Struk Thermal</strong>: format kecil untuk printer thermal 80mm</li>
                </ul>
            </div>
            @endif

            @if($section === 'tagihan')
            <h2 class="text-2xl font-bold mb-4">Tagihan & Invoice</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <h3>Generate Tagihan SPP</h3>
                <p>Tagihan SPP otomatis digenerate per bulan Hijriah menggunakan command: <code>php artisan keuangan:generate-spp</code></p>
                <h3>Jenis Tagihan</h3>
                <p>Kelola di <strong>Keuangan > Jenis Biaya</strong>: Syahriyyah, Ujian Semester 1 & 2, Muadalah, Additional.</p>
                <h3>Waqof Otomatis</h3>
                <p>Santri dengan tunggakan 3+ bulan otomatis berstatus Waqof (ditangguhkan). Reactivasi dilakukan setelah pelunasan.</p>
            </div>
            @endif

            @if($section === 'laporan')
            <h2 class="text-2xl font-bold mb-4">Laporan Keuangan</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <h3>Tab Tersedia</h3>
                <ul>
                    <li><strong>Dashboard KPI</strong>: 4 KPI utama, breakdown metode, top tunggakan, tren 12 bulan</li>
                    <li><strong>Ringkasan</strong>: overview tagihan vs terbayar, tunggakan per kelas</li>
                    <li><strong>Daftar Tunggakan</strong>: list santri menunggak, bisa filter per kelas/jenjang</li>
                    <li><strong>Matrix Syahriyyah</strong>: visualisasi santri x 12 bulan</li>
                    <li><strong>Rekap Per Bulan</strong>: progress per bulan Hijriah</li>
                    <li><strong>Buku Setoran</strong>: log transaksi harian/bulanan</li>
                </ul>
                <h3>Export PDF</h3>
                <p>Setiap tab memiliki tombol <strong>Export PDF</strong> untuk mencetak laporan.</p>
            </div>
            @endif

            @if($section === 'matrix')
            <h2 class="text-2xl font-bold mb-4">Matrix Syahriyyah</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <p>Visualisasi tabel santri x 12 bulan Hijriah untuk monitoring pembayaran SPP.</p>
                <h3>Keterangan Simbol</h3>
                <ul>
                    <li><strong class="text-green-600">✓</strong> = Lunas</li>
                    <li><strong class="text-yellow-600">~</strong> = Cicilan (partial)</li>
                    <li><strong class="text-red-600">✗</strong> = Belum Bayar</li>
                    <li><strong class="text-gray-400">—</strong> = Belum Ada Tagihan</li>
                </ul>
                <p>Data otomatis sinkron dengan transaksi yang sudah dicatat di POS Pembayaran.</p>
            </div>
            @endif

            @if($section === 'broadcast')
            <h2 class="text-2xl font-bold mb-4">Broadcast Penagihan</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <p>Menu <strong>Keuangan > Broadcast Penagihan</strong> untuk mengirim pesan penagihan massal ke wali santri.</p>
                <h3>Cara Menggunakan</h3>
                <ol>
                    <li>Filter target: minimal tunggakan (bulan), kelas</li>
                    <li>Pilih channel: WhatsApp dan/atau Notifikasi In-App</li>
                    <li>Opsional: tulis pesan kustom (kosongkan untuk template default)</li>
                    <li>Preview daftar target</li>
                    <li>Klik <strong>Kirim Broadcast</strong></li>
                </ol>
            </div>
            @endif

            @if($section === 'surat')
            <h2 class="text-2xl font-bold mb-4">Kop Surat & Template</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <p>Menu <strong>Pengaturan > Kop Surat</strong> untuk membuat dan mengelola template kop surat.</p>
                <h3>Fitur</h3>
                <ul>
                    <li>Upload logo kiri dan kanan</li>
                    <li>Kustomisasi nama lembaga, alamat, telepon, email, website</li>
                    <li>Set template default untuk semua output PDF</li>
                    <li>Bisa punya beberapa template (misal: Kop Keuangan, Kop Umum)</li>
                </ul>
            </div>
            @endif

            @if($section === 'import_export')
            <h2 class="text-2xl font-bold mb-4">Import / Export Data Santri</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <h3>Export</h3>
                <p>Pilih filter status, lalu klik Download Excel. File berformat .xlsx dengan kolom lengkap.</p>
                <h3>Import</h3>
                <ol>
                    <li>Siapkan file Excel (.xlsx) dengan minimal kolom: <strong>NIS</strong> dan <strong>Nama</strong></li>
                    <li>Upload file, klik Preview untuk melihat 5 baris pertama</li>
                    <li>Klik Import untuk memproses</li>
                    <li>Data existing (by NIS) akan diperbarui, data baru akan dibuat</li>
                </ol>
            </div>
            @endif

            @if($section === 'backup')
            <h2 class="text-2xl font-bold mb-4">Backup & Restore</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <p>Menu <strong>Pengaturan > Backup & Restore</strong> (hanya Superadmin).</p>
                <h3>Backup</h3>
                <p>Klik <strong>Buat Backup Sekarang</strong> untuk mengexport database PostgreSQL ke file .sql.</p>
                <h3>Download</h3>
                <p>Backup tersimpan di server. Klik Download untuk mengunduh file backup.</p>
                <h3>Restore</h3>
                <p>Untuk restore, gunakan command: <code>psql -U username -d database &lt; backup.sql</code></p>
            </div>
            @endif

            @if($section === 'pengaturan')
            <h2 class="text-2xl font-bold mb-4">Pengaturan Lainnya</h2>
            <div class="prose dark:prose-invert max-w-none text-sm">
                <h3>Metode Pembayaran</h3>
                <p>Menu <strong>Pengaturan > Metode Pembayaran</strong> untuk menambah, edit, atau menonaktifkan rekening bank dan channel pembayaran.</p>
                <h3>Jenis Biaya</h3>
                <p>Menu <strong>Keuangan > Jenis Biaya</strong> untuk mengelola jenis tagihan (SPP, Ujian, Muadalah, dll) beserta nominal default.</p>
                <h3>Manajemen Pengguna</h3>
                <p>Menu <strong>Pengguna > Akun ERP</strong> untuk mengelola akun staff, wali santri, dan role.</p>
            </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
