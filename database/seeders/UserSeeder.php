<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed user dengan role yang sudah dibuat oleh RolesAndPermissionsSeeder.
     * Password super admin & admin: lihat konstanta di bawah.
     * Password user lain (dev) = password
     */
    public function run(): void
    {
        $passwords = [
            'superadmin.ngekos@gmail.com' => 'Superadmin.ngekos123',
            'admin.ngekos@gmail.com' => 'Admin.ngekos123',
        ];
        $users = [
            ['nama' => 'Super Admin Ngekos', 'email' => 'superadmin.ngekos@gmail.com', 'no_hp' => '081234567001', 'role' => 'super_admin'],
            ['nama' => 'Pemilik Budi', 'email' => 'pemilik1@ngekos.test', 'no_hp' => '081234567002', 'role' => 'pemilik'],
            ['nama' => 'Pemilik Siti', 'email' => 'pemilik2@ngekos.test', 'no_hp' => '081234567003', 'role' => 'pemilik'],
            ['nama' => 'Admin Ngekos', 'email' => 'admin.ngekos@gmail.com', 'no_hp' => '081234567004', 'role' => 'admin'],
            ['nama' => 'Anak Kos Rina', 'email' => 'anak1@ngekos.test', 'no_hp' => '081234567006', 'role' => 'anak_kos'],
            ['nama' => 'Anak Kos Yoga', 'email' => 'anak2@ngekos.test', 'no_hp' => '081234567007', 'role' => 'anak_kos'],
            ['nama' => 'Anak Kos Maya', 'email' => 'anak3@ngekos.test', 'no_hp' => '081234567008', 'role' => 'anak_kos'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nama' => $data['nama'],
                    'no_hp' => $data['no_hp'],
                    'password' => Hash::make($passwords[$data['email']] ?? 'password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
