<x-filament-panels::page>
    @if($registrations->isEmpty())
        <x-filament::section>
            <div class="text-center py-12">
                <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                    <x-heroicon-o-document-plus class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum Ada Pendaftaran</h3>
                <p class="text-sm text-gray-500">Hubungi panitia SPMB untuk mendaftar.</p>
            </div>
        </x-filament::section>
    @else
        {{-- Info Multi Anak --}}
        @if($registrations->count() > 1)
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 mb-4">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user-group class="w-5 h-5 text-blue-500" />
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Anda memiliki <strong>{{ $registrations->count() }} calon santri</strong> yang terdaftar.
                    </p>
                </div>
            </div>
        @endif

        @foreach($registrations as $reg)
            {{-- ====== KARTU PESERTA PPDB (VIRTUAL CARD) ====== --}}
            <div class="rounded-2xl overflow-hidden shadow-xl mb-6 border border-gray-200 dark:border-gray-700">
                {{-- Header Card --}}
                <div class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-emerald-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                <x-heroicon-o-academic-cap class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Kartu Peserta PPDB {{ substr($reg->academic_year, 0, 4) }}</p>
                                <p class="text-base font-bold text-white">Pondok Pesantren Asy-Syifaa</p>
                            </div>
                        </div>
                        @php
                            $verified = in_array($reg->status, ['lulus', 'enrolled']);
                            $badgeColor = $verified ? 'bg-emerald-400 text-emerald-900' : 'bg-yellow-400 text-yellow-900';
                            $badgeText = $verified ? 'TERVERIFIKASI' : $reg->status_label;
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeColor }}">
                            {{ $badgeText }}
                        </span>
                    </div>
                </div>

                {{-- Body Card --}}
                <div class="bg-white dark:bg-gray-800 px-6 py-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        {{-- Foto / Avatar --}}
                        <div class="flex-shrink-0">
                            <div class="w-36 h-44 rounded-xl bg-gray-100 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 overflow-hidden flex items-center justify-center">
                                @if($reg->foto_url)
                                    @php
                                        $user = auth('erp')->user();
                                        $canViewFemalePhoto = $reg->gender !== 'P' || ($user && $user->hasAnyRole(['Superadmin', 'Mudir']));
                                    @endphp
                                    @if($canViewFemalePhoto)
                                        <img src="{{ asset('storage/' . $reg->foto_url) }}" alt="Foto" class="w-full h-full object-cover">
                                    @else
                                        {{-- Avatar wanita bercadar --}}
                                        <svg viewBox="0 0 120 150" class="w-28 h-36">
                                            <defs>
                                                <linearGradient id="hijab{{ $reg->id }}" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" style="stop-color:#047857"/>
                                                    <stop offset="100%" style="stop-color:#065f46"/>
                                                </linearGradient>
                                            </defs>
                                            <ellipse cx="60" cy="65" rx="50" ry="60" fill="url(#hijab{{ $reg->id }})"/>
                                            <ellipse cx="60" cy="55" rx="30" ry="25" fill="#fde68a" opacity="0.15"/>
                                            <ellipse cx="48" cy="52" rx="4" ry="3" fill="#1f2937"/>
                                            <ellipse cx="72" cy="52" rx="4" ry="3" fill="#1f2937"/>
                                            <rect x="30" y="62" width="60" height="35" rx="5" fill="url(#hijab{{ $reg->id }})" opacity="0.9"/>
                                            <path d="M25 70 Q60 85 95 70" stroke="#065f46" stroke-width="2" fill="none"/>
                                            <rect x="20" y="120" width="80" height="30" rx="5" fill="url(#hijab{{ $reg->id }})"/>
                                            <text x="60" y="142" text-anchor="middle" fill="white" font-size="8" font-weight="bold">PHOTO ID</text>
                                        </svg>
                                    @endif
                                @else
                                    @if($reg->gender === 'P')
                                        {{-- Avatar wanita bercadar (default) --}}
                                        <svg viewBox="0 0 120 150" class="w-28 h-36">
                                            <defs>
                                                <linearGradient id="hijabDef{{ $reg->id }}" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" style="stop-color:#047857"/>
                                                    <stop offset="100%" style="stop-color:#065f46"/>
                                                </linearGradient>
                                            </defs>
                                            <ellipse cx="60" cy="65" rx="50" ry="60" fill="url(#hijabDef{{ $reg->id }})"/>
                                            <ellipse cx="60" cy="55" rx="30" ry="25" fill="#fde68a" opacity="0.15"/>
                                            <ellipse cx="48" cy="52" rx="4" ry="3" fill="#1f2937"/>
                                            <ellipse cx="72" cy="52" rx="4" ry="3" fill="#1f2937"/>
                                            <rect x="30" y="62" width="60" height="35" rx="5" fill="url(#hijabDef{{ $reg->id }})" opacity="0.9"/>
                                            <path d="M25 70 Q60 85 95 70" stroke="#065f46" stroke-width="2" fill="none"/>
                                            <rect x="20" y="120" width="80" height="30" rx="5" fill="url(#hijabDef{{ $reg->id }})"/>
                                            <text x="60" y="142" text-anchor="middle" fill="white" font-size="8" font-weight="bold">PHOTO ID</text>
                                        </svg>
                                    @else
                                        {{-- Avatar laki-laki --}}
                                        <svg viewBox="0 0 120 150" class="w-28 h-36">
                                            <circle cx="60" cy="50" r="30" fill="#d1d5db"/>
                                            <circle cx="60" cy="45" r="22" fill="#fbbf24" opacity="0.15"/>
                                            <circle cx="60" cy="45" r="20" fill="#9ca3af"/>
                                            <ellipse cx="50" cy="43" rx="3" ry="2.5" fill="#1f2937"/>
                                            <ellipse cx="70" cy="43" rx="3" ry="2.5" fill="#1f2937"/>
                                            <path d="M52 55 Q60 60 68 55" stroke="#6b7280" stroke-width="2" fill="none"/>
                                            <path d="M35 30 Q45 15 60 18 Q75 15 85 30" stroke="#6b7280" stroke-width="3" fill="#4b5563"/>
                                            <rect x="25" y="85" width="70" height="50" rx="10" fill="#e5e7eb"/>
                                            <rect x="40" y="95" width="40" height="10" rx="3" fill="#d1d5db"/>
                                            <rect x="20" y="120" width="80" height="30" rx="5" fill="#6b7280"/>
                                            <text x="60" y="142" text-anchor="middle" fill="white" font-size="8" font-weight="bold">PHOTO ID</text>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Data Peserta --}}
                        <div class="flex-1">
                            <div class="mb-4">
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 uppercase font-semibold tracking-wide">Nama Lengkap</p>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $reg->student_name }}</h2>
                            </div>

                            <div class="space-y-2">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-semibold">No. Registrasi</p>
                                    <p class="text-lg font-mono font-bold text-gray-800 dark:text-gray-200">{{ $reg->registration_number }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-semibold">Jalur Masuk</p>
                                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">Reguler</p>
                                </div>
                            </div>

                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">VALIDITY: 31 DEC {{ substr($reg->academic_year, 0, 4) }}</p>
                        </div>

                        {{-- QR Code area (kanan) --}}
                        @php
                            $verifyUrl = url('/verifikasi/' . $reg->registration_number);
                            $qrOptions = new \chillerlan\QRCode\QROptions([
                                'outputType' => \chillerlan\QRCode\Output\QROutputInterface::MARKUP_SVG,
                                'outputBase64' => false,
                                'svgUseCssProperties' => false,
                                'drawLightModules' => false,
                                'addQuietzone' => true,
                                'scale' => 5,
                                'cssClass' => '',
                            ]);
                            $qrSvg = (new \chillerlan\QRCode\QRCode($qrOptions))->render($verifyUrl);
                        @endphp
                        <div class="flex flex-col items-center gap-1 flex-shrink-0">
                            <div class="w-28 h-28 bg-white rounded-xl border-2 border-gray-200 dark:border-gray-600 flex items-center justify-center p-1.5">
                                {!! $qrSvg !!}
                            </div>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Scan untuk verifikasi</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gradient-to-r from-emerald-700 to-emerald-600 px-6 py-2.5">
                    <p class="text-xs text-emerald-100 font-semibold tracking-wider uppercase">
                        Digital Identity System &bull; Asy-Syifaa Edu
                    </p>
                </div>
            </div>

            {{-- ====== STATUS CARDS ====== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                {{-- Status Pendaftaran --}}
                <div class="rounded-xl bg-white dark:bg-gray-800 p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-emerald-600" />
                        </div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Status Pendaftaran</p>
                    </div>
                    <span @class([
                        'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold',
                        'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' => in_array($reg->status, ['lulus', 'enrolled']),
                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' => in_array($reg->status, ['pending', 'cadangan']),
                        'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' => in_array($reg->status, ['document_review', 'selection']),
                        'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' => $reg->status === 'rejected',
                    ])>
                        {{ $reg->status_label }}
                    </span>
                </div>

                {{-- Dokumen Progress --}}
                <div class="rounded-xl bg-white dark:bg-gray-800 p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <x-heroicon-o-document-text class="w-5 h-5 text-blue-600" />
                        </div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Kelengkapan Dokumen</p>
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-bold text-emerald-600">{{ $reg->approved_docs }}</span>
                        <span class="text-gray-400 text-lg pb-0.5">/{{ $totalDocs }}</span>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $reg->doc_pct }}%"></div>
                    </div>
                </div>

                {{-- Ujian --}}
                <div class="rounded-xl bg-white dark:bg-gray-800 p-5 border border-gray-200 dark:border-gray-700 shadow-sm opacity-50">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <x-heroicon-o-pencil-square class="w-5 h-5 text-amber-600" />
                        </div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tes/Ujian Masuk</p>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Belum Tersedia</p>
                    <p class="text-xs text-gray-400 mt-1">Segera hadir di Wave 2</p>
                </div>
            </div>
        @endforeach

        {{-- ====== TIMELINE PPDB ====== --}}
        <x-filament::section>
            <x-slot name="heading">Jadwal / Timeline SPMB</x-slot>
            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-emerald-200 dark:bg-emerald-800"></div>
                <div class="space-y-6">
                    @foreach($timeline as $i => $step)
                        <div class="relative flex items-start gap-4 pl-10">
                            <div class="absolute left-2 w-5 h-5 rounded-full border-2 border-emerald-500 bg-white dark:bg-gray-800 flex items-center justify-center" style="top:2px;">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $step['kegiatan'] }}</p>
                                <p class="text-xs text-gray-500">{{ $step['tanggal'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-filament::section>

        {{-- ====== KEBIJAKAN ====== --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Pernyataan & Kebijakan Pesantren</x-slot>
            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300">
                @foreach(config('spmb.pernyataan_kebijakan', []) as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ol>
        </x-filament::section>
    @endif
</x-filament-panels::page>
