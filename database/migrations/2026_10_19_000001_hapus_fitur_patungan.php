<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus fitur patungan (teman sekamar).
 *
 * Sebelum tabel dibuang, anggota aktif dipindahkan menjadi penyewa mandiri:
 * - Dibuatkan baris `penyewaans` baru di kamar yang sama (data historis).
 * - Tagihan periode berjalan yang belum dibayar dibagi rata: tagihan penyewa
 *   utama disesuaikan ke porsinya, lalu tiap anggota mendapat tagihan sendiri.
 *   Tagihan yang sudah punya pembayaran tidak dipecah agar riwayat tetap utuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penyewaan_anggotas') && Schema::hasTable('penyewaans')) {
            $this->pindahkanAnggota();
        }

        Schema::dropIfExists('penyewaan_anggotas');

        if (Schema::hasColumn('penyewaans', 'mode_hunian')) {
            Schema::table('penyewaans', function (Blueprint $table) {
                $table->dropColumn('mode_hunian');
            });
        }
    }

    public function down(): void
    {
        // Fitur patungan tidak dikembalikan; kolom & tabel sengaja tidak dibuat ulang.
    }

    private function pindahkanAnggota(): void
    {
        if (! Schema::hasColumn('penyewaans', 'mode_hunian')) {
            return;
        }

        $sewaPatungan = DB::table('penyewaans')
            ->where('mode_hunian', 'patungan')
            ->get();

        foreach ($sewaPatungan as $sewa) {
            $anggotas = DB::table('penyewaan_anggotas')
                ->where('penyewaan_id', $sewa->id)
                ->where('status', 'aktif')
                ->get();

            if ($anggotas->isEmpty()) {
                DB::table('penyewaans')->where('id', $sewa->id)->update(['mode_hunian' => 'tunggal']);

                continue;
            }

            $jumlahPenghuni = 1 + $anggotas->count();
            $tagihanDibagi = $this->bagiTagihanBerjalan($sewa->id, $jumlahPenghuni);

            foreach ($anggotas as $anggota) {
                $sudahAda = DB::table('penyewaans')
                    ->where('anak_kos_id', $anggota->user_id)
                    ->where('kamar_id', $sewa->kamar_id)
                    ->where('status', 'aktif')
                    ->exists();

                if ($sudahAda) {
                    continue;
                }

                $penyewaanBaruId = DB::table('penyewaans')->insertGetId([
                    'anak_kos_id' => $anggota->user_id,
                    'kamar_id' => $sewa->kamar_id,
                    'properti_id' => $sewa->properti_id,
                    'tanggal_masuk' => $sewa->tanggal_masuk,
                    'tanggal_keluar' => null,
                    'tanggal_berakhir' => $sewa->tanggal_berakhir ?? null,
                    'perpanjangan_status' => null,
                    'perpanjangan_ditawarkan_pada' => null,
                    'status' => 'aktif',
                    'permintaan_keluar_pada' => null,
                    'ktp_path' => $anggota->ktp_path,
                    'mode_hunian' => 'tunggal',
                    'created_at' => $sewa->created_at ?? now(),
                    'updated_at' => now(),
                ]);

                foreach ($tagihanDibagi as $tagihan) {
                    DB::table('tagihans')->insert([
                        'penyewaan_id' => $penyewaanBaruId,
                        'periode' => $tagihan['periode'],
                        'jumlah' => $tagihan['porsi'],
                        'jenis' => $tagihan['jenis'],
                        'denda' => 0,
                        'jatuh_tempo' => $tagihan['jatuh_tempo'],
                        'status' => 'belum_bayar',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('penyewaans')->where('id', $sewa->id)->update(['mode_hunian' => 'tunggal']);
        }
    }

    /**
     * Bagi tagihan belum lunas yang belum punya pembayaran sama sekali.
     *
     * @return array<int, array{periode: string, jenis: string, jatuh_tempo: string, porsi: float}>
     */
    private function bagiTagihanBerjalan(int $penyewaanId, int $jumlahPenghuni): array
    {
        if ($jumlahPenghuni < 2) {
            return [];
        }

        $hasil = [];

        $tagihans = DB::table('tagihans')
            ->where('penyewaan_id', $penyewaanId)
            ->whereNotIn('status', ['lunas', 'batal'])
            ->get();

        foreach ($tagihans as $tagihan) {
            $adaPembayaran = DB::table('pembayarans')->where('tagihan_id', $tagihan->id)->exists();

            if ($adaPembayaran) {
                continue;
            }

            $total = (float) $tagihan->jumlah + (float) $tagihan->denda;
            $porsi = round($total / $jumlahPenghuni, 2);

            DB::table('tagihans')->where('id', $tagihan->id)->update([
                'jumlah' => $porsi,
                'denda' => 0,
                'updated_at' => now(),
            ]);

            $hasil[] = [
                'periode' => $tagihan->periode,
                'jenis' => $tagihan->jenis ?? 'sewa',
                'jatuh_tempo' => $tagihan->jatuh_tempo,
                'porsi' => $porsi,
            ];
        }

        return $hasil;
    }
};
