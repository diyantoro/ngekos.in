<?php

namespace Tests;

use App\Models\Kamar;
use App\Models\Penyewaan;
use App\Models\User;
use App\Services\PenyewaanService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Nonaktifkan Vite di semua test agar tidak butuh public/build/manifest.json
        // (di CI manifest tidak ada karena /public/build di-gitignore dan tanpa npm build).
        $this->withoutVite();
    }

    /**
     * Booking bulanan + verifikasi DP oleh pemilik sehingga booking fiks
     * lalu pemilik menandai SUDAH MASUK — sewa AKTIF. Cerminan alur produksi:
     * bukti DP wajib, sewa aktif saat penyewa masuk / sewa pertama dibayar.
     */
    protected function sewaBulananAktif(User $anak, Kamar $kamar, int $durasiBulan = 1): Penyewaan
    {
        $kamar->loadMissing('properti');
        $service = app(PenyewaanService::class);

        $sewa = $service->sewaKamar(
            $anak, $kamar, today()->toDateString(), $durasiBulan, null, null, 'bukti-dp-test.jpg'
        );
        $service->setujuiBooking($sewa->refresh(), (int) $kamar->properti->pemilik_id);
        \App\Services\CheckoutService::tandaiMasuk($sewa->refresh(), (int) $kamar->properti->pemilik_id);

        return $sewa->refresh();
    }
}
