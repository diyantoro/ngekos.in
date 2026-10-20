<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\User;
use App\Services\PembayaranService;
use App\Services\PenyewaanService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingDpVerifikasiTest extends TestCase
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

    private function bookingPending(User $anak, Kamar $kamar): Penyewaan
    {
        return app(PenyewaanService::class)->sewaKamar(
            $anak, $kamar, today()->toDateString(), 1, null, null, 'bukti-dp-test.jpg'
        );
    }

    public function test_booking_tanpa_bukti_dp_ditolak(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $kamar = Properti::where('nama', 'Kos Melati')->firstOrFail()
            ->kamars()->where('status', 'tersedia')->firstOrFail();

        try {
            app(PenyewaanService::class)->sewaKamar($anak, $kamar, today()->toDateString(), 1);
            $this->fail('Seharusnya menolak booking tanpa bukti DP.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('bukti transfer', $e->getMessage());
        }

        $this->assertSame('tersedia', $kamar->refresh()->status->value);
    }

    public function test_booking_ditolak_bila_pemilik_belum_punya_rekening(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $properti->update(['bank_nama' => null, 'no_rekening' => null, 'rekening_nama' => null]);
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        try {
            app(PenyewaanService::class)->sewaKamar($anak, $kamar, today()->toDateString(), 1, null, null, 'bukti.jpg');
            $this->fail('Seharusnya menolak karena rekening kosong.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('rekening', $e->getMessage());
        }

        // Setelah pemilik mencantumkan rekening, booking jalan.
        $properti->update(['bank_nama' => 'BCA', 'no_rekening' => '111222333', 'rekening_nama' => 'Budi']);
        $sewa = $this->bookingPending($anak, $kamar->refresh());
        $this->assertSame('menunggu_verifikasi', $sewa->status);
    }

    public function test_setujui_booking_mengaktifkan_sewa(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->bookingPending($anak, $kamar);
        $this->assertSame('menunggu_verifikasi', $sewa->status);
        $this->assertSame('terisi', $kamar->refresh()->status->value);

        $hasil = app(PenyewaanService::class)->setujuiBooking($sewa->refresh(), $pemilik->id);

        $this->assertTrue($hasil['tagihan_lunas']);
        $this->assertSame('dibooking', $sewa->refresh()->status);
        $this->assertSame('lunas', $sewa->tagihans()->where('jenis', 'dp')->firstOrFail()->status);
        $this->assertSame('terisi', $kamar->refresh()->status->value);

        // Pemilik menandai sudah masuk -> sewa aktif, checkout ketat berlaku.
        \App\Services\CheckoutService::tandaiMasuk($sewa->refresh(), $pemilik->id);
        $this->assertSame('aktif', $sewa->refresh()->status);
    }

    public function test_tolak_booking_membatalkan_dan_melepas_kamar(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->bookingPending($anak, $kamar);

        app(PenyewaanService::class)->tolakBooking($sewa->refresh(), $pemilik->id);

        $sewa->refresh();
        $this->assertSame('dibatalkan', $sewa->status);
        $this->assertSame(0, $sewa->tagihans()->whereNotIn('status', ['batal'])->count());
        $this->assertSame('tersedia', $kamar->refresh()->status->value);
        $this->assertSame(
            'ditolak',
            Pembayaran::whereHas('tagihan', fn ($q) => $q->where('penyewaan_id', $sewa->id))->firstOrFail()->status
        );
    }

    public function test_verifikasi_pembayaran_dp_langsung_mengaktifkan_booking(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->bookingPending($anak, $kamar);
        $bayar = Pembayaran::whereHas('tagihan', fn ($q) => $q->where('penyewaan_id', $sewa->id))
            ->where('status', 'menunggu_verifikasi')
            ->firstOrFail();

        // Jalur halaman Tagihan pemilik: verifikasi pembayaran mentah.
        $hasil = PembayaranService::verifikasi($bayar, $pemilik->id, 'diverifikasi');

        $this->assertTrue($hasil['booking_disetujui']);
        $this->assertSame('dibooking', $sewa->refresh()->status);
    }

    public function test_bayar_tagihan_pending_ditolak_api(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->bookingPending($anak, $kamar);
        $sisa = $sewa->tagihans()->where('jenis', 'sewa')->firstOrFail();

        Sanctum::actingAs($anak, ['*']);

        $this->postJson('/api/dashboard/anak-kos/bayar', [
            'tagihan_id' => $sisa->id,
            'metode' => 'cash',
            'jumlah' => (float) $sisa->jumlah,
        ])->assertStatus(422)
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'belum diverifikasi'));
    }

    public function test_api_verifikasi_dp_mengaktifkan_booking(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $sewa = $this->bookingPending($anak, $kamar);
        $bayar = Pembayaran::whereHas('tagihan', fn ($q) => $q->where('penyewaan_id', $sewa->id))
            ->where('status', 'menunggu_verifikasi')
            ->firstOrFail();

        Sanctum::actingAs($pemilik, ['*']);

        $this->postJson("/api/dashboard/pemilik/pembayaran/{$bayar->id}/verifikasi", [
            'status' => 'diverifikasi',
        ])->assertOk()
            ->assertJsonPath('booking_disetujui', true);

        $this->assertSame('dibooking', $sewa->refresh()->status);

        // Sewa baru 'aktif' setelah sewa pertama dibayar & terverifikasi.
        $sisa = $sewa->tagihans()->where('jenis', 'sewa')->firstOrFail();
        $buktiSisa = \App\Services\BuktiStorage::simpan(
            \Illuminate\Http\Testing\File::fake()->image('bukti-sisa.png'),
            'bukti-pembayaran'
        );
        $bayarSisa = Pembayaran::create([
            'tagihan_id' => $sisa->id,
            'anak_kos_id' => $anak->id,
            'metode' => 'transfer',
            'jumlah' => $sisa->jumlah,
            'bukti' => $buktiSisa,
            'status' => 'menunggu_verifikasi',
        ]);

        PembayaranService::verifikasi($bayarSisa, $pemilik->id, 'diverifikasi');

        $this->assertSame('aktif', $sewa->refresh()->status);
    }
}
