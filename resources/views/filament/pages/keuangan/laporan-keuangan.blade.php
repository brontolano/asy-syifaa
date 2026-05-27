<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Tab Navigation --}}
        <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-2">
            @foreach([
                'dashboard' => 'Dashboard KPI',
                'ringkasan' => 'Ringkasan',
                'tunggakan' => 'Daftar Tunggakan',
                'matrix' => 'Matrix Syahriyyah',
                'rekap_bulan' => 'Rekap Per Bulan',
                'transaksi' => 'Buku Setoran',
            ] as $key => $label)
                <button wire:click="$set('reportType', '{{ $key }}')"
                    class="px-4 py-2 rounded-t-lg text-sm font-semibold transition
                    {{ $reportType === $key ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- DASHBOARD KPI --}}
        @if($reportType === 'dashboard')
        {{-- Period Filter --}}
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-medium text-gray-500">Periode:</label>
                <select wire:model.live="filterPeriod" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                    <option value="hari_ini">Hari Ini</option>
                    <option value="bulan_ini">Bulan Ini</option>
                    <option value="tahun_ini">Tahun Ini</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            @if($filterPeriod === 'custom')
            <div>
                <label class="text-xs font-medium text-gray-500">Dari:</label>
                <input type="date" wire:model.live="dateFrom" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Sampai:</label>
                <input type="date" wire:model.live="dateTo" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
            </div>
            @endif
            <a href="{{ route('pdf.laporan-periode', ['from' => $dateFrom, 'to' => $dateTo]) }}" target="_blank"
                class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-500">
                <x-heroicon-o-document-arrow-down class="w-4 h-4"/> Export PDF
            </a>
        </div>

        @php $kpi = $this->dashboardKpi; @endphp

        {{-- 4 KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 p-5 text-white">
                <p class="text-xs opacity-80">Total Transaksi</p>
                <p class="text-3xl font-bold">{{ $kpi['totalTransaksi'] }}</p>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-green-500 to-green-700 p-5 text-white">
                <p class="text-xs opacity-80">Total Pemasukan</p>
                <p class="text-2xl font-bold">Rp {{ number_format($kpi['totalPemasukan'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 p-5 text-white">
                <p class="text-xs opacity-80">Cash</p>
                <p class="text-2xl font-bold">Rp {{ number_format($kpi['totalCash'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 p-5 text-white">
                <p class="text-xs opacity-80">Transfer</p>
                <p class="text-2xl font-bold">Rp {{ number_format($kpi['totalTransfer'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Breakdown Metode --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                <h3 class="text-lg font-semibold mb-4">Breakdown Metode Pembayaran</h3>
                <div class="space-y-3">
                    @foreach($kpi['byChannel'] as $channel => $data)
                    @php
                        $pct = $kpi['totalPemasukan'] > 0 ? round(($data['total'] / $kpi['totalPemasukan']) * 100) : 0;
                        $colors = ['cash' => 'bg-amber-500', 'transfer_bsi' => 'bg-blue-500', 'transfer_bca' => 'bg-indigo-500'];
                        $color = $colors[$channel] ?? 'bg-gray-400';
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $channel ?: 'Lainnya')) }}</span>
                            <span>{{ $data['count'] }}x — Rp {{ number_format($data['total'], 0, ',', '.') }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="{{ $color }} h-3 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Top Jenis Pembayaran --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                <h3 class="text-lg font-semibold mb-4">Top Jenis Pembayaran</h3>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">Jenis</th>
                            <th class="px-3 py-2 text-right">Jumlah</th>
                            <th class="px-3 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kpi['byType'] as $type => $data)
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2 font-medium uppercase">{{ $type }}</td>
                            <td class="px-3 py-2 text-right">{{ $data['count'] }}</td>
                            <td class="px-3 py-2 text-right font-semibold">Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top 10 Tunggakan --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4">Top 10 Tunggakan Terbanyak</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-red-50 dark:bg-red-900/20">
                        <tr>
                            <th class="px-3 py-2 text-left">No</th>
                            <th class="px-3 py-2 text-left">NIS</th>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Kelas</th>
                            <th class="px-3 py-2 text-center">Tunggakan</th>
                            <th class="px-3 py-2 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kpi['topTunggakan'] as $i => $s)
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2">{{ $i + 1 }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $s['nis'] }}</td>
                            <td class="px-3 py-2 font-semibold">{{ $s['name'] }}</td>
                            <td class="px-3 py-2">{{ $s['kelas'] }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">{{ $s['bulan'] }} bln</span>
                            </td>
                            <td class="px-3 py-2 text-right text-red-600 font-bold">Rp {{ number_format($s['nominal'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Trend 12 Bulan --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4">Tren Pemasukan 12 Bulan Terakhir</h3>
            @php $maxTrend = collect($kpi['trend'])->max('total') ?: 1; @endphp
            <div class="flex items-end gap-1 h-48">
                @foreach($kpi['trend'] as $t)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full flex flex-col items-center" style="height: 160px; justify-content: flex-end;">
                        @php
                            $cashH = ($t['cash'] / $maxTrend) * 140;
                            $transferH = ($t['transfer'] / $maxTrend) * 140;
                        @endphp
                        <div class="w-full rounded-t" style="height: {{ $transferH }}px; background: #8b5cf6;" title="Transfer: Rp {{ number_format($t['transfer'], 0, ',', '.') }}"></div>
                        <div class="w-full" style="height: {{ $cashH }}px; background: #f59e0b;" title="Cash: Rp {{ number_format($t['cash'], 0, ',', '.') }}"></div>
                    </div>
                    <div class="text-[9px] text-gray-400 text-center leading-tight">{{ $t['label'] }}</div>
                </div>
                @endforeach
            </div>
            <div class="flex gap-4 mt-3 text-xs">
                <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-amber-500"></div> Cash</div>
                <div class="flex items-center gap-1"><div class="w-3 h-3 rounded bg-purple-500"></div> Transfer</div>
            </div>
        </div>
        @endif

        {{-- RINGKASAN --}}
        @if($reportType === 'ringkasan')
        @php $r = $this->ringkasan; @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4">
                <p class="text-sm text-blue-600 dark:text-blue-400">Total Tagihan</p>
                <p class="text-xl font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($r['totalTagihan'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4">
                <p class="text-sm text-green-600 dark:text-green-400">Total Terbayar</p>
                <p class="text-xl font-bold text-green-700 dark:text-green-300">Rp {{ number_format($r['totalBayar'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4">
                <p class="text-sm text-red-600 dark:text-red-400">Total Tunggakan</p>
                <p class="text-xl font-bold text-red-700 dark:text-red-300">Rp {{ number_format($r['totalTunggakan'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Santri Aktif</p>
                <p class="text-xl font-bold text-gray-700 dark:text-gray-300">{{ $r['totalSantriAktif'] }}</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 text-center">
                <p class="text-3xl font-bold text-green-600">{{ $r['santriLunas'] }}</p>
                <p class="text-sm text-green-500">Santri Lunas</p>
            </div>
            <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 p-4 text-center">
                <p class="text-3xl font-bold text-yellow-600">{{ $r['santriTunggakan'] }}</p>
                <p class="text-sm text-yellow-500">Santri Menunggak</p>
            </div>
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4 text-center">
                <p class="text-3xl font-bold text-red-600">{{ $r['santriWaqof'] }}</p>
                <p class="text-sm text-red-500">Santri Waqof</p>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Tunggakan Per Kelas</h3>
                <a href="{{ route('pdf.tagihan-massal') }}" target="_blank"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-500">
                    <x-heroicon-o-document-arrow-down class="w-3.5 h-3.5"/> Cetak PDF
                </a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-3 py-2 text-left">Kelas</th>
                        <th class="px-3 py-2 text-right">Jml Santri</th>
                        <th class="px-3 py-2 text-right">Total Bulan</th>
                        <th class="px-3 py-2 text-right">Total Tunggakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->tunggakanPerKelas as $row)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="px-3 py-2 font-semibold">Kelas {{ $row['kelas'] }}</td>
                        <td class="px-3 py-2 text-right">{{ $row['jumlah_santri'] }}</td>
                        <td class="px-3 py-2 text-right">{{ $row['total_bulan'] }} bln</td>
                        <td class="px-3 py-2 text-right text-red-600 font-semibold">Rp {{ number_format($row['total_amount'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- DAFTAR TUNGGAKAN --}}
        @if($reportType === 'tunggakan')
        <div class="flex gap-3 mb-4 items-end">
            <div>
                <label class="text-xs font-medium text-gray-500">Kelas:</label>
                <select wire:model.live="filterKelas" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach(['1','2','3','4','5','6','7','Tamhidi'] as $k)
                    <option value="{{ $k }}">Kelas {{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Jenjang:</label>
                <select wire:model.live="filterJenjang" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="Wustha">Wustha</option>
                    <option value="Ulya">Ulya</option>
                    <option value="Tamhidi">Tamhidi</option>
                    <option value="Takhassus">Takhassus</option>
                </select>
            </div>
            <a href="{{ route('pdf.tagihan-massal', ['kelas' => $filterKelas, 'jenjang' => $filterJenjang]) }}" target="_blank"
                class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-500">
                <x-heroicon-o-document-arrow-down class="w-4 h-4"/> Export PDF
            </a>
        </div>
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Daftar Santri Menunggak ({{ count($this->daftarTunggakan) }})</h3>
                <span class="text-sm text-red-500 font-semibold">
                    Total: Rp {{ number_format(collect($this->daftarTunggakan)->sum('tunggakan_amount'), 0, ',', '.') }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">NIS</th>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Kelas</th>
                            <th class="px-3 py-2 text-left">Jenjang</th>
                            <th class="px-3 py-2 text-center">Tunggakan</th>
                            <th class="px-3 py-2 text-right">Nominal</th>
                            <th class="px-3 py-2 text-left">No. HP</th>
                            <th class="px-3 py-2 text-left">Wali</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->daftarTunggakan as $s)
                        <tr class="border-t border-gray-100 dark:border-gray-700 {{ $s['tunggakan_bulan'] >= 3 ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                            <td class="px-3 py-2 font-mono text-xs">{{ $s['nis'] }}</td>
                            <td class="px-3 py-2 font-semibold">{{ $s['name'] }}</td>
                            <td class="px-3 py-2">{{ $s['kelas'] }}</td>
                            <td class="px-3 py-2">{{ $s['jenjang'] }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $s['tunggakan_bulan'] >= 3 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $s['tunggakan_bulan'] }} bln</span>
                            </td>
                            <td class="px-3 py-2 text-right text-red-600 font-semibold">Rp {{ number_format($s['tunggakan_amount'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-xs">{{ $s['phone'] ?? '-' }}</td>
                            <td class="px-3 py-2 text-xs">{{ $s['wali'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- MATRIX SYAHRIYYAH --}}
        @if($reportType === 'matrix')
        <div class="flex gap-3 mb-4 items-end">
            <div>
                <label class="text-xs font-medium text-gray-500">Tahun Hijriah:</label>
                <select wire:model.live="matrixYear" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                    <option value="1446">1446 H</option>
                    <option value="1447">1447 H</option>
                    <option value="1448">1448 H</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Kelas:</label>
                <select wire:model.live="filterKelas" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach(['1','2','3','4','5','6','7','Tamhidi'] as $k)
                    <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('pdf.matrix-syahriyyah', ['year' => $matrixYear, 'kelas' => $filterKelas]) }}" target="_blank"
                class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-500">
                <x-heroicon-o-document-arrow-down class="w-4 h-4"/> Export PDF
            </a>
        </div>
        @php $md = $this->matrixData; @endphp
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <h3 class="text-lg font-semibold mb-3">Matrix Syahriyyah {{ $matrixYear }} H</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="px-2 py-1.5 text-left border border-gray-200 dark:border-gray-700 sticky left-0 bg-gray-100 dark:bg-gray-800 z-10">No</th>
                            <th class="px-2 py-1.5 text-left border border-gray-200 dark:border-gray-700 sticky left-8 bg-gray-100 dark:bg-gray-800 z-10">NIS</th>
                            <th class="px-2 py-1.5 text-left border border-gray-200 dark:border-gray-700 sticky left-24 bg-gray-100 dark:bg-gray-800 z-10 min-w-[140px]">Nama</th>
                            <th class="px-2 py-1.5 text-center border border-gray-200 dark:border-gray-700">Kls</th>
                            @foreach($md['periods'] as $p)
                            <th class="px-1 py-1.5 text-center border border-gray-200 dark:border-gray-700 min-w-[45px]">{{ substr($p->hijri_month_name, 0, 3) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($md['matrix'] as $i => $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-2 py-1 border border-gray-200 dark:border-gray-700 sticky left-0 bg-white dark:bg-gray-900">{{ $i + 1 }}</td>
                            <td class="px-2 py-1 border border-gray-200 dark:border-gray-700 sticky left-8 bg-white dark:bg-gray-900 font-mono">{{ $row['nis'] }}</td>
                            <td class="px-2 py-1 border border-gray-200 dark:border-gray-700 sticky left-24 bg-white dark:bg-gray-900 truncate max-w-[160px]">{{ $row['name'] }}</td>
                            <td class="px-2 py-1 border border-gray-200 dark:border-gray-700 text-center">{{ $row['kelas'] }}</td>
                            @foreach($md['periods'] as $p)
                            @php $st = $row['months'][$p->id] ?? 'none'; @endphp
                            <td class="px-1 py-1 border border-gray-200 dark:border-gray-700 text-center font-bold
                                {{ match($st) {
                                    'paid' => 'bg-green-100 dark:bg-green-900/30 text-green-700',
                                    'partial' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700',
                                    'issued', 'overdue' => 'bg-red-100 dark:bg-red-900/30 text-red-700',
                                    default => 'text-gray-300',
                                } }}">
                                {{ match($st) { 'paid' => '✓', 'partial' => '~', 'issued' => '✗', 'overdue' => '!', default => '—' } }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex gap-4 mt-3 text-xs text-gray-500">
                <span><span class="inline-block w-4 h-3 bg-green-100 rounded mr-1"></span>✓ Lunas</span>
                <span><span class="inline-block w-4 h-3 bg-yellow-100 rounded mr-1"></span>~ Cicilan</span>
                <span><span class="inline-block w-4 h-3 bg-red-100 rounded mr-1"></span>✗ Belum Bayar</span>
                <span>— Belum Ada Tagihan</span>
            </div>
        </div>
        @endif

        {{-- REKAP PER BULAN --}}
        @if($reportType === 'rekap_bulan')
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4">Rekap SPP Per Bulan Hijriah</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">Bulan</th>
                            <th class="px-3 py-2 text-right">Invoice</th>
                            <th class="px-3 py-2 text-right">Lunas</th>
                            <th class="px-3 py-2 text-right">Tagihan</th>
                            <th class="px-3 py-2 text-right">Terbayar</th>
                            <th class="px-3 py-2 text-right">Tunggakan</th>
                            <th class="px-3 py-2 text-center">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->rekapPerBulan as $row)
                        @if($row['jumlah_invoice'] > 0)
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2 font-semibold">{{ $row['label'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['jumlah_invoice'] }}</td>
                            <td class="px-3 py-2 text-right text-green-600">{{ $row['lunas'] }}</td>
                            <td class="px-3 py-2 text-right">Rp {{ number_format($row['total_tagihan'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right text-green-600">Rp {{ number_format($row['total_bayar'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right text-red-600 font-semibold">Rp {{ number_format($row['tunggakan'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $row['persen'] }}%"></div>
                                </div>
                                <span class="text-xs text-center block">{{ $row['persen'] }}%</span>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- BUKU SETORAN --}}
        @if($reportType === 'transaksi')
        <div class="flex gap-3 mb-4 items-end">
            <div>
                <label class="text-xs font-medium text-gray-500">Dari:</label>
                <input type="date" wire:model.live="dateFrom" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Sampai:</label>
                <input type="date" wire:model.live="dateTo" class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
            </div>
            <a href="{{ route('pdf.buku-setoran', ['from' => $dateFrom, 'to' => $dateTo]) }}" target="_blank"
                class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-500">
                <x-heroicon-o-document-arrow-down class="w-4 h-4"/> Cetak Buku Setoran
            </a>
        </div>

        @php
            $txList = $this->transaksiTerakhir;
            $txCash = collect($txList)->where('method', 'cash')->sum('amount');
            $txTransfer = collect($txList)->where('method', 'transfer')->sum('amount');
        @endphp
        <div class="grid grid-cols-4 gap-3 mb-4">
            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-3 text-center">
                <div class="text-xs text-blue-500">Transaksi</div>
                <div class="text-lg font-bold text-blue-700">{{ count($txList) }}</div>
            </div>
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-3 text-center">
                <div class="text-xs text-green-500">Total</div>
                <div class="text-lg font-bold text-green-700">Rp {{ number_format(collect($txList)->sum('amount'), 0, ',', '.') }}</div>
            </div>
            <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 p-3 text-center">
                <div class="text-xs text-amber-500">Cash</div>
                <div class="text-lg font-bold text-amber-700">Rp {{ number_format($txCash, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-lg bg-purple-50 dark:bg-purple-900/20 p-3 text-center">
                <div class="text-xs text-purple-500">Transfer</div>
                <div class="text-lg font-bold text-purple-700">Rp {{ number_format($txTransfer, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            @if(empty($txList))
            <p class="text-gray-500 text-center py-8">Belum ada transaksi pada periode ini.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs">Tanggal</th>
                            <th class="px-3 py-2 text-left text-xs">NIS</th>
                            <th class="px-3 py-2 text-left text-xs">Nama</th>
                            <th class="px-3 py-2 text-left text-xs">Invoice</th>
                            <th class="px-3 py-2 text-right text-xs">Jumlah</th>
                            <th class="px-3 py-2 text-left text-xs">Metode</th>
                            <th class="px-3 py-2 text-left text-xs">Channel</th>
                            <th class="px-3 py-2 text-left text-xs">Ref</th>
                            <th class="px-3 py-2 text-center text-xs">Cetak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($txList as $t)
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2 text-xs">{{ $t['date'] }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $t['nis'] }}</td>
                            <td class="px-3 py-2">{{ $t['student'] }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $t['invoice'] }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-green-600">Rp {{ number_format($t['amount'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-xs">{{ ucfirst($t['method']) }}</td>
                            <td class="px-3 py-2 text-xs">{{ $t['channel'] ?? '-' }}</td>
                            <td class="px-3 py-2 text-xs">{{ $t['reference'] ?? '-' }}</td>
                            <td class="px-3 py-2 text-center">
                                <a href="{{ route('pdf.nota', $t['id']) }}" target="_blank" class="text-primary-500 hover:text-primary-700">
                                    <x-heroicon-o-printer class="w-4 h-4 inline"/>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif
    </div>
</x-filament-panels::page>
