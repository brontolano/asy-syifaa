<?php

namespace Database\Seeders;

use App\Models\BillingType;
use Illuminate\Database\Seeder;

class BillingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'DAFTAR', 'name' => 'Biaya Daftar Ulang (Perlengkapan Santri)', 'amount_default' => 7000000, 'is_recurring' => false],
            ['code' => 'SPP', 'name' => 'SPP Bulanan (Makan & Laundry)', 'amount_default' => 750000, 'is_recurring' => true],
            ['code' => 'SERAGAM', 'name' => 'Seragam Santri', 'amount_default' => 0, 'is_recurring' => false],
            ['code' => 'LEMARI_GANTUNG', 'name' => 'Lemari Pakaian Gantung', 'amount_default' => 0, 'is_recurring' => false],
            ['code' => 'LEMARI_LIPAT', 'name' => 'Lemari Pakaian Lipat', 'amount_default' => 0, 'is_recurring' => false],
            ['code' => 'LEMARI_KITAB', 'name' => 'Lemari Kitab', 'amount_default' => 0, 'is_recurring' => false],
            ['code' => 'BANGKU', 'name' => 'Bangku Belajar', 'amount_default' => 0, 'is_recurring' => false],
            ['code' => 'TIDUR', 'name' => 'Perlengkapan Tidur (Kasur, Bantal, Selimut)', 'amount_default' => 0, 'is_recurring' => false],
            ['code' => 'JARIYAH', 'name' => 'Jariyah Pembangunan', 'amount_default' => 0, 'is_recurring' => false],
        ];

        foreach ($types as $type) {
            BillingType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
