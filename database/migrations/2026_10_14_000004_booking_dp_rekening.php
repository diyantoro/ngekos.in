<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Booking DP terverifikasi + rekening pemilik:
     * - propertis: bank_nama, no_rekening, rekening_nama (tujuan transfer DP).
     * - penyewaans.status: tambah 'menunggu_verifikasi' (booking menunggu
     *   verifikasi bukti DP oleh pemilik/admin) dan 'dibatalkan'
     *   (booking ditolak / dibatalkan sebelum aktif).
     */
    public function up(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->string('bank_nama', 50)->nullable()->after('denda_per_hari');
            $table->string('no_rekening', 50)->nullable()->after('bank_nama');
            $table->string('rekening_nama', 100)->nullable()->after('no_rekening');
        });

        // SQLite (dipakai test) tidak mengenal ENUM: lewati, kolom string biasa
        // sudah bisa menampung nilai baru. MySQL: perluas daftar ENUM.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE penyewaans MODIFY COLUMN status ENUM('aktif','selesai','menunggu_verifikasi','dibatalkan') NOT NULL DEFAULT 'aktif'"
            );
        }
    }

    public function down(): void
    {
        // Kembalikan baris berstatus baru ke 'selesai' agar enum lama valid.
        DB::table('penyewaans')
            ->whereIn('status', ['menunggu_verifikasi', 'dibatalkan'])
            ->update(['status' => 'selesai']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE penyewaans MODIFY COLUMN status ENUM('aktif','selesai') NOT NULL DEFAULT 'aktif'"
            );
        }

        Schema::table('propertis', function (Blueprint $table) {
            $table->dropColumn(['bank_nama', 'no_rekening', 'rekening_nama']);
        });
    }
};
