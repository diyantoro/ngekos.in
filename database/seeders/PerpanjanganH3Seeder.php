<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PerpanjanganService;
use Illuminate\Database\Seeder;

/**
 * Data test alur perpanjangan H-3 (tanya LANJUT/TIDAK).
 *
 * Jalankan  : php artisan db:seed --class=PerpanjanganH3Seeder
 * Picu cron : php artisan app:proses-perpanjangan
 *
 * 3 skenario (idempotent, boleh dijalankan berulang untuk me-reset):
 * 1. Rina @ A2 (Kos Melati) — sisa 3 hari, BELUM ditawarkan.
 *    Jalankan app:proses-perpanjangan: tawaran + chat notifikasi muncul
 *    di halaman Tagihan, lalu uji pilihan LANJUT/TIDAK.
 * 2. Yoga @ B1 (Kos Mawar) — sisa 3 hari, SUDAH ditawarkan ('ditawarkan').
 *    Login anak2@ngekos.test → halaman Tagihan → langsung bisa klik
 *    LANJUT / TIDAK.
 * 3. Maya @ B2 (Kos Mawar) — sudah lewat tanggal berakhir (kemarin).
 *    Jalankan app:proses-perpanjangan: sewa otomatis 'selesai' dan
 *    kamar B2 kembali 'tersedia'.
 *
 * Login semua akun demo: password.
 */
class PerpanjanganH3Seeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            DomainDataSeeder::class,
        ]);

        $rina = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $yoga = User::where('email', 'anak2@ngekos.test')->firstOrFail();
        $maya = User::where('email', 'anak3@ngekos.test')->firstOrFail();

        $melati = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $mawar = Properti::where('nama', 'Kos Mawar')->firstOrFail();

        $a2 = Kamar::where('properti_id', $melati->id)->where('nama', 'A2')->firstOrFail();
        $b1 = Kamar::where('properti_id', $mawar->id)->where('nama', 'B1')->firstOrFail();
        $b2 = Kamar::where('properti_id', $mawar->id)->where('nama', 'B2')->firstOrFail();

        // --- Skenario 1: Rina @ A2 — H-3, belum ditawarkan --------------------
        // Masa sewa 1 bulan penuh yang berakhir 3 hari lagi.
        $berakhirH3 = today()->copy()->addDays(3);

        $sewaRina = Penyewaan::firstOrCreate(
            ['anak_kos_id' => $rina->id, 'kamar_id' => $a2->id],
            [
                'properti_id' => $melati->id,
                'tanggal_masuk' => $berakhirH3->copy()->subMonthNoOverflow()->addDay()->toDateString(),
                'tanggal_berakhir' => $berakhirH3->toDateString(),
                'status' => 'aktif',
            ],
        );
        $sewaRina->update([
            'tanggal_masuk' => $berakhirH3->copy()->subMonthNoOverflow()->addDay()->toDateString(),
            'tanggal_keluar' => null,
            'tanggal_berakhir' => $berakhirH3->toDateString(),
            'status' => 'aktif',
            'perpanjangan_status' => null,
            'perpanjangan_ditawarkan_pada' => null,
        ]);
        $a2->update(['status' => 'terisi']);

        // --- Skenario 2: Yoga @ B1 — H-3, sudah ditawarkan --------------------
        $sewaYoga = Penyewaan::firstOrCreate(
            ['anak_kos_id' => $yoga->id, 'kamar_id' => $b1->id],
            [
                'properti_id' => $mawar->id,
                'tanggal_masuk' => $berakhirH3->copy()->subMonthNoOverflow()->addDay()->toDateString(),
                'tanggal_berakhir' => $berakhirH3->toDateString(),
                'status' => 'aktif',
            ],
        );
        $sewaYoga->update([
            'tanggal_masuk' => $berakhirH3->copy()->subMonthNoOverflow()->addDay()->toDateString(),
            'tanggal_keluar' => null,
            'tanggal_berakhir' => $berakhirH3->toDateString(),
            'status' => 'aktif',
        ]);
        $b1->update(['status' => 'terisi']);

        // Pakai jalur produksi PerpanjanganService supaya status 'ditawarkan'
        // + chat notifikasi dibuat persis seperti scheduler harian.
        if ($sewaYoga->refresh()->perpanjangan_status !== PerpanjanganService::STATUS_DITAWARKAN) {
            $sewaYoga->update([
                'perpanjangan_status' => null,
                'perpanjangan_ditawarkan_pada' => null,
            ]);
            PerpanjanganService::tawarkan($sewaYoga->refresh());
        }

        // --- Skenario 3: Maya @ B2 — lewat berakhir, menunggu auto-close -------
        $berakhirKemarin = today()->copy()->subDay();

        $sewaMaya = Penyewaan::firstOrCreate(
            ['anak_kos_id' => $maya->id, 'kamar_id' => $b2->id],
            [
                'properti_id' => $mawar->id,
                'tanggal_masuk' => $berakhirKemarin->copy()->subMonthNoOverflow()->addDay()->toDateString(),
                'tanggal_berakhir' => $berakhirKemarin->toDateString(),
                'status' => 'aktif',
            ],
        );
        $sewaMaya->update([
            'tanggal_masuk' => $berakhirKemarin->copy()->subMonthNoOverflow()->addDay()->toDateString(),
            'tanggal_keluar' => null,
            'tanggal_berakhir' => $berakhirKemarin->toDateString(),
            'status' => 'aktif',
            'perpanjangan_status' => PerpanjanganService::STATUS_TIDAK,
            'perpanjangan_ditawarkan_pada' => $berakhirKemarin->copy()->subDays(3)->toDateString(),
        ]);
        $b2->update(['status' => 'terisi']);

        // Tagihan bulan berjalan biar tampil realistis di halaman Tagihan.
        Tagihan::firstOrCreate(
            ['penyewaan_id' => $sewaMaya->id, 'periode' => now()->translatedFormat('F Y')],
            [
                'jumlah' => (float) $b2->harga_sewa_bulanan,
                'denda' => 0,
                'jatuh_tempo' => today()->copy()->startOfMonth()->addDays(5)->toDateString(),
                'status' => 'belum_bayar',
            ],
        );

        $this->command?->info('Data test perpanjangan H-3 siap:');
        $this->command?->info('1) Rina (anak1@ngekos.test) @ A2 — sisa 3 hari, BELUM ditawarkan.');
        $this->command?->info('2) Yoga (anak2@ngekos.test) @ B1 — sisa 3 hari, SUDAH ditawarkan → siap klik LANJUT/TIDAK.');
        $this->command?->info('3) Maya (anak3@ngekos.test) @ B2 — lewat berakhir (kemarin) → menunggu auto-close.');
        $this->command?->info('Picu scheduler: php artisan app:proses-perpanjangan');
    }
}
