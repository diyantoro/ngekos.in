<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\PembayaranService;
use App\Services\PenyewaanService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pembatalan sewa awal: sewa berstatus 'dibooking' (DP terverifikasi tapi
 * penyewa belum masuk) bisa dibatalkan — DP hangus untuk pemilik, tagihan
 * lain batal, sewa ditutup, kamar langsung tersedia lagi.
 */
class BatalSewaAwalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function sewaDibooking(Kamar $kamar, User $anak): Penyewaan
    {
        $sewa = app(PenyewaanService::class)->sewaKamar(
            $anak, $kamar, today()->toDateString(), 1, null, null, 'bukti-dp-test.jpg'
        );

        app(PenyewaanService::class)->setujuiBooking($sewa->refresh(), (int) $kamar->properti->pemilik_id);

        return $sewa->refresh();
    }

    public function test_dp_terverifikasi_menghasilkan_dibooking_bukan_aktif(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->sewaDibooking($kamar, $anak);

        $this->assertSame('dibooking', $sewa->status);
        $this->assertSame('terisi', $kamar->refresh()->status->value);
    }

    public function test_pembayaran_sewa_pertama_otomatis_menandai_masuk(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->sewaDibooking($kamar, $anak);

        // Simulasikan pembayaran sewa (sisa) + verifikasi pemilik.
        $sisa = $sewa->tagihans()->where('jenis', 'sewa')->firstOrFail();
        $bayar = $sisa->pembayarans()->create([
            'anak_kos_id' => $anak->id,
            'metode' => 'cash',
            'jumlah' => $sisa->jumlah,
            'status' => 'menunggu_verifikasi',
        ]);

        PembayaranService::verifikasi($bayar, $pemilik->id, 'diverifikasi');

        $this->assertSame('aktif', $sewa->refresh()->status);
    }

    public function test_penghuni_ajukan_batal_lalu_pemilik_setujui_dp_hangus_kamar_tersedia(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->sewaDibooking($kamar, $anak);
        $dpJumlah = (float) $sewa->tagihans()->where('jenis', 'dp')->firstOrFail()->jumlah;

        CheckoutService::ajukanBatalSewa($sewa->refresh(), $anak->id);
        $this->assertNotNull($sewa->refresh()->permintaan_keluar_pada);

        $hasil = CheckoutService::setujuiBatalSewa($sewa->refresh(), $pemilik->id);

        $this->assertSame('batal_sewa', $hasil['jenis']);
        $this->assertEquals($dpJumlah, $hasil['dp_hangus']);
        $this->assertTrue($hasil['kamar_tersedia']);

        $sewa->refresh();
        $this->assertSame('dibatalkan', $sewa->status);
        // DP tetap LUNAS (hangus untuk pemilik); tagihan lain dibatalkan.
        $this->assertSame('lunas', $sewa->tagihans()->where('jenis', 'dp')->firstOrFail()->status);
        $this->assertSame(0, $sewa->tagihans()->where('jenis', 'sewa')->whereNotIn('status', ['batal'])->count());
        $this->assertSame('tersedia', $kamar->refresh()->status->value);

        // Pesan batal terkirim ke thread chat.
        $this->assertDatabaseHas('chat_pesans', [
            'properti_id' => $properti->id,
            'anak_kos_id' => $anak->id,
        ]);
    }

    public function test_sudah_aktif_tidak_bisa_pakai_jalur_batal_awal(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->sewaDibooking($kamar, $anak);
        CheckoutService::tandaiMasuk($sewa->refresh(), $pemilik->id);

        try {
            CheckoutService::ajukanBatalSewa($sewa->refresh(), $anak->id);
            $this->fail('Sewa aktif tidak boleh dibatalkan lewat jalur batal awal.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Dibooking', $e->getMessage());
        }
    }

    public function test_batal_otomatis_diblokir_sebelum_toleransi_lalu_ijin_setelah_lewat(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->sewaDibooking($kamar, $anak);

        // Masa tenggang masih berjalan: diblokir.
        try {
            CheckoutService::batalOtomatis($sewa->refresh(), $pemilik->id);
            $this->fail('Belum boleh batal otomatis.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Belum boleh', $e->getMessage());
        }

        // Lewat toleransi (tanggal masuk menggeser mundur > 5 hari).
        $sewa->update(['tanggal_masuk' => today()->subDays(CheckoutService::TOLERANSI_BATAL_HARI + 1)->toDateString()]);

        $hasil = CheckoutService::batalOtomatis($sewa->refresh(), $pemilik->id);

        $this->assertSame('batal_sewa', $hasil['jenis']);
        $this->assertTrue($hasil['kamar_tersedia']);
        $this->assertSame('dibatalkan', $sewa->refresh()->status);
        $this->assertSame('lunas', $sewa->tagihans()->where('jenis', 'dp')->firstOrFail()->status);
        $this->assertSame('tersedia', $kamar->refresh()->status->value);
    }

    public function test_api_pemilik_setujui_checkout_menerima_dibooking(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->sewaDibooking($kamar, $anak);
        CheckoutService::ajukanBatalSewa($sewa->refresh(), $anak->id);
        $this->actingAs($pemilik)
            ->postJson("/api/dashboard/pemilik/sewaan/{$sewa->id}/setujui-checkout")
            ->assertOk()
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'Pembatalan sewa') && str_contains($m, 'hangus'));

        $this->assertSame('dibatalkan', $sewa->refresh()->status);
        $this->assertSame('tersedia', $kamar->refresh()->status->value);
    }
}
