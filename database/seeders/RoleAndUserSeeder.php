<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan User Root ada (Super Admin yang bypass semua check)
        $root = User::updateOrCreate(
            ['username' => 'root'],
            [
                'name' => 'Super Admin',
                'password' => 'root123', // Silakan diubah setelah login pertama
                'role' => 'admin', // Enum lama untuk kompatibilitas
                'role_id' => null, // Root tidak butuh role_id karena bypass check
            ]
        );

        // 2. Buat Role Default "Administrator" (Semua Izin)
        $allPermissions = [
            'access_items',
            'access_categories',
            'access_locations',
            'access_services',
            'access_reports',
            'access_settings',
            'access_users',
        ];

        $adminRole = Role::updateOrCreate(
            ['name' => 'Administrator'],
            ['permissions' => $allPermissions]
        );

        // 3. Buat Role Default "Staf Operasional" (Hanya Kelola Barang & Servis)
        $staffRole = Role::updateOrCreate(
            ['name' => 'Staf Operasional'],
            [
                'permissions' => [
                    'access_items',
                    'access_services',
                ]
            ]
        );

        // 4. Contoh User dengan Role Administrator
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Sistem',
                'password' => 'admin123',
                'role' => 'admin',
                'role_id' => $adminRole->id,
            ]
        );

        // 5. Contoh User dengan Role Staf
        User::updateOrCreate(
            ['username' => 'staff'],
            [
                'name' => 'Staf Gudang',
                'password' => 'staff123',
                'role' => 'editor',
                'role_id' => $staffRole->id,
            ]
        );

        $this->command->info('Seeder Role dan User berhasil dijalankan.');
        $this->command->info('Akun Root: root / root123');
        $this->command->info('Akun Admin: admin / admin123');
        $this->command->info('Akun Staff: staff / staff123');
    }
}
