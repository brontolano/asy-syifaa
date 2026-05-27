<x-filament-panels::page>
    @foreach($registrations as $reg)
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-4">
            <div @class([
                'px-6 py-5',
                'bg-green-50 dark:bg-green-900/20' => in_array($reg->status, ['lulus', 'enrolled']),
                'bg-yellow-50 dark:bg-yellow-900/20' => $reg->status === 'cadangan',
                'bg-red-50 dark:bg-red-900/20' => $reg->status === 'rejected',
                'bg-gray-50 dark:bg-gray-800' => !in_array($reg->status, ['lulus', 'enrolled', 'cadangan', 'rejected']),
            ])>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ $reg->student_name }}</h3>
                        <p class="text-sm text-gray-500">{{ $reg->registration_number }} &bull; {{ $reg->academic_year }}</p>
                    </div>
                    <div class="text-right">
                        @if(in_array($reg->status, ['lulus', 'enrolled']))
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 dark:bg-green-800">
                                <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                                <span class="font-bold text-green-700 dark:text-green-300">LULUS</span>
                            </div>
                        @elseif($reg->status === 'cadangan')
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-100 dark:bg-yellow-800">
                                <x-heroicon-s-clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                <span class="font-bold text-yellow-700 dark:text-yellow-300">CADANGAN</span>
                            </div>
                        @elseif($reg->status === 'rejected')
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 dark:bg-red-800">
                                <x-heroicon-s-x-circle class="w-5 h-5 text-red-600 dark:text-red-400" />
                                <span class="font-bold text-red-700 dark:text-red-300">TIDAK LULUS</span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 dark:bg-gray-700">
                                <x-heroicon-s-clock class="w-5 h-5 text-gray-500" />
                                <span class="font-semibold text-gray-600 dark:text-gray-400">Belum Diumumkan</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($reg->notes)
                    <div class="mt-3 p-3 rounded-lg bg-white/60 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Catatan:</strong> {{ $reg->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    @if($registrations->isEmpty())
        <x-filament::section>
            <div class="text-center py-8">
                <x-heroicon-o-trophy class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                <p class="text-gray-500">Belum ada data seleksi.</p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
