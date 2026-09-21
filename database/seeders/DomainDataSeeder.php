<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DomainDataSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::all()->keyBy('email');

        $pemilikBudi = $u['pemilik1@ngekos.test'] ?? null;
        $pemilikSiti = $u['pemilik2@ngekos.test'] ?? null;
        $admin = $u['admin.ngekos@gmail.com'] ?? null;
        $rina = $u['anak1@ngekos.test'] ?? null;
        $yoga = $u['anak2@ngekos.test'] ?? null;
        $maya = $u['anak3@ngekos.test'] ?? null;

        if (! $pemilikBudi || ! $pemilikSiti || ! $admin || ! $rina || ! $yoga || ! $maya) {
            return;
        }

        $melati = Properti::firstOrCreate(['nama' => 'Kos Melati'], [
            'pemilik_id' => $pemilikBudi->id,
            'kota' => 'Bandung',
            'alamat' => 'Jl. Melati No. 12, Bandung',
            'latitude' => -6.9034,
            'longitude' => 107.6102,
            'deskripsi' => 'Kos bersih dekat kampus, tersedia kamar AC.',
            'fasilitas' => 'WiFi, Kamar mandi dalam, Kasur, Lemari, Kipas Angin',
            'aturan' => 'Jam malam 23.00, dilarang membawa tamu menginap.',
            'denda_per_hari' => 5000,
            'harga' => 1000000,
            'harga_harian' => 50000,
            'harga_asli' => 1200000,
            'status' => 'aktif',
        ]);
        $melati->admins()->syncWithoutDetaching([$admin->id]);

        $mawar = Properti::firstOrCreate(['nama' => 'Kos Mawar'], [
            'pemilik_id' => $pemilikBudi->id,
            'kota' => 'Bandung',
            'alamat' => 'Jl. Mawar No. 8, Bandung',
            'latitude' => -6.9218,
            'longitude' => 107.6048,
            'deskripsi' => 'Kos murah dekat pasar, cocok untuk pekerja.',
            'fasilitas' => 'WiFi, Dapur bersama, Parkir motor',
            'aturan' => 'Dilarang merokok di dalam kamar.',
            'denda_per_hari' => 5000,
            'status' => 'aktif',
        ]);
        $mawar->admins()->syncWithoutDetaching([$admin->id]);

        $anggrek = Properti::firstOrCreate(['nama' => 'Kos Anggrek'], [
            'pemilik_id' => $pemilikSiti->id,
            'kota' => 'Bandung',
            'alamat' => 'Jl. Anggrek No. 3, Bandung',
            'latitude' => -6.8932,
            'longitude' => 107.6311,
            'deskripsi' => 'Kos eksklusif dengan kamar luas ber-AC.',
            'fasilitas' => 'AC, WiFi, Kulkas, Kamar mandi dalam',
            'aturan' => 'Bebas jam malam, dilarang bising.',
            'denda_per_hari' => 10000,
            'status' => 'aktif',
        ]);
        $anggrek->admins()->syncWithoutDetaching([$admin->id]);

        $a1 = Kamar::firstOrCreate(['properti_id' => $melati->id, 'nama' => 'A1'], [
            'kapasitas' => 1, 'harga_sewa_bulanan' => 1000000, 'harga_sewa_harian' => 50000, 'harga_asli' => 1200000, 'status' => 'tersedia',
        ]);
        $a2 = Kamar::firstOrCreate(['properti_id' => $melati->id, 'nama' => 'A2'], [
            'kapasitas' => 1, 'harga_sewa_bulanan' => 1000000, 'harga_sewa_harian' => 50000, 'harga_asli' => 1200000, 'status' => 'terisi',
        ]);
        $a3 = Kamar::firstOrCreate(['properti_id' => $melati->id, 'nama' => 'A3'], [
            'kapasitas' => 2, 'harga_sewa_bulanan' => 1200000, 'harga_sewa_harian' => 60000, 'harga_asli' => 1400000, 'status' => 'tersedia',
        ]);

        $b1 = Kamar::firstOrCreate(['properti_id' => $mawar->id, 'nama' => 'B1'], [
            'kapasitas' => 1, 'harga_sewa_bulanan' => 850000, 'harga_sewa_harian' => 40000, 'harga_asli' => 950000, 'status' => 'terisi',
        ]);
        $b2 = Kamar::firstOrCreate(['properti_id' => $mawar->id, 'nama' => 'B2'], [
            'kapasitas' => 1, 'harga_sewa_bulanan' => 850000, 'harga_sewa_harian' => 40000, 'harga_asli' => 950000, 'status' => 'tersedia',
        ]);

        $c1 = Kamar::firstOrCreate(['properti_id' => $anggrek->id, 'nama' => 'C1'], [
            'kapasitas' => 1, 'harga_sewa_bulanan' => 1500000, 'harga_sewa_harian' => 75000, 'status' => 'tersedia',
        ]);
        $c2 = Kamar::firstOrCreate(['properti_id' => $anggrek->id, 'nama' => 'C2'], [
            'kapasitas' => 1, 'harga_sewa_bulanan' => 1500000, 'harga_sewa_harian' => 75000, 'status' => 'terisi',
        ]);

        $sewaRina = Penyewaan::firstOrCreate(['anak_kos_id' => $rina->id, 'kamar_id' => $a2->id], [
            'properti_id' => $melati->id,
            'tanggal_masuk' => now()->subMonths(2)->startOfMonth()->toDateString(),
            'status' => 'aktif',
        ]);
        $sewaYoga = Penyewaan::firstOrCreate(['anak_kos_id' => $yoga->id, 'kamar_id' => $b1->id], [
            'properti_id' => $mawar->id,
            'tanggal_masuk' => now()->subMonth()->startOfMonth()->toDateString(),
            'status' => 'aktif',
        ]);
        Penyewaan::firstOrCreate(['anak_kos_id' => $maya->id, 'kamar_id' => $c2->id], [
            'properti_id' => $anggrek->id,
            'tanggal_masuk' => now()->subMonths(6)->startOfMonth()->toDateString(),
            'tanggal_keluar' => now()->startOfMonth()->subDay()->toDateString(),
            'status' => 'selesai',
        ]);

        $tagihanRinaJul = Tagihan::firstOrCreate(
            ['penyewaan_id' => $sewaRina->id, 'periode' => now()->subMonth()->translatedFormat('F Y')],
            ['jumlah' => 1000000, 'denda' => 0, 'jatuh_tempo' => now()->subMonth()->startOfMonth()->addDays(5)->toDateString(), 'status' => 'lunas']
        );
        $tagihanRinaAgu = Tagihan::firstOrCreate(
            ['penyewaan_id' => $sewaRina->id, 'periode' => now()->translatedFormat('F Y')],
            ['jumlah' => 1000000, 'denda' => 0, 'jatuh_tempo' => now()->startOfMonth()->addDays(5)->toDateString(), 'status' => 'belum_bayar']
        );
        $tagihanYogaJul = Tagihan::firstOrCreate(
            ['penyewaan_id' => $sewaYoga->id, 'periode' => now()->subMonth()->translatedFormat('F Y')],
            ['jumlah' => 850000, 'denda' => 0, 'jatuh_tempo' => now()->subMonth()->startOfMonth()->addDays(5)->toDateString(), 'status' => 'lunas']
        );
        $tagihanYogaAgu = Tagihan::firstOrCreate(
            ['penyewaan_id' => $sewaYoga->id, 'periode' => now()->translatedFormat('F Y')],
            ['jumlah' => 850000, 'denda' => 0, 'jatuh_tempo' => now()->startOfMonth()->addDays(5)->toDateString(), 'status' => 'belum_bayar']
        );

        Pembayaran::firstOrCreate(['tagihan_id' => $tagihanRinaJul->id, 'anak_kos_id' => $rina->id], [
            'metode' => 'transfer',
            'jumlah' => 1000000,
            'status' => 'diverifikasi',
            'diverifikasi_oleh' => $admin->id,
            'verified_at' => now()->subMonth()->startOfMonth()->addDays(3),
        ]);
        Pembayaran::firstOrCreate(['tagihan_id' => $tagihanYogaJul->id, 'anak_kos_id' => $yoga->id], [
            'metode' => 'transfer',
            'jumlah' => 850000,
            'status' => 'diverifikasi',
            'diverifikasi_oleh' => $admin->id,
            'verified_at' => now()->subMonth()->startOfMonth()->addDays(2),
        ]);
        Pembayaran::firstOrCreate(['tagihan_id' => $tagihanYogaAgu->id, 'anak_kos_id' => $yoga->id], [
            'metode' => 'transfer',
            'jumlah' => 850000,
            'status' => 'menunggu_verifikasi',
        ]);

        // Demo patungan 50/50: Rina (utama) + Maya (anggota) di kamar A3 kapasitas 2.
        if ($a3->kapasitas >= 2) {
            $sewaPatungan = Penyewaan::firstOrCreate(['anak_kos_id' => $rina->id, 'kamar_id' => $a3->id], [
                'properti_id' => $melati->id,
                'tanggal_masuk' => now()->startOfMonth()->toDateString(),
                'status' => 'aktif',
                'ktp_path' => 'ktp/demo-rina.jpg',
                'mode_hunian' => 'patungan',
            ]);

            if ($sewaPatungan->wasRecentlyCreated) {
                $a3->update(['status' => 'terisi']);
                $sewaPatungan->tagihans()->create([
                    'periode' => now()->translatedFormat('F Y'),
                    'jumlah' => $a3->harga_sewa_bulanan,
                    'denda' => 0,
                    'jatuh_tempo' => now()->startOfMonth()->addDays(5)->toDateString(),
                    'status' => 'belum_bayar',
                ]);
            } else {
                $sewaPatungan->update(['mode_hunian' => 'patungan']);
            }

            \App\Models\PenyewaanAnggota::firstOrCreate(
                ['penyewaan_id' => $sewaPatungan->id, 'user_id' => $maya->id],
                ['porsi_persen' => 50, 'status' => 'aktif', 'ktp_path' => 'ktp/demo-maya.jpg'],
            );
        }

        $budgetBulananMelati = [
            ['listrik', 'Tagihan listrik bulanan', 250000],
            ['internet', 'Langganan WiFi bulanan', 100000],
            ['air', 'PDAM bulanan', 80000],
        ];
        foreach (range(0, 5) as $i) {
            $tanggal = now()->startOfMonth()->subMonths($i)->addDays(10);
            foreach ($budgetBulananMelati as [$kategori, $keterangan, $jumlah]) {
                Pengeluaran::firstOrCreate([
                    'properti_id' => $melati->id,
                    'kategori' => $kategori,
                    'keterangan' => $keterangan.' '.now()->startOfMonth()->subMonths($i)->translatedFormat('F Y'),
                    'tanggal' => $tanggal,
                ], [
                    'jumlah' => $jumlah,
                    'dibuat_oleh' => $pemilikBudi->id,
                ]);
            }
        }

        Pengeluaran::firstOrCreate([
            'properti_id' => $melati->id,
            'kategori' => 'maintenance',
            'keterangan' => 'Servis AC kamar A2',
            'tanggal' => now()->startOfMonth()->subMonths(3)->addDays(18),
        ], ['jumlah' => 150000, 'dibuat_oleh' => $pemilikBudi->id]);

        Pengeluaran::firstOrCreate([
            'properti_id' => $mawar->id,
            'kategori' => 'maintenance',
            'keterangan' => 'Ganti kunci kamar B1',
            'tanggal' => now()->startOfMonth()->subMonths(2)->addDays(12),
        ], ['jumlah' => 90000, 'dibuat_oleh' => $pemilikBudi->id]);

        Pengeluaran::firstOrCreate([
            'properti_id' => $anggrek->id,
            'kategori' => 'gaji',
            'keterangan' => 'Gaji pengurus kos bulanan',
            'tanggal' => now()->startOfMonth()->subMonths(1)->addDays(5),
        ], ['jumlah' => 500000, 'dibuat_oleh' => $pemilikSiti->id]);
    }
}
