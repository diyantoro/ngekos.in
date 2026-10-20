<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

/**
 * Dua fitur dalam satu migration:
 *
 * 1. Siklus sewa baru: status 'dibooking' — booking DP tersverifikasi tapi
 *    penyewa BELUM masuk. Di fase ini sewa bisa dibatalkan (DP hangus ke
 *    pemilik, tagihan lain batal, kamar langsung tersedia). Kolom tambahan
 *    peringatan_batal_pada menandai pesan peringatan sebelum batas batal
 *    otomatis (toleransi +5 hari) sudah terkirim — supaya schedule harian
 *    tidak mengirim ulang.
 *    Data lama: penyewaan 'aktif' yang BELUM pernah ada pembayaran sewa
 *    terverifikasi (hanya DP) dianggap belum masuk -> 'dibooking'.
 *
 * 2. Kota kanonik: kolom kota_kanonik hasil \App\Support\DaftarKota::kanonik()
 *    dipakai filter & dropdown katalog supaya varian 'Kota X'/'Kab. X'/salah
 *    ketik tidak lagi bocor dari filter, dan katalog jadi satu cara pencarian.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- penyewaans.status: tambah 'dibooking' ---
        // SQLite (dipakai test) tidak mengenal ENUM: lewati, kolom string
        // biasa sudah bisa menampung nilai baru. MySQL: perluas daftar ENUM.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE penyewaans MODIFY COLUMN status ENUM('aktif','selesai','dibooking','menunggu_verifikasi','dibatalkan') NOT NULL DEFAULT 'aktif'"
            );
        }

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->timestamp('peringatan_batal_pada')->nullable()->after('permintaan_keluar_pada');
        });

        // Data lama: sewa 'aktif' yang belum pernah bayar sewa (tanpa
        // pembayaran terverifikasi sama sekali) dianggap belum masuk.
        DB::table('penyewaans as p')
            ->where('p.status', 'aktif')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tagihans as t')
                    ->join('pembayarans as b', 'b.tagihan_id', '=', 't.id')
                    ->whereColumn('t.penyewaan_id', 'p.id')
                    ->where('b.status', 'diverifikasi');
            })
            ->update(['p.status' => 'dibooking']);

        // --- propertis.kota_kanonik + index ---
        Schema::table('propertis', function (Blueprint $table) {
            $table->string('kota_kanonik', 100)->nullable()->after('kota');
        });

        Schema::table('propertis', function (Blueprint $table) {
            $table->index(['status', 'kota_kanonik'], 'propertis_status_kota_kanonik_index');
        });

        $kanonik = fn ($v) => \App\Support\DaftarKota::kanonik(trim((string) $v)) ?? trim((string) $v);

        try {
            DB::table('propertis')
                ->whereNotNull('kota')
                ->where('kota', '!=', '')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($kanonik) {
                    foreach ($rows as $r) {
                        // Semua baris ber-kota wajib punya kota_kanonik (bukan
                        // hanya yang bentuknya berbeda) supaya filter katalog
                        // `where('kota_kanonik', ...)` tidak kehilangan kos.
                        $nom = trim((string) $kanonik($r->kota));

                        if ($nom === '') {
                            continue;
                        }

                        DB::table('propertis')->where('id', $r->id)->update(['kota_kanonik' => $nom]);
                    }
                });

        } catch (\Throwable $e) {
            Log::warning('Backfill kota_kanonik gagal: '.$e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->dropIndex('propertis_status_kota_kanonik_index');
            $table->dropColumn('kota_kanonik');
        });

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->dropColumn('peringatan_batal_pada');
        });

        // Pastikan tak ada baris 'dibooking' sebelum enum dikembalikan.
        DB::statement("UPDATE penyewaans SET status = 'aktif' WHERE status = 'dibooking'");

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE penyewaans MODIFY COLUMN status ENUM('aktif','selesai','menunggu_verifikasi','dibatalkan') NOT NULL DEFAULT 'aktif'"
            );
        }
    }
};
