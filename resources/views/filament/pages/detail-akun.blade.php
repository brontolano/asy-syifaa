<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info Akun --}}
        @php $user = auth('erp')->user(); @endphp
        <x-filament::section>
            <x-slot name="heading">Informasi Akun</x-slot>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold">Nama Lengkap</p>
                    <p class="text-base font-medium text-gray-800 dark:text-gray-200">{{ $user->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold">Username</p>
                    <p class="text-base font-medium text-gray-800 dark:text-gray-200">{{ $user->username }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold">Nomor HP (Login)</p>
                    <p class="text-base font-medium text-gray-800 dark:text-gray-200">{{ $user->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold">Role</p>
                    <p class="text-base font-medium text-gray-800 dark:text-gray-200">{{ $user->getRoleNames()->implode(', ') }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Ubah Password --}}
        <x-filament::section>
            <x-slot name="heading">Ubah Password</x-slot>
            <x-slot name="description">Pastikan menggunakan password yang kuat dan tidak mudah ditebak.</x-slot>

            <form wire:submit="changePassword" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-full md:col-span-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password Lama</label>
                        <input type="password" wire:model="current_password"
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                               required>
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                        <input type="password" wire:model="new_password"
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                               required minlength="8">
                        @error('new_password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru</label>
                        <input type="password" wire:model="new_password_confirmation"
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                               required>
                        @error('new_password_confirmation')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <x-filament::button type="submit" color="primary">
                        Simpan Password Baru
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Ubah Nomor HP --}}
        <x-filament::section>
            <x-slot name="heading">Ubah Nomor HP</x-slot>
            <x-slot name="description">Nomor HP digunakan untuk login dan menerima notifikasi WhatsApp. Perubahan memerlukan verifikasi via WhatsApp.</x-slot>

            @if(!$otpSent)
                <form wire:submit="sendPhoneOtp" class="space-y-4">
                    <div class="max-w-md">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nomor HP Saat Ini</label>
                        <input type="text" value="{{ $user->phone ?? '-' }}" disabled
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-500 shadow-sm sm:text-sm">
                    </div>
                    <div class="max-w-md">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nomor HP Baru</label>
                        <input type="tel" wire:model="new_phone" placeholder="Contoh: 081234567890"
                               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                               required>
                        @error('new_phone')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">Link verifikasi akan dikirim ke nomor HP baru via WhatsApp.</p>
                    </div>
                    <div>
                        <x-filament::button type="submit" color="warning">
                            Kirim Link Verifikasi
                        </x-filament::button>
                    </div>
                </form>
            @else
                <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-o-device-phone-mobile class="w-5 h-5 text-amber-600" />
                        </div>
                        <div>
                            <h4 class="font-semibold text-amber-800 dark:text-amber-300">Link Verifikasi Terkirim</h4>
                            <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                                Link verifikasi telah dikirim ke <strong>{{ $new_phone }}</strong> via WhatsApp.
                            </p>
                            <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                                Klik link di WhatsApp untuk mengkonfirmasi perubahan nomor. Link berlaku <strong>15 menit</strong>.
                            </p>
                            <p class="text-xs text-amber-500 mt-2">
                                Setelah diklik, nomor HP Anda akan otomatis berubah dan Anda perlu login ulang dengan nomor baru.
                            </p>
                            <div class="mt-3">
                                <x-filament::button wire:click="cancelPhoneChange" color="gray" size="sm">
                                    Batalkan Perubahan
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
