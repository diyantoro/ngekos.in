<?php

namespace App\Console\Commands;

use App\Models\Penyewaan;
use App\Services\CheckoutService;
use App\Services\PenyewaanService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Peringatan sebelum batas batal-otomatis: setiap hari memeriksa penyewaan
 * berstatus 'dibooking' (DP terverifikasi tapi penyewa belum masuk).
 * - Hari ke-3 (TOLERANSI minus 2) telat masuk: kirim pesan peringatan
 *   SEKALI ke thread chat (penyewa + pemilik membaca sama-sama): "DP hangus
 *   dan booking dibatalkan jika tidak konfirmasi masuk sebelum [tanggal]".
 * - Batas batal otomatis sendiri tetap dijalankan pemilik lewat tombol
 *   di dashboard (checkoutPemilik / batalOtomatis), bukan otomatis disini.
 */
class ProsesBookingBatal extends Command
{
    protected $signature = 'app:proses-booking-batal';

    protected $description = 'Peringatan penyewa & pemilik sebelum batas batal-otomatis booking (belum masuk kos)';

    public function handle(): int
    {
        $hariIni = Carbon::today();
        $toleransi = CheckoutService::TOLERANSI_BATAL_HARI;
        $terkirim = 0;

        Penyewaan::query()
            ->where('status', 'dibooking')
            ->whereNotNull('tanggal_masuk')
            ->whereNull('peringatan_batal_pada')
            ->with(['kamar', 'properti', 'tagihans'])
            ->chunkById(100, function ($sewaans) use ($hariIni, $toleransi, &$terkirim) {
                foreach ($sewaans as $sewa) {
                    $telat = CheckoutService::hariTelatMasuk($sewa, $hariIni);

                    if ($telat < $toleransi - 2) {
                        continue; // masih dalam masa tunggu normal
                    }

                    $batas = $sewa->tanggal_masuk->copy()->addDays($toleransi);
                    $dp = $sewa->tagihans->first(fn ($t) => PenyewaanService::isDp($t));

                    DB::transaction(function () use ($sewa, $batas, $telat, $dp) {
                        // Kunci baris supaya jadwal ganda tidak kirim dobel.
                        $segi = Penyewaan::whereKey($sewa->id)->lockForUpdate()->first();
                        if (! $segi || $segi->status !== 'dibooking' || $segi->peringatan_batal_pada) {
                            return;
                        }

                        \App\Models\ChatPesan::notifikasiTagihan(
                            (int) $sewa->properti_id,
                            (int) $sewa->anak_kos_id,
                            (int) ($sewa->properti?->pemilik_id ?? $sewa->anak_kos_id),
                            'Peringatan: booking kamar '.$sewa->kamar?->nama.' di '.$sewa->properti?->nama
                                .' sudah telat masuk '.$telat.' hari. '
                                .'Gagal masuk sampai '.$batas->locale('id')->translatedFormat('d F Y').' '
                                .'maka booking dibatalkan (pemilik bisa batal otomatis), DP Rp'
                                .number_format((float) ($dp?->jumlah ?? 0), 0, ',', '.')
                                .' hangus, dan kamar kembali tersedia. '
                                .'Sudah masuk? Jangan lupa bayar sewa pertama / koordinasi dengan pemilik ya.',
                        );

                        $segi->update(['peringatan_batal_pada' => now()]);
                    });

                    $terkirim++;
                }
            });

        $this->info("Peringatan booking-batal terkirim: {$terkirim}.");

        return self::SUCCESS;
    }
}
