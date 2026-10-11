<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seeder kompatibilitas: role kini enum di users.role.
     * Tidak ada tabel roles/permissions lagi; seeder ini dipertahankan
     * kosong agar urutan DatabaseSeeder lama tetap valid.
     */
    public function run(): void
    {
        foreach (UserRole::values() as $nilai) {
            // Validasi nilai enum tetap sinkron dengan migrasi users.role.
            UserRole::tryFrom($nilai) ?? throw new \RuntimeException("Nilai role tidak valid: {$nilai}");
        }
    }
}
