<?php

namespace Database\Seeders;

use App\Models\ErpAccount;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = ErpAccount::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'full_name' => 'Super Administrator',
                'email' => 'admin@asy-syifaa.com',
                'password' => 'password123',
                'is_active' => true,
            ]
        );

        $admin->assignRole('Superadmin');
    }
}
