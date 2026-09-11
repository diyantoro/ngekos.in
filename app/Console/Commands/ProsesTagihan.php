<?php

namespace App\Console\Commands;

use App\Models\Pengaturan;
use App\Models\Penyewaan;
use App\Services\TagihanService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ProsesTagihan extends Command
{
    protected $signature = 'app:proses-tagihan';

    protected $description = 'Membuat tagihan bulanan penyewaan aktif dan menghitung denda keterlambatan';

    public function handle(): int
    {
        $hariIni = Carbon::today();

        Penyewaan::query()
            ->where('status', 'aktif')
            ->with(['kamar', 'properti', 'tagihans'])
            ->chunkById(100, function ($sewaans) use ($hariIni) {
                foreach ($sewaans as $sewaan) {
                    $dibuat = $this->buatTagihanKurang($sewaan, $hariIni);
                    $diubah = $this->hitungDenda($sewaan, $hariIni);

                    $this->info("Penyewaan #{$sewaan->id}: {$dibuat} tagihan baru, {$diubah} denda diperbarui.");
                }
            });

        $this->components->info('Proses tagihan selesai.');

        return self::SUCCESS;
    }

    /**
     * Pastikan setiap bulan sejak tanggal masuk punya satu tagihan
     * (periode bulan kalender, jatuh tempo akhir bulan).
     * Penyewaan harian (tanggal_keluar sudah terisi & ditagih sekaligus saat
     * booking) dilewati agar tagihannya tidak dobel dibuat.
     */
    private function buatTagihanKurang(Penyewaan $sewaan, Carbon $hariIni): int
    {
        if (! $sewaan->kamar) {
            return 0;
        }

        if ($sewaan->tanggal_keluar !== null) {
            return 0;
        }

        $dibuat = 0;
        $bulan = $sewaan->tanggal_masuk->copy()->startOfDay()->startOfMonth();
        $batas = $hariIni->copy()->startOfMonth();

        while ($bulan->lessThanOrEqualTo($batas)) {
            $sudahAda = $sewaan->tagihans->contains(
                fn ($t) => $t->jatuh_tempo?->isSameMonth($bulan)
            );

            if (! $sudahAda) {
                $sewaan->tagihans()->create([
                    'periode' => $bulan->translatedFormat('F Y'),
                    'jumlah' => $sewaan->kamar->harga_sewa_bulanan,
                    'denda' => 0,
                    'jatuh_tempo' => Pengaturan::jatuhTempoUntuk($bulan)->toDateString(),
                    'status' => 'belum_bayar',
                ]);

                $dibuat++;
            }

            $bulan->addMonthNoOverflow();
        }

        return $dibuat;
    }

    /**
     * Denda = denda_per_hari x hari terlambat (setelah lewat jatuh tempo),
     * dihitung ulang tiap eksekusi selama tagihan belum lunas.
     */
    private function hitungDenda(Penyewaan $sewaan, Carbon $hariIni): int
    {
        $diubah = 0;

        $sewaan->tagihans()
            ->where('status', '!=', 'lunas')
            ->whereDate('jatuh_tempo', '<=', $hariIni->toDateString())
            ->get()
            ->each(function ($tagihan) use ($hariIni, &$diubah) {
                $sebelum = (float) $tagihan->denda;
                $sesudah = TagihanService::sinkronDenda($tagihan, $hariIni);

                if ($sebelum !== $sesudah) {
                    $diubah++;
                }
            });

        return $diubah;
    }
}
