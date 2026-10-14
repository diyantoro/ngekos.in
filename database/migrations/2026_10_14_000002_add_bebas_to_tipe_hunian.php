<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah opsi 'bebas' ke kolom enum tipe_hunian (kos bebas:
     * menerima semua gender tanpa batasan putra/putri).
     *
     * Hanya jalan di MySQL (ALTER ... MODIFY). Di SQLite (pengujian)
     * kolom enum tidak diberlakukan sehingga migrasi dilewati.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $cs = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';

        DB::statement("ALTER TABLE `propertis` MODIFY `tipe_hunian` ENUM('putra','putri','campur','bebas') {$cs} NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Kembalikan opsi semula. Baris bernilai 'bebas' (bila ada)
        // dipetakan ke 'campur' dulu agar MODIFY tidak gagal.
        DB::table('propertis')->where('tipe_hunian', 'bebas')->update(['tipe_hunian' => 'campur']);

        $cs = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';

        DB::statement("ALTER TABLE `propertis` MODIFY `tipe_hunian` ENUM('putra','putri','campur') {$cs} NULL");
    }
};
