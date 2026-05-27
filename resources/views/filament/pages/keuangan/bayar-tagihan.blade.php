<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header: Search + Recent Transactions --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Search Card --}}
            <div class="lg:col-span-2 fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-primary-600 dark:text-primary-400"/>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold dark:text-white">Cari Santri</h3>
                        <p class="text-xs text-gray-500">Ketik NIS atau nama, lalu tekan Enter</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <input type="text" wire:model="student_id" wire:keydown.enter="searchStudent"
                        placeholder="Contoh: 22230001 atau Ahmad..."
                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-4 py-3 text-lg"
                        autofocus>
                    <button wire:click="searchStudent"
                        class="fi-btn px-6 py-3 rounded-lg bg-primary-600 text-white font-semibold hover:bg-primary-500 whitespace-nowrap">
                        Cari
                    </button>
                    @if($studentInfo)
                    <button wire:click="resetForm"
                        class="fi-btn px-4 py-3 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-300">
                        Reset
                    </button>
                    @endif
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="fi-section rounded-xl bg-gradient-to-br from-primary-600 to-primary-800 shadow-sm p-6 text-white">
                <h3 class="text-sm font-medium opacity-80 mb-3">Transaksi Hari Ini</h3>
                @php
                    $todayPayments = \App\Models\Payment::whereDate('payment_date', today())->get();
                    $todayTotal = $todayPayments->sum('amount');
                    $todayCash = $todayPayments->where('payment_method', 'cash')->sum('amount');
                    $todayTransfer = $todayPayments->where('payment_method', 'transfer')->sum('amount');
                @endphp
                <div class="text-2xl font-bold mb-1">Rp {{ number_format($todayTotal, 0, ',', '.') }}</div>
                <div class="text-xs opacity-70">{{ $todayPayments->count() }} transaksi</div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-white/10 rounded-lg p-2 text-center">
                        <div class="opacity-70">Cash</div>
                        <div class="font-bold">Rp {{ number_format($todayCash, 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-white/10 rounded-lg p-2 text-center">
                        <div class="opacity-70">Transfer</div>
                        <div class="font-bold">Rp {{ number_format($todayTransfer, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($studentInfo)
        {{-- Student Info Card --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex justify-between items-start">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-xl font-bold text-primary-600">
                        {{ substr($studentInfo['name'], 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $studentInfo['name'] }}</h3>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">NIS: {{ $studentInfo['nis'] }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">{{ $studentInfo['kelas'] ?? '-' }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300">{{ $studentInfo['jenjang'] ?? '-' }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Wali: {{ $studentInfo['wali'] ?? '-' }} | HP: {{ $studentInfo['phone'] ?? '-' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                        {{ $studentInfo['status'] === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($studentInfo['status']) }}
                    </span>
                    <p class="text-sm text-gray-500 mt-1">SPP: Rp {{ number_format($studentInfo['spp'], 0, ',', '.') }}/bln</p>
                    @if($studentInfo['tunggakan_bulan'] > 0)
                    <p class="text-sm text-red-600 font-bold mt-1">Tunggakan: {{ $studentInfo['tunggakan_bulan'] }} bulan</p>
                    @else
                    <p class="text-sm text-green-600 font-bold mt-1">LUNAS</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Left: Invoices Table --}}
            <div class="lg:col-span-3">
                @if(!empty($unpaidInvoices))
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
                        <x-heroicon-o-document-text class="w-5 h-5 text-red-500"/>
                        Tagihan Belum Lunas
                        <span class="text-sm font-normal text-gray-500">({{ count($unpaidInvoices) }} tagihan)</span>
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs">Periode</th>
                                    <th class="px-3 py-2 text-left text-xs">Jenis</th>
                                    <th class="px-3 py-2 text-right text-xs">Tagihan</th>
                                    <th class="px-3 py-2 text-right text-xs">Terbayar</th>
                                    <th class="px-3 py-2 text-right text-xs font-bold">Sisa</th>
                                    <th class="px-3 py-2 text-center text-xs">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unpaidInvoices as $inv)
                                <tr class="border-t border-gray-100 dark:border-gray-700 {{ $inv['status'] === 'overdue' ? 'bg-red-50 dark:bg-red-900/20' : '' }}
                                    cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                                    wire:click="$set('specific_invoice_id', '{{ $inv['id'] }}')">
                                    <td class="px-3 py-2 font-medium">{{ $inv['label'] }}</td>
                                    <td class="px-3 py-2 uppercase text-xs">{{ $inv['type'] }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($inv['total'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right text-green-600">{{ number_format($inv['paid'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-bold text-red-600">{{ number_format($inv['remaining'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold
                                            {{ $inv['status'] === 'overdue' ? 'bg-red-100 text-red-700' : ($inv['status'] === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                                            {{ $inv['status'] === 'overdue' ? 'JATUH TEMPO' : ($inv['status'] === 'partial' ? 'CICILAN' : 'BELUM BAYAR') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="border-t-2 border-gray-300 font-bold bg-gray-50 dark:bg-gray-800">
                                    <td colspan="4" class="px-3 py-3 text-right text-sm">TOTAL TUNGGAKAN:</td>
                                    <td class="px-3 py-3 text-right text-red-600 text-lg">Rp {{ number_format(collect($unpaidInvoices)->sum('remaining'), 0, ',', '.') }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Klik baris tagihan untuk bayar spesifik, atau biarkan kosong untuk bayar otomatis dari yang terlama.</p>
                </div>
                @else
                <div class="fi-section rounded-xl bg-green-50 dark:bg-green-900/20 shadow-sm ring-1 ring-green-200 dark:ring-green-800 p-8 text-center">
                    <x-heroicon-o-check-circle class="w-12 h-12 text-green-500 mx-auto mb-3"/>
                    <h3 class="text-lg font-bold text-green-700 dark:text-green-300">Semua Tagihan Lunas!</h3>
                    <p class="text-sm text-green-600">Santri ini tidak memiliki tagihan yang belum dibayar.</p>
                </div>
                @endif

                {{-- Cetak Buku SPP link --}}
                @if($studentInfo)
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('pdf.buku-spp', ['student' => $studentInfo['id'], 'year' => '1447']) }}" target="_blank"
                        class="inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400">
                        <x-heroicon-o-printer class="w-4 h-4"/> Cetak Buku SPP 1447 H
                    </a>
                    <a href="{{ route('pdf.buku-spp', ['student' => $studentInfo['id'], 'year' => '1448']) }}" target="_blank"
                        class="inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400">
                        <x-heroicon-o-printer class="w-4 h-4"/> Cetak Buku SPP 1448 H
                    </a>
                </div>
                @endif
            </div>

            {{-- Right: Payment Form --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-green-500"/>
                        Form Pembayaran
                    </h3>

                    {{-- Quick Amount Buttons --}}
                    <div class="mb-4">
                        <label class="block text-xs font-medium mb-2 text-gray-500 dark:text-gray-400">Nominal Cepat:</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach([
                                ['label' => '1 Bulan', 'value' => $studentInfo['spp']],
                                ['label' => '2 Bulan', 'value' => $studentInfo['spp'] * 2],
                                ['label' => '3 Bulan', 'value' => $studentInfo['spp'] * 3],
                                ['label' => '6 Bulan', 'value' => $studentInfo['spp'] * 6],
                                ['label' => 'Sisa', 'value' => collect($unpaidInvoices ?? [])->sum('remaining')],
                                ['label' => '1 Tahun', 'value' => $studentInfo['spp'] * 12],
                            ] as $btn)
                            <button wire:click="quickFill('{{ $btn['value'] }}')"
                                class="px-2 py-2 text-xs rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:border-primary-300 transition
                                {{ $amount == $btn['value'] ? 'bg-primary-50 border-primary-400 dark:bg-primary-900/30' : '' }}">
                                <div class="font-semibold">{{ $btn['label'] }}</div>
                                <div class="text-gray-500 text-[10px]">{{ number_format($btn['value'], 0, ',', '.') }}</div>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Jumlah Bayar (Rp) *</label>
                            <input type="number" wire:model="amount" placeholder="750000"
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2.5 text-lg font-bold">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Metode Pembayaran *</label>
                            <select wire:model="payment_channel"
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2.5">
                                <option value="">-- Pilih Metode --</option>
                                @foreach($this->paymentMethods as $method)
                                <option value="{{ $method['code'] }}" {{ !$method['is_active'] ? 'disabled' : '' }}>
                                    {{ $method['name'] }}
                                    @if($method['account_number']) ({{ $method['account_number'] }}) @endif
                                    @if(!$method['is_active']) — Segera Hadir @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Bank info for selected method --}}
                        @if($payment_channel)
                        @php $selected = collect($this->paymentMethods)->firstWhere('code', $payment_channel); @endphp
                        @if($selected && $selected['account_number'])
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm">
                            <div class="font-semibold text-blue-700 dark:text-blue-300">{{ $selected['bank_name'] ?? $selected['name'] }}</div>
                            <div class="font-mono text-lg font-bold text-blue-800 dark:text-blue-200">{{ $selected['account_number'] }}</div>
                            @if($selected['account_holder'])
                            <div class="text-blue-600 dark:text-blue-400">{{ $selected['account_holder'] }}</div>
                            @endif
                        </div>
                        @endif
                        @endif

                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">No. Referensi / Bukti</label>
                            <input type="text" wire:model="reference_number" placeholder="No. transfer / kode ref"
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Upload Bukti Transfer</label>
                            <input type="file" wire:model="proof_image" accept="image/*"
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                            @if($proof_image)
                            <div class="mt-2 text-xs text-green-600">Gambar siap diupload</div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">Catatan</label>
                            <input type="text" wire:model="notes" placeholder="Catatan tambahan..."
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2">
                        </div>
                    </div>

                    <button wire:click="processPayment" wire:confirm="Yakin memproses pembayaran Rp {{ number_format($amount ?? 0, 0, ',', '.') }}?"
                        class="mt-4 w-full px-6 py-3 rounded-lg bg-green-600 text-white font-bold text-lg hover:bg-green-500 transition flex items-center justify-center gap-2"
                        {{ empty($amount) || empty($payment_channel) ? 'disabled' : '' }}>
                        <x-heroicon-o-check-circle class="w-5 h-5"/>
                        Proses Pembayaran
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Payment Result --}}
        @if($paymentResult)
        <div class="fi-section rounded-xl bg-green-50 dark:bg-green-900/20 shadow-sm ring-2 ring-green-300 dark:ring-green-700 p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-green-600"/>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-green-700 dark:text-green-300">Pembayaran Berhasil!</h3>
                        <p class="text-sm text-green-600">{{ $paymentResult['count'] }} tagihan terbayar</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-green-700">Rp {{ number_format($paymentResult['total_paid'], 0, ',', '.') }}</div>
                    @if($paymentResult['remaining'] > 0)
                    <div class="text-sm text-yellow-600 font-semibold">Kelebihan: Rp {{ number_format($paymentResult['remaining'], 0, ',', '.') }}</div>
                    @endif
                </div>
            </div>

            @if(!empty($paymentResult['invoices_paid']))
            <div class="mt-4 border-t border-green-200 dark:border-green-700 pt-3">
                <p class="text-sm font-semibold text-green-700 mb-2">Rincian Alokasi:</p>
                @foreach($paymentResult['invoices_paid'] as $ip)
                <div class="flex justify-between items-center text-sm text-green-600 py-1">
                    <span>{{ $ip['invoice'] }} — {{ $ip['label'] }}</span>
                    <span class="font-bold">Rp {{ number_format($ip['amount'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Print Buttons --}}
            @if($lastPaymentId)
            <div class="mt-4 flex flex-wrap gap-2 border-t border-green-200 dark:border-green-700 pt-3">
                <a href="{{ route('pdf.nota', $lastPaymentId) }}" target="_blank"
                    class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-white border border-green-300 text-green-700 text-sm font-semibold hover:bg-green-50 transition">
                    <x-heroicon-o-printer class="w-4 h-4"/> Cetak Nota A4
                </a>
                <a href="{{ route('pdf.struk', $lastPaymentId) }}" target="_blank"
                    class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-white border border-green-300 text-green-700 text-sm font-semibold hover:bg-green-50 transition">
                    <x-heroicon-o-printer class="w-4 h-4"/> Cetak Struk Thermal
                </a>
            </div>
            @endif
        </div>
        @endif

        {{-- Recent Transactions --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-gray-400"/>
                Transaksi Terakhir
            </h3>
            @if(empty($this->recentPayments))
            <p class="text-gray-400 text-center py-4">Belum ada transaksi.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs">Waktu</th>
                            <th class="px-3 py-2 text-left text-xs">NIS</th>
                            <th class="px-3 py-2 text-left text-xs">Nama</th>
                            <th class="px-3 py-2 text-right text-xs">Jumlah</th>
                            <th class="px-3 py-2 text-left text-xs">Metode</th>
                            <th class="px-3 py-2 text-left text-xs">Petugas</th>
                            <th class="px-3 py-2 text-center text-xs">Cetak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->recentPayments as $t)
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2 text-xs">{{ $t['date'] }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $t['nis'] }}</td>
                            <td class="px-3 py-2">{{ $t['student'] }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-green-600">Rp {{ number_format($t['amount'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-xs">{{ $t['method'] }}</td>
                            <td class="px-3 py-2 text-xs">{{ $t['receiver'] }}</td>
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
    </div>
</x-filament-panels::page>
