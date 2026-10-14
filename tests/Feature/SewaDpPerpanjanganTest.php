<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use App\Services\PenyewaanService;
use App\Services\PerpanjanganService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SewaDpPerpanjanganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_sewa_bulanan_membuat_dp_dan_sisa(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();
        $harga = (float) $kamar->harga_sewa_bulanan;
        $this->assertGreaterThan(PenyewaanService::DP_NOMINAL, $harga);

        $penyewaan = app(PenyewaanService::class)->sewaKamar($user, $kamar, today()->toDateString(), 1, null, null, 'bukti-dp-test.jpg');

        // Booking masih pending: DP + sisa sudah dibuat, sewa belum aktif.
        $this->assertSame('menunggu_verifikasi', $penyewaan->status);
        $this->assertSame(2, $penyewaan->tagihans()->count());

        $dp = $penyewaan->tagihans()->where('jenis', 'dp')->firstOrFail();
        $this->assertEquals(PenyewaanService::DP_NOMINAL, (float) $dp->jumlah);
        $this->assertSame(today()->toDateString(), $dp->jatuh_tempo->toDateString());

        $sisa = $penyewaan->tagihans()->where('jenis', 'sewa')->firstOrFail();
        $this->assertEquals($harga - PenyewaanService::DP_NOMINAL, (float) $sisa->jumlah);

        $this->assertEquals($harga, (float) $dp->jumlah + (float) $sisa->jumlah);

        // Masa sewa 1 bulan.
        $this->assertSame(
            today()->copy()->addMonthNoOverflow()->subDay()->toDateString(),
            $penyewaan->refresh()->tanggal_berakhir->toDateString(),
        );
    }

    public function test_tawaran_h3_lanjut_dan_tutup_otomatis(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $penyewaan = $this->sewaBulananAktif($user, $kamar, 1);
        $penyewaan->update(['tanggal_berakhir' => today()->copy()->addDays(3)->toDateString()]);

        // H-3: ditawarkan.
        $hasil = PerpanjanganService::prosesHarian(today());
        $this->assertSame(1, $hasil['ditawarkan']);
        $this->assertSame('ditawarkan', $penyewaan->refresh()->perpanjangan_status);

        // Lanjut: berakhir +1 bulan + tagihan baru.
        $berakhirLama = $penyewaan->tanggal_berakhir->copy();
        $jumlahTagihan = $penyewaan->tagihans()->count();
        PerpanjanganService::lanjutkan($penyewaan->refresh(), $user->id);

        $baru = $penyewaan->refresh();
        $this->assertNull($baru->perpanjangan_status);
        $this->assertSame(
            $berakhirLama->copy()->addMonthNoOverflow()->toDateString(),
            $baru->tanggal_berakhir->toDateString(),
        );
        $this->assertSame($jumlahTagihan + 1, $baru->tagihans()->count());

        // Tidak lanjut lalu lewat berakhir: otomatis tutup + kamar tersedia.
        $baru->update([
            'tanggal_berakhir' => today()->copy()->subDay()->toDateString(),
            'perpanjangan_status' => 'tidak',
        ]);

        $hasil = PerpanjanganService::prosesHarian(today());
        $this->assertSame(1, $hasil['ditutup']);

        $this->assertSame('selesai', $baru->refresh()->status);
        $this->assertSame('tersedia', $kamar->refresh()->status->value);
    }

    public function test_diam_sampai_lewat_berakhir_otomatis_kosong(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $penyewaan = $this->sewaBulananAktif($user, $kamar, 1);
        $penyewaan->update(['tanggal_berakhir' => today()->copy()->subDay()->toDateString()]);

        PerpanjanganService::prosesHarian(today());

        $this->assertSame('selesai', $penyewaan->refresh()->status);
        $this->assertSame('tersedia', $kamar->refresh()->status->value);
    }
}
