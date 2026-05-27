<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4">Filter Target Penagihan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Minimal Tunggakan (bulan)</label>
                    <select wire:model.live="minTunggakan"
                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2">
                        <option value="1">≥ 1 bulan</option>
                        <option value="2">≥ 2 bulan</option>
                        <option value="3">≥ 3 bulan (Kandidat Waqof)</option>
                        <option value="4">≥ 4 bulan</option>
                        <option value="6">≥ 6 bulan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Kelas</label>
                    <select wire:model.live="filterKelas"
                        class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2">
                        <option value="">Semua Kelas</option>
                        @foreach(['1','2','3','4','5','6','7','Tamhidi'] as $k)
                        <option value="{{ $k }}">Kelas {{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Channel</label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 text-sm dark:text-gray-300">
                            <input type="checkbox" wire:model="sendWhatsapp" class="rounded"> WhatsApp
                        </label>
                        <label class="flex items-center gap-2 text-sm dark:text-gray-300">
                            <input type="checkbox" wire:model="sendNotification" class="rounded"> Notifikasi In-App
                        </label>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Pesan Kustom (opsional, kosongkan untuk template default)</label>
                <textarea wire:model="customMessage" rows="4" placeholder="Kosongkan untuk menggunakan template default penagihan..."
                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2"></textarea>
            </div>
        </div>

        {{-- Preview Target --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Target Penagihan: {{ $this->targetStudents->count() }} santri</h3>
                <button wire:click="sendBroadcast"
                    wire:confirm="Kirim broadcast penagihan ke {{ $this->targetStudents->count() }} wali santri?"
                    class="fi-btn px-6 py-2.5 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-500"
                    {{ $this->targetStudents->isEmpty() ? 'disabled' : '' }}>
                    📢 Kirim Broadcast Penagihan
                </button>
            </div>

            @if($this->targetStudents->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">NIS</th>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">Kelas</th>
                            <th class="px-3 py-2 text-center">Tunggakan</th>
                            <th class="px-3 py-2 text-left">No. HP Wali</th>
                            <th class="px-3 py-2 text-left">Wali</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->targetStudents->take(50) as $s)
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-3 py-2 font-mono text-xs">{{ $s->nis }}</td>
                            <td class="px-3 py-2">{{ $s->full_name }}</td>
                            <td class="px-3 py-2">{{ $s->kelas_detail ?? $s->kelas }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold {{ $s->tunggakan_bulan >= 3 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $s->tunggakan_bulan }} bln
                                </span>
                            </td>
                            <td class="px-3 py-2 text-xs {{ $s->phone ? '' : 'text-red-400' }}">{{ $s->phone ?? 'Tidak ada HP' }}</td>
                            <td class="px-3 py-2 text-xs">{{ $s->wali_nama_display }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($this->targetStudents->count() > 50)
                <p class="text-sm text-gray-500 mt-2 text-center">... dan {{ $this->targetStudents->count() - 50 }} santri lainnya</p>
                @endif
            </div>
            @else
            <p class="text-gray-500 text-center py-8">Tidak ada santri yang memenuhi filter.</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
