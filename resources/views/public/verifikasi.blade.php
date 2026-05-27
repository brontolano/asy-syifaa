<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Peserta - Pondok Pesantren Asy-Syifaa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    {{-- Header --}}
    <header class="bg-gradient-to-r from-emerald-700 to-emerald-600 text-white shadow-lg">
        <div class="max-w-3xl mx-auto px-4 py-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold">Verifikasi Peserta</h1>
                <p class="text-emerald-100 text-sm">Pondok Pesantren Asy-Syifaa</p>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-8">
        {{-- Search Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-1">Cari Status Pendaftaran</h2>
            <p class="text-sm text-gray-500 mb-4">Masukkan nomor registrasi untuk melihat status pendaftaran.</p>
            <form action="{{ route('public.verifikasi') }}" method="GET" class="flex gap-3">
                <input type="text" name="q" value="{{ $query ?? '' }}"
                       placeholder="Contoh: SPMB/2026/0001"
                       class="flex-1 rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm px-4 py-3">
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-2 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
            </form>
        </div>

        @if($query && !$registration)
            {{-- Not Found --}}
            <div class="bg-white rounded-2xl shadow-sm border border-red-200 p-8 text-center">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.072 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Data Tidak Ditemukan</h3>
                <p class="text-sm text-gray-500">Nomor registrasi <strong>"{{ $query }}"</strong> tidak ditemukan. Pastikan nomor yang dimasukkan sudah benar.</p>
            </div>
        @elseif($registration)
            {{-- Result Card --}}
            @php
                $statusMap = [
                    'registered' => ['label' => 'Terdaftar', 'color' => 'blue', 'icon' => 'clipboard-document-check'],
                    'document_review' => ['label' => 'Review Dokumen', 'color' => 'yellow', 'icon' => 'document-magnifying-glass'],
                    'document_review_done' => ['label' => 'Dokumen Lengkap', 'color' => 'emerald', 'icon' => 'document-check'],
                    'lulus' => ['label' => 'Lulus Seleksi', 'color' => 'emerald', 'icon' => 'academic-cap'],
                    'cadangan' => ['label' => 'Cadangan', 'color' => 'amber', 'icon' => 'clock'],
                    'tidak_lulus' => ['label' => 'Tidak Lulus', 'color' => 'red', 'icon' => 'x-circle'],
                    'enrolled' => ['label' => 'Santri Aktif', 'color' => 'emerald', 'icon' => 'check-badge'],
                ];
                $status = $statusMap[$registration->status] ?? ['label' => ucfirst($registration->status), 'color' => 'gray', 'icon' => 'question-mark-circle'];

                $docApproved = $registration->documents->where('status', 'approved')->count();
                $docTotal = 8;
                $isFemale = strtolower($registration->gender ?? '') === 'p' || strtolower($registration->gender ?? '') === 'perempuan';
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                {{-- Green Header --}}
                <div class="bg-gradient-to-r from-emerald-700 to-emerald-500 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div>
                            <p class="text-emerald-100 text-xs uppercase tracking-wider font-semibold">Verifikasi Identitas</p>
                            <p class="text-white font-bold">Pondok Pesantren Asy-Syifaa</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        @if($status['color'] === 'emerald') bg-emerald-100 text-emerald-800
                        @elseif($status['color'] === 'blue') bg-blue-100 text-blue-800
                        @elseif($status['color'] === 'yellow') bg-yellow-100 text-yellow-800
                        @elseif($status['color'] === 'amber') bg-amber-100 text-amber-800
                        @elseif($status['color'] === 'red') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif
                    ">{{ $status['label'] }}</span>
                </div>

                {{-- Body --}}
                <div class="p-6">
                    <div class="flex items-start gap-5">
                        {{-- Avatar --}}
                        <div class="w-20 h-24 bg-gray-100 rounded-xl border-2 border-gray-200 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            @if(!$isFemale && $registration->foto_url)
                                <img src="{{ Storage::url($registration->foto_url) }}" alt="Foto" class="w-full h-full object-cover">
                            @elseif($isFemale)
                                {{-- Female veiled avatar --}}
                                <svg viewBox="0 0 80 100" class="w-14 h-18">
                                    <ellipse cx="40" cy="35" rx="22" ry="24" fill="#059669"/>
                                    <rect x="18" y="55" width="44" height="40" rx="6" fill="#059669"/>
                                    <ellipse cx="40" cy="38" rx="12" ry="14" fill="#fcd7b6"/>
                                    <circle cx="35" cy="36" r="1.5" fill="#1f2937"/>
                                    <circle cx="45" cy="36" r="1.5" fill="#1f2937"/>
                                </svg>
                            @else
                                {{-- Male avatar --}}
                                <svg viewBox="0 0 80 100" class="w-14 h-18">
                                    <circle cx="40" cy="32" r="18" fill="#d1d5db"/>
                                    <ellipse cx="40" cy="32" rx="14" ry="16" fill="#fcd7b6"/>
                                    <rect x="26" y="16" width="28" height="8" rx="4" fill="#1f2937"/>
                                    <circle cx="35" cy="30" r="1.5" fill="#1f2937"/>
                                    <circle cx="45" cy="30" r="1.5" fill="#1f2937"/>
                                    <rect x="15" y="58" width="50" height="38" rx="8" fill="#d1d5db"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 space-y-3">
                            <div>
                                <p class="text-xs text-emerald-600 uppercase font-semibold tracking-wider">Nama Lengkap</p>
                                <p class="text-xl font-bold text-gray-900">{{ $registration->student_name }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-semibold">No. Registrasi</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $registration->registration_number }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-semibold">Tahun Ajaran</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $registration->academic_year ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-semibold">Jenis Kelamin</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $registration->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-semibold">Jalur Masuk</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $registration->entry_path ?? 'Reguler' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Status & Progress --}}
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Progress Pendaftaran</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            {{-- Dokumen --}}
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-1">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-lg font-bold text-gray-800">{{ $docApproved }}/{{ $docTotal }}</p>
                                <p class="text-[10px] text-gray-500 uppercase font-semibold">Dokumen</p>
                            </div>
                            {{-- Seleksi --}}
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <div class="w-8 h-8 {{ in_array($registration->status, ['lulus','cadangan','tidak_lulus','enrolled']) ? 'bg-emerald-100' : 'bg-gray-200' }} rounded-full flex items-center justify-center mx-auto mb-1">
                                    <svg class="w-4 h-4 {{ in_array($registration->status, ['lulus','cadangan','tidak_lulus','enrolled']) ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                                    </svg>
                                </div>
                                <p class="text-lg font-bold text-gray-800">{{ in_array($registration->status, ['lulus','enrolled']) ? 'Lulus' : (in_array($registration->status, ['cadangan','tidak_lulus']) ? $status['label'] : '-') }}</p>
                                <p class="text-[10px] text-gray-500 uppercase font-semibold">Seleksi</p>
                            </div>
                            {{-- Pembayaran --}}
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                    </svg>
                                </div>
                                <p class="text-lg font-bold text-gray-800">-</p>
                                <p class="text-[10px] text-gray-500 uppercase font-semibold">Pembayaran</p>
                            </div>
                            {{-- Status Akhir --}}
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <div class="w-8 h-8 {{ $registration->status === 'enrolled' ? 'bg-emerald-100' : 'bg-gray-200' }} rounded-full flex items-center justify-center mx-auto mb-1">
                                    <svg class="w-4 h-4 {{ $registration->status === 'enrolled' ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-lg font-bold text-gray-800">{{ $registration->status === 'enrolled' ? 'Aktif' : '-' }}</p>
                                <p class="text-[10px] text-gray-500 uppercase font-semibold">Santri</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-emerald-50 border-t border-emerald-100 px-6 py-3 text-center">
                    <p class="text-xs text-emerald-700">DIGITAL IDENTITY SYSTEM &bull; ASY-SYIFAA EDU &bull; Terverifikasi {{ now()->format('d M Y H:i') }}</p>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75H16.5v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Scan QR Code atau Cari Manual</h3>
                <p class="text-sm text-gray-500">Scan QR Code pada Kartu Peserta PPDB atau masukkan nomor registrasi di kolom pencarian di atas.</p>
            </div>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="max-w-3xl mx-auto px-4 py-6 text-center">
        <p class="text-xs text-gray-400">&copy; {{ date('Y') }} Pondok Pesantren Asy-Syifaa. Sistem Verifikasi Digital.</p>
    </footer>
</body>
</html>
