<x-filament-panels::page>

    {{-- ===== BANNER SELAMAT DATANG ===== --}}
    <div class="rounded-2xl overflow-hidden shadow-lg border border-emerald-100 dark:border-emerald-900 mb-6">
        <div class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-500 px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-academic-cap class="w-8 h-8 text-white" />
                </div>
                <div>
                    <p class="text-emerald-100 text-sm font-medium">Selamat datang,</p>
                    <h2 class="text-white text-xl font-bold leading-tight">{{ $user->name ?? $user->username }}</h2>
                    <p class="text-emerald-200 text-xs mt-0.5">Portal Orang Tua &amp; Wali Santri &mdash; Pondok Pesantren Asy-Syifaa</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== BANNER APLIKASI PWA (UTAMA) ===== --}}
    <div class="rounded-2xl overflow-hidden shadow-xl border border-emerald-200 dark:border-emerald-800 mb-6">

        {{-- Bagian atas: ilustrasi + info app --}}
        <div class="bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-400 p-6">
            <div class="flex flex-col md:flex-row items-center gap-6">

                {{-- Icon Aplikasi --}}
                <div class="flex-shrink-0">
                    <div class="w-28 h-28 md:w-32 md:h-32 rounded-3xl bg-white shadow-2xl flex items-center justify-center border-4 border-white/30">
                        <img
                            src="{{ asset('images/favicon.png') }}"
                            alt="Asy-Syifaa App"
                            class="w-20 h-20 md:w-24 md:h-24 object-contain rounded-2xl"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <div style="display:none" class="w-20 h-20 md:w-24 md:h-24 items-center justify-center">
                            <x-heroicon-o-device-phone-mobile class="w-14 h-14 text-emerald-500" />
                        </div>
                    </div>
                </div>

                {{-- Info Aplikasi --}}
                <div class="flex-1 text-center md:text-left">
                    <div class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur rounded-full px-3 py-1 mb-3">
                        <div class="w-2 h-2 rounded-full bg-green-300 animate-pulse"></div>
                        <span class="text-white text-xs font-semibold uppercase tracking-wide">Tersedia Sekarang</span>
                    </div>

                    <h3 class="text-white text-2xl md:text-3xl font-bold mb-1">Asy-Syifaa App</h3>
                    <p class="text-emerald-100 text-sm md:text-base mb-4">
                        Pantau perkembangan santri, informasi tagihan, pengumuman, dan komunikasi pesantren &mdash; semua dalam satu aplikasi.
                    </p>

                    {{-- Fitur bullet --}}
                    <div class="grid grid-cols-2 gap-2 text-xs text-emerald-50 mb-5">
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-300 flex-shrink-0" />
                            <span>Info tagihan &amp; SPP</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-300 flex-shrink-0" />
                            <span>Perkembangan santri</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-300 flex-shrink-0" />
                            <span>Pengumuman pesantren</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-300 flex-shrink-0" />
                            <span>Notifikasi real-time</span>
                        </div>
                    </div>

                    {{-- Tombol Buka Aplikasi --}}
                    <a
                        href="{{ $ssoUrl }}"
                        class="inline-flex items-center gap-2.5 bg-white text-emerald-700 font-bold px-6 py-3 rounded-xl shadow-lg hover:bg-emerald-50 active:scale-95 transition-all duration-150 text-sm md:text-base"
                    >
                        <x-heroicon-s-device-phone-mobile class="w-5 h-5" />
                        Buka Aplikasi Sekarang
                        <x-heroicon-s-arrow-right class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>

        {{-- Bagian bawah: label & info teknis --}}
        <div class="bg-white dark:bg-gray-800 px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                    <x-heroicon-o-lock-closed class="w-4 h-4 text-emerald-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Login otomatis dengan akun ini</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tidak perlu login ulang &mdash; klik tombol di atas untuk langsung masuk</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <x-heroicon-o-globe-alt class="w-4 h-4" />
                <span>{{ $pwaUrl }}</span>
            </div>
        </div>
    </div>

    {{-- ===== DATA SANTRI (jika ada) ===== --}}
    @if($santriList->isNotEmpty())
        <x-filament::section class="mb-6">
            <x-slot name="heading">Data Santri Anda</x-slot>
            <x-slot name="description">Informasi santri yang terdaftar di bawah pengawasan Anda</x-slot>

            <div class="space-y-3">
                @foreach($santriList as $santri)
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-o-user class="w-5 h-5 text-emerald-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $santri->full_name ?? $santri->name ?? 'Santri' }}</p>
                            <p class="text-xs text-gray-500">{{ $santri->nis ?? '' }}{{ $santri->class_name ? ' &bull; ' . $santri->class_name : '' }}</p>
                        </div>
                        <span class="flex-shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                            Aktif
                        </span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    {{-- ===== DATA PPDB (jika masih pendaftar) ===== --}}
    @if($ppdbList->isNotEmpty() && $santriList->isEmpty())
        <x-filament::section class="mb-6">
            <x-slot name="heading">Status Pendaftaran</x-slot>

            <div class="space-y-3">
                @foreach($ppdbList as $reg)
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-blue-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $reg->student_name ?? 'Calon Santri' }}</p>
                            <p class="text-xs text-gray-500">No. Reg: {{ $reg->registration_number ?? '-' }}</p>
                        </div>
                        <span class="flex-shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300">
                            {{ $reg->status_label ?? ucfirst($reg->status ?? 'Proses') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    {{-- ===== PANDUAN SINGKAT ===== --}}
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">Cara Menggunakan Aplikasi</x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-emerald-700 dark:text-emerald-300 font-bold text-sm">1</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-0.5">Klik "Buka Aplikasi"</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Klik tombol di atas untuk membuka Asy-Syifaa App dengan login otomatis</p>
                </div>
            </div>
            <div class="flex gap-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20">
                <div class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-blue-700 dark:text-blue-300 font-bold text-sm">2</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-0.5">Pasang ke Layar Utama</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Di browser HP, pilih "Tambah ke layar utama" agar seperti aplikasi native</p>
                </div>
            </div>
            <div class="flex gap-3 p-4 rounded-xl bg-violet-50 dark:bg-violet-900/20">
                <div class="w-9 h-9 rounded-lg bg-violet-100 dark:bg-violet-900/50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-violet-700 dark:text-violet-300 font-bold text-sm">3</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-0.5">Notifikasi Aktif</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Izinkan notifikasi agar selalu update info tagihan &amp; pengumuman</p>
                </div>
            </div>
        </div>

        <div class="mt-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
            <div class="flex items-start gap-2">
                <x-heroicon-o-information-circle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-amber-700 dark:text-amber-300">
                    Akun aplikasi Anda sama dengan akun login ini. Gunakan nomor HP dan password yang sama saat login langsung di aplikasi.
                </p>
            </div>
        </div>
    </x-filament::section>

</x-filament-panels::page>
