<x-filament-panels::page>
    <div class="space-y-6">
        {{-- DB Stats --}}
        @php $stats = $this->dbStats; @endphp
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                <div class="text-sm text-blue-500">Santri</div>
                <div class="text-xl font-bold text-blue-700">{{ number_format($stats['students']) }}</div>
            </div>
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 text-center">
                <div class="text-sm text-green-500">Invoice</div>
                <div class="text-xl font-bold text-green-700">{{ number_format($stats['invoices']) }}</div>
            </div>
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 p-4 text-center">
                <div class="text-sm text-amber-500">Pembayaran</div>
                <div class="text-xl font-bold text-amber-700">{{ number_format($stats['payments']) }}</div>
            </div>
            <div class="rounded-xl bg-purple-50 dark:bg-purple-900/20 p-4 text-center">
                <div class="text-sm text-purple-500">Akun</div>
                <div class="text-xl font-bold text-purple-700">{{ number_format($stats['erp_accounts']) }}</div>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-4 text-center">
                <div class="text-sm text-gray-500">Ukuran DB</div>
                <div class="text-xl font-bold text-gray-700 dark:text-gray-300">{{ $stats['db_size'] }}</div>
            </div>
        </div>

        {{-- Create Backup --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">Backup Database</h3>
                    <p class="text-sm text-gray-500">Buat backup PostgreSQL menggunakan pg_dump</p>
                </div>
                <button wire:click="createBackup" wire:confirm="Buat backup database sekarang?"
                    class="px-6 py-3 rounded-lg bg-primary-600 text-white font-semibold hover:bg-primary-500 flex items-center gap-2">
                    <x-heroicon-o-server-stack class="w-5 h-5"/>
                    Buat Backup Sekarang
                </button>
            </div>
        </div>

        {{-- Backup List --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4">Daftar Backup</h3>
            @if(empty($this->backups))
            <p class="text-gray-400 text-center py-6">Belum ada backup.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">Nama File</th>
                            <th class="px-3 py-2 text-right">Ukuran</th>
                            <th class="px-3 py-2 text-left">Tanggal</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->backups as $backup)
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2 font-mono text-xs">{{ $backup['name'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $backup['size'] }} KB</td>
                            <td class="px-3 py-2">{{ $backup['date'] }}</td>
                            <td class="px-3 py-2 text-center flex gap-2 justify-center">
                                <button wire:click="downloadBackup('{{ $backup['name'] }}')"
                                    class="text-primary-500 hover:text-primary-700 text-xs">Download</button>
                                <button wire:click="deleteBackup('{{ $backup['name'] }}')"
                                    wire:confirm="Hapus backup {{ $backup['name'] }}?"
                                    class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
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
