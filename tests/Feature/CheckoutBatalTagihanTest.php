<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\PatunganService;
use App\Services\PenyewaanService;
use App\Services\TagihanService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CheckoutBatalTagihanTest extends TestCase
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

    private function sewaBulan(User $anak, int $durasiBulan = 3): Penyewaan
    {
        Tagihan::query()->delete();

        $properti = Properti::create([
            'pemilik_id' => $this->user('pemilik1@ngekos.test')->id,
            'nama' => 'Kos Uji '.uniqid(),
            'kota' => 'Yogyakarta',
            'denda_per_hari' => 0,
            'status' => 'aktif',
        ]);
        $kamar = Kamar::create([
            'properti_id' => $properti->id,
            'nama' => 'U1',
            'kapasitas' => 2,
            'harga_sewa_bulanan' => 1000000,
            'status' => 'tersedia',
        ]);

        return app(PenyewaanService::class)->sewaKamar(
            $anak, $kamar, today()->toDateString(), $durasiBulan, null, 'ktp/uji.jpg'
        );
    }

    private function lunasiBulanBerjalan(Penyewaan $sewa): void
    {
        foreach (CheckoutService::tagihanWajibBelumLunas($sewa) as $t) {
            $t->update(['status' => 'lunas']);
        }
    }

    public function test_checkout_lunas_bulan_ini_membatalkan_masa_depan_dan_kamar_tersedia(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $sewa = $this->sewaBulan($anak, 3);

        $this->assertCount(3, $sewa->tagihans);

        $this->lunasiBulanBerjalan($sewa);

        $hasil = CheckoutService::keluarPenghuni($sewa->refresh(), $anak->id);
        $this->assertSame('pengajuan', $hasil['jenis']);

        $setuju = CheckoutService::setujuiCheckout($sewa->refresh(), $pemilik->id);

        $this->assertSame('penuh', $setuju['jenis']);
        $this->assertTrue($setuju['kamar_tersedia']);
        $this->assertSame(2, $setuju['dibatalkan']);
        $this->assertSame('selesai', $sewa->refresh()->status);
        $this->assertSame('tersedia', $sewa->refresh()->kamar->status);
        $this->assertSame(2, $sewa->tagihans()->where('status', 'batal')->count());
        $this->assertSame(1, $sewa->tagihans()->where('status', 'lunas')->count());
        $this->assertSame(0, $setuju['tagihan_belum_lunas']);
    }

    public function test_pengajuan_ditolak_bila_bulan_ini_belum_lunas(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaBulan($anak, 3);

        $this->assertNotEmpty(CheckoutService::tagihanBulanBerjalanBelumLunas($sewa));

        try {
            CheckoutService::keluarPenghuni($sewa->refresh(), $anak->id);
            $this->fail('Seharusnya ditolak karena bulan berjalan belum lunas.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('Lunasi dulu', $e->getMessage());
        }

        $this->assertNull($sewa->refresh()->permintaan_keluar_pada);
    }

    public function test_setujui_ditolak_bila_bulan_berjalan_belum_lunas(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $sewa = $this->sewaBulan($anak, 1);

        // Ajukan dulu tanpa guard? Guard ada di pengajuan juga, jadi buat
        // pengajuan manual untuk simulasi jeda pengajuan -> belum bayar.
        $sewa->update(['permintaan_keluar_pada' => now()]);

        try {
            CheckoutService::setujuiCheckout($sewa->refresh(), $pemilik->id);
            $this->fail('Seharusnya ditolak karena bulan berjalan belum lunas.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('Lunasi dulu', $e->getMessage());
        }

        $this->assertSame('aktif', $sewa->refresh()->status);
    }

    public function test_tunggakan_bulan_lalu_tetap_memblokir_walau_bulan_ini_lunas(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaBulan($anak, 3);

        // Simulasi tunggakan: tagihan paling lampau digeser ke bulan lalu, belum lunas.
        $tunggakan = $sewa->tagihans()->orderBy('jatuh_tempo')->firstOrFail();
        $tunggakan->update([
            'periode' => now()->subMonth()->translatedFormat('F Y'),
            'jatuh_tempo' => now()->subMonth()->endOfMonth()->toDateString(),
            'status' => 'belum_bayar',
        ]);

        // Bulan ini dilunasi, tunggakan bulan lalu dibiarkan nunggak.
        foreach (CheckoutService::tagihanBulanBerjalanBelumLunas($sewa->refresh()) as $t) {
            $t->update(['status' => 'lunas']);
        }
        $this->assertSame('belum_bayar', $tunggakan->refresh()->status);

        $this->assertNotEmpty(CheckoutService::tagihanWajibBelumLunas($sewa->refresh()));

        try {
            CheckoutService::keluarPenghuni($sewa->refresh(), $anak->id);
            $this->fail('Seharusnya ditolak karena ada tunggakan bulan lalu.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('Lunasi dulu', $e->getMessage());
        }

        $this->assertNull($sewa->refresh()->permintaan_keluar_pada);
    }

    public function test_batalkan_ditolak_bila_ada_pembayaran_menunggu_verifikasi(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $sewa = $this->sewaBulan($anak, 3);
        $this->lunasiBulanBerjalan($sewa);

        $masaDepan = CheckoutService::tagihanMasaDepan($sewa->refresh())->firstOrFail();
        Pembayaran::create([
            'tagihan_id' => $masaDepan->id,
            'anak_kos_id' => $anak->id,
            'metode' => 'transfer',
            'jumlah' => (float) $masaDepan->jumlah,
            'status' => 'menunggu_verifikasi',
        ]);

        CheckoutService::keluarPenghuni($sewa->refresh(), $anak->id);

        try {
            CheckoutService::setujuiCheckout($sewa->refresh(), $pemilik->id);
            $this->fail('Seharusnya ditolak karena ada menunggu_verifikasi.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('menunggu verifikasi', $e->getMessage());
        }
    }

    public function test_tagihan_batal_tidak_bisa_dibayar_dan_tidak_kena_denda(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaBulan($anak, 3);
        $this->lunasiBulanBerjalan($sewa);

        CheckoutService::keluarPenghuni($sewa->refresh(), $anak->id);
        CheckoutService::setujuiCheckout($sewa->refresh(), $this->user('pemilik1@ngekos.test')->id);

        $batal = $sewa->tagihans()->where('status', 'batal')->firstOrFail();
        $this->assertSame(0.0, TagihanService::wajibBayar($batal, $anak->id));
        $this->assertSame(0.0, TagihanService::sinkronDenda($batal->refresh()));
    }

    public function test_tombol_checkout_terkunci_bila_bulan_ini_belum_lunas(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $sewa = $this->sewaBulan($anak, 3);

        // Tombol anak kos terkunci + ada alasan.
        Volt::actingAs($anak)->test('pages.anak-kos.tagihan')
            ->assertViewHas('checkoutInfo')
            ->assertSee('Lunasi dulu');

        // Tombol pemilik terkunci juga.
        Volt::actingAs($pemilik)->test('pages.pemilik.penyewa')
            ->assertSee('belum lunas.');

        // Setelah dilunasi, tombol terbuka kembali.
        $this->lunasiBulanBerjalan($sewa);

        Volt::actingAs($anak)->test('pages.anak-kos.tagihan')
            ->assertDontSee('Lunasi dulu');
    }

    public function test_pemilik_melihat_penjelasan_tagihan_bulan_depan(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');
        $sewa = $this->sewaBulan($anak, 3);
        $this->lunasiBulanBerjalan($sewa);
        $sewa->refresh()->update(['permintaan_keluar_pada' => now()]);

        Volt::actingAs($pemilik)->test('pages.pemilik.penyewa')
            ->assertSee('Penjelasan tagihan check-out')
            ->assertSee('dibatalkan')
            ->assertSee('Tagihan sampai bulan ini sudah lunas');
    }

    public function test_patungan_partial_tidak_membatalkan_masa_depan(): void
    {
        $rina = $this->user('anak1@ngekos.test');
        $yoga = $this->user('anak2@ngekos.test');
        $sewa = $this->sewaBulan($rina, 3);
        PatunganService::tambahAnggota($sewa->refresh(), $yoga, 'ktp/yoga.jpg');

        // Lunasi porsi yoga sampai bulan berjalan agar boleh keluar partial.
        foreach ($sewa->refresh()->tagihans()->whereNotIn('status', ['lunas', 'batal'])->get() as $t) {
            if ($t->jatuh_tempo && $t->jatuh_tempo->gt(today()->copy()->endOfMonth())) {
                continue;
            }
            $porsi = PatunganService::porsiTagihan($sewa->refresh(), $t);
            Pembayaran::create([
                'tagihan_id' => $t->id,
                'anak_kos_id' => $yoga->id,
                'metode' => 'cash',
                'jumlah' => $porsi,
                'status' => 'diverifikasi',
            ]);
        }

        PatunganService::keluarkan($sewa->refresh(), $yoga->id);

        $this->assertSame('aktif', $sewa->refresh()->status);
        $this->assertSame(0, $sewa->tagihans()->where('status', 'batal')->count());
    }
}
