<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => 'transfer_bsi', 'type' => 'bank', 'name' => 'Transfer Bank BSI',
                'bank_name' => 'Bank Syariah Indonesia (BSI)', 'account_number' => '7100xxxxxx',
                'account_holder' => 'Yayasan Asy-Syifaa', 'icon' => 'heroicon-o-building-library',
                'is_active' => true, 'sort_order' => 1,
            ],
            [
                'code' => 'transfer_bca', 'type' => 'bank', 'name' => 'Transfer Bank BCA',
                'bank_name' => 'Bank Central Asia (BCA)', 'account_number' => '1234xxxxxx',
                'account_holder' => 'Yayasan Asy-Syifaa', 'icon' => 'heroicon-o-building-library',
                'is_active' => true, 'sort_order' => 2,
            ],
            [
                'code' => 'qris', 'type' => 'qris', 'name' => 'QRIS',
                'bank_name' => 'QRIS (semua e-wallet & m-banking)',
                'account_holder' => 'Yayasan Asy-Syifaa', 'icon' => 'heroicon-o-qr-code',
                'instructions' => 'Scan kode QR menggunakan aplikasi m-banking atau e-wallet Anda.',
                'is_active' => true, 'sort_order' => 3,
            ],
        ];

        foreach ($methods as $m) {
            PaymentMethod::updateOrCreate(['code' => $m['code']], $m);
        }
    }
}
