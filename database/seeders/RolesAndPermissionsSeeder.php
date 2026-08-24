<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Daftar permission yang akan dibuat.
     * Nama permission memakai pola {modul}.{aksi} agar mudah dipetakan ke policy.
     */
    public function run(): void
    {
        // Reset cache permission agar definisi terbaru langsung terbaca.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Properti (Modul 2)
            'properti.lihat', 'properti.buat', 'properti.ubah', 'properti.hapus',
            // Penugasan admin ke properti (Modul 2)
            'properti.kelola-admin',
            // Kamar (Modul 2)
            'kamar.lihat', 'kamar.buat', 'kamar.ubah', 'kamar.hapus',
            // Booking (Modul 3)
            'booking.lihat', 'booking.buat', 'booking.ubah', 'booking.batal', 'booking.hapus',
            // Penyewaan / check-in-out (Modul 4)
            'penyewaan.lihat', 'penyewaan.buat', 'penyewaan.ubah', 'penyewaan.hapus',
            // Tagihan & Pembayaran (Modul 5)
            'tagihan.lihat', 'tagihan.ubah',
            'pembayaran.lihat', 'pembayaran.buat', 'pembayaran.verifikasi',
            // Konfigurasi sistem (khusus Super Admin)
            'konfigurasi.kelola',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Definisi role -> permission.
        $roles = [
            'super_admin' => $permissions,
            'pemilik' => array_values(array_diff($permissions, ['konfigurasi.kelola'])),
            'admin' => [
                'kamar.lihat', 'kamar.ubah',
                'booking.lihat', 'booking.ubah',
                'penyewaan.lihat', 'penyewaan.buat', 'penyewaan.ubah',
                'tagihan.lihat', 'tagihan.ubah',
                'pembayaran.lihat', 'pembayaran.verifikasi',
            ],
            'anak_kos' => [
                'booking.lihat', 'booking.buat', 'booking.batal',
                'penyewaan.lihat',
                'tagihan.lihat',
                'pembayaran.lihat', 'pembayaran.buat',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
