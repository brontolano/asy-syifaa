<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Superadmin',
            'Admin',
            'Mudir',
            'Wakil Mudir',
            'Kepala TU',
            'Staf TU',
            'Bendahara',
            'Kepala Akademik',
            'Guru',
            'Wali Kelas',
            'Pengasuh Asrama',
            'Staf Kesehatan',
            'Staf Perpustakaan',
            'Staf Keamanan',
            'Wali Santri',
            'Santri',
            'Pendaftar',
            'Guest',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'erp']
            );
        }
    }
}
