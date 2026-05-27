<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Export --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-green-500"/>
                Export Data Santri
            </h3>
            <p class="text-sm text-gray-500 mb-4">Download data santri ke format Excel (.xlsx)</p>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Filter Status:</label>
                <select wire:model="exportFilter" class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2">
                    <option value="aktif">Santri Aktif</option>
                    <option value="alumni">Alumni</option>
                    <option value="waqof">Waqof</option>
                    <option value="semua">Semua Status</option>
                </select>
            </div>

            <button wire:click="exportExcel"
                class="w-full px-4 py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-500 flex items-center justify-center gap-2">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5"/>
                Download Excel
            </button>
        </div>

        {{-- Import --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <x-heroicon-o-arrow-up-tray class="w-5 h-5 text-blue-500"/>
                Import Data Santri
            </h3>
            <p class="text-sm text-gray-500 mb-4">Upload file Excel (.xlsx) untuk import data santri. Kolom minimum: NIS, Nama.</p>

            <div class="mb-4">
                <input type="file" wire:model="importFile" accept=".xlsx,.xls"
                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2">
            </div>

            <div class="flex gap-2">
                <button wire:click="previewImport"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-500" {{ !$importFile ? 'disabled' : '' }}>
                    Preview
                </button>
                @if($importPreview)
                <button wire:click="executeImport" wire:confirm="Yakin import {{ $importPreview['total'] }} data santri?"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-orange-600 text-white font-semibold hover:bg-orange-500">
                    Import {{ $importPreview['total'] }} Data
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Preview Table --}}
    @if($importPreview)
    <div class="mt-6 fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <h3 class="text-lg font-semibold mb-3">Preview (5 baris pertama dari {{ $importPreview['total'] }} total)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        @foreach($importPreview['headers'] as $h)
                        <th class="px-2 py-1 text-left">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($importPreview['rows'] as $row)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        @foreach($importPreview['headers'] as $h)
                        <td class="px-2 py-1">{{ $row[$h] ?? '-' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Import Result --}}
    @if($importResult)
    <div class="mt-6 fi-section rounded-xl bg-green-50 dark:bg-green-900/20 shadow-sm ring-1 ring-green-200 p-6">
        <h3 class="text-lg font-semibold text-green-700 mb-3">Hasil Import</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-2xl font-bold text-green-600">{{ $importResult['created'] }}</div>
                <div class="text-sm text-green-500">Data Baru</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-blue-600">{{ $importResult['updated'] }}</div>
                <div class="text-sm text-blue-500">Diperbarui</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-red-600">{{ $importResult['errors'] }}</div>
                <div class="text-sm text-red-500">Error</div>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
