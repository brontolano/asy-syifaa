<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Summary --}}
        @php
            $totalModules = collect($this->modules)->pluck('modules')->flatten(1)->count();
            $activeModules = collect($this->modules)->pluck('modules')->flatten(1)->where('active', true)->count();
        @endphp
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <span class="font-semibold text-gray-900 dark:text-white">{{ $activeModules }}</span> modul aktif dari
            <span class="font-semibold text-gray-900 dark:text-white">{{ $totalModules }}</span> total
        </p>

        {{-- Module Groups --}}
        @foreach($this->modules as $group)
        <div>
            <div class="flex items-center gap-3 mb-3">
                <h2 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $group['group'] }}</h2>
                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px;">
                @foreach($group['modules'] as $module)
                @php
                    $isActive = $module['active'];
                    $color = $module['color'];
                    $iconBg = $isActive ? match($color) {
                        'emerald' => 'background: #d1fae5; color: #059669;',
                        'sky' => 'background: #e0f2fe; color: #0284c7;',
                        'violet' => 'background: #ede9fe; color: #7c3aed;',
                        'amber' => 'background: #fef3c7; color: #d97706;',
                        'rose' => 'background: #ffe4e6; color: #e11d48;',
                        default => 'background: #f3f4f6; color: #6b7280;',
                    } : 'background: #f3f4f6; color: #9ca3af;';
                    $iconBgDark = $isActive ? match($color) {
                        'emerald' => 'background: rgba(16,185,129,0.15); color: #34d399;',
                        'sky' => 'background: rgba(14,165,233,0.15); color: #38bdf8;',
                        'violet' => 'background: rgba(139,92,246,0.15); color: #a78bfa;',
                        'amber' => 'background: rgba(245,158,11,0.15); color: #fbbf24;',
                        'rose' => 'background: rgba(244,63,94,0.15); color: #fb7185;',
                        default => 'background: rgba(107,114,128,0.15); color: #9ca3af;',
                    } : 'background: rgba(107,114,128,0.1); color: #6b7280;';
                @endphp

                @if($isActive)
                <a href="{{ $module['url'] }}" wire:navigate
                   class="group flex flex-col items-center text-center p-3 rounded-xl bg-white dark:bg-gray-900 hover:shadow-md transition-all duration-150 hover:-translate-y-0.5"
                   style="border: 1px solid rgba(0,0,0,0.08);"
                   onmouseenter="this.style.borderColor='rgba(0,0,0,0.2)'" onmouseleave="this.style.borderColor='rgba(0,0,0,0.08)'">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-2 transition-transform duration-150 group-hover:scale-110 dark:hidden" style="{{ $iconBg }}">
                        <x-filament::icon :icon="$module['icon']" class="w-5 h-5" style="color: inherit;" />
                    </div>
                    <div class="w-10 h-10 rounded-lg items-center justify-center mb-2 transition-transform duration-150 group-hover:scale-110 hidden dark:flex" style="{{ $iconBgDark }}">
                        <x-filament::icon :icon="$module['icon']" class="w-5 h-5" style="color: inherit;" />
                    </div>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 leading-tight">{{ $module['name'] }}</span>
                </a>
                @else
                <div class="flex flex-col items-center text-center p-3 rounded-xl opacity-40 cursor-not-allowed select-none"
                     style="background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-2" style="{{ $iconBg }}">
                        <x-filament::icon :icon="$module['icon']" class="w-5 h-5" style="color: inherit;" />
                    </div>
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-600 leading-tight">{{ $module['name'] }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</x-filament-panels::page>
