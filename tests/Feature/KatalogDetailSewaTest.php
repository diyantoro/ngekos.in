<?php

namespace Tests\Feature;

use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\User;
use App\Services\PenyewaanService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class KatalogDetailSewaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_anak_kos_bisa_sewa_kamar_langsung_dari_detail(): void
    {
        Storage::fake('private');

        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $this->actingAs($user)
            ->get(route('kos.detail', $properti->id))
            ->assertOk()
            ->assertSeeVolt('pages.katalog.detail');

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);

        $component->call('pesanKamar', $kamar->id)
            ->assertSet('modalKamarId', $kamar->id);

        // Tanpa bukti DP: booking bulanan ditolak.
        $component->set('tanggalMasuk', today()->toDateString())
            ->call('konfirmasiSewa')
            ->assertHasErrors(['buktiDp']);

        $this->assertSame('tersedia', $kamar->refresh()->status->value);

        // Dengan bukti DP: pengajuan terkirim, kamar ditahan tapi BELUM tersewa.
        $component->set('buktiDp', UploadedFile::fake()->image('dp.jpg'))
            ->call('konfirmasiSewa')
            ->assertSet('modalKamarId', null)
            ->assertSet('pesan', fn ($pesan) => str_contains($pesan, 'belum otomatis tersewa'));

        $kamar->refresh();
        $this->assertSame('terisi', $kamar->status->value);

        $penyewaan = Penyewaan::where('anak_kos_id', $user->id)
            ->where('kamar_id', $kamar->id)
            ->firstOrFail();
        $this->assertSame('menunggu_verifikasi', $penyewaan->status);
        $this->assertNull($penyewaan->ktp_path);
        // Sewa bulanan: DP Rp100.000 + sisa bulan pertama.
        $this->assertSame(2, $penyewaan->tagihans()->count());
        $dp = $penyewaan->tagihans()->where('jenis', 'dp')->firstOrFail();
        $sisa = $penyewaan->tagihans()->where('jenis', 'sewa')->firstOrFail();
        $this->assertEquals(100000, (float) $dp->jumlah);
        $this->assertEquals((float) $kamar->harga_sewa_bulanan, (float) $dp->jumlah + (float) $sisa->jumlah);

        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $dp->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $this->assertDatabaseHas('chat_pesans', [
            'properti_id' => $properti->id,
            'anak_kos_id' => $user->id,
            'pengirim_id' => $user->id,
        ]);
    }

    public function test_service_membuat_tagihan_sesuai_durasi(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $service = app(PenyewaanService::class);
        $penyewaan = $this->sewaBulananAktif($user, $kamar, 3);

        // Durasi 3 bulan: 1 DP + 3 tagihan sewa (bulan pertama = sisa setelah DP).
        // Setelah approval, DP lunas, 3 sewa belum bayar.
        $this->assertSame(4, $penyewaan->tagihans()->count());
        $this->assertSame(1, $penyewaan->tagihans()->where('jenis', 'dp')->count());
        $this->assertSame(3, $penyewaan->tagihans()->where('jenis', 'sewa')->count());
        $this->assertSame(3, $penyewaan->tagihans()->where('status', 'belum_bayar')->count());
        $this->assertSame(1, $penyewaan->tagihans()->where('status', 'lunas')->count());
        $periodes = $penyewaan->tagihans()->orderBy('id')->pluck('periode')->all();
        $bulan0 = today()->startOfMonth()->locale('id')->translatedFormat('F Y');
        $this->assertSame(
            [
                'DP - '.$bulan0,
                'Sisa '.$bulan0,
                today()->startOfMonth()->addMonthsNoOverflow(1)->locale('id')->translatedFormat('F Y'),
                today()->startOfMonth()->addMonthsNoOverflow(2)->locale('id')->translatedFormat('F Y'),
            ],
            $periodes,
        );
    }

    public function test_sewa_ditolak_saat_kamar_sudah_terisi(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'terisi')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);

        $component->call('pesanKamar', $kamar->id)
            ->assertSet('galat', 'Kamar tidak tersedia saat ini.')
            ->assertSet('modalKamarId', null);
    }

    public function test_tanggal_masuk_tidak_boleh_mundur(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);
        $component->call('pesanKamar', $kamar->id);
        $component->set('tanggalMasuk', today()->subMonth()->toDateString())
            ->call('konfirmasiSewa')
            ->assertHasErrors(['tanggalMasuk' => 'after_or_equal']);

        $kamar->refresh();
        $this->assertSame('tersedia', $kamar->status->value);
    }

    public function test_tanggal_masuk_maksimal_10_hari_ke_depan(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);
        $component->call('pesanKamar', $kamar->id);

        // 11 hari ke depan: ditolak.
        $component->set('tanggalMasuk', today()->addDays(11)->toDateString())
            ->call('konfirmasiSewa')
            ->assertHasErrors(['tanggalMasuk' => 'before_or_equal']);

        $kamar->refresh();
        $this->assertSame('tersedia', $kamar->status->value);

        // Tepat 10 hari ke depan: lolos validasi tanggal, tapi masih wajib bukti DP.
        $component->set('tanggalMasuk', today()->addDays(10)->toDateString())
            ->call('konfirmasiSewa')
            ->assertHasErrors(['buktiDp']);

        $component->set('buktiDp', UploadedFile::fake()->image('dp.jpg'))
            ->call('konfirmasiSewa')
            ->assertSet('modalKamarId', null)
            ->assertSet('pesan', fn ($pesan) => str_contains($pesan, 'Pengajuan booking'));
    }

    public function test_service_menolak_tanggal_masuk_lebih_dari_10_hari(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $service = app(PenyewaanService::class);

        try {
            $service->sewaKamar($user, $kamar, today()->addDays(11)->toDateString(), 1);
            $this->fail('Seharusnya menolak tanggal masuk lebih dari 10 hari ke depan.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('10 hari', $e->getMessage());
        }

        $kamar->refresh();
        $this->assertSame('tersedia', $kamar->status->value);
    }

    public function test_double_booking_kamar_yang_sama_ditolak(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $service = app(PenyewaanService::class);

        $sewaanPertama = $service->sewaKamar($user, $kamar, today()->toDateString(), 1, null, null, 'bukti-dp-test.jpg');
        $this->assertNotNull($sewaanPertama);

        $this->expectException(\DomainException::class);

        try {
            $service->sewaKamar($user, $kamar, today()->toDateString(), 1, null, null, 'bukti-dp-test.jpg');
        } finally {
            $this->assertSame(1, Penyewaan::where('kamar_id', $kamar->id)->count());
        }
    }

    public function test_pemilik_tidak_bisa_sewa_kamar_dari_detail(): void
    {
        $user = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();

        // Pemilik tidak melihat tombol sewa di halaman detail.
        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);
        $component->call('pesanKamar', $properti->kamars()->where('status', 'tersedia')->firstOrFail()->id)
            ->assertSet('modalKamarId', null)
            ->assertSet('galat', 'Hanya akun pencari kos (anak kos) yang dapat menyewa kamar.');
    }

    public function test_anak_kos_bisa_sewa_harian_dari_detail(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $kamar->update(['harga_sewa_harian' => 50000]);

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);
        $component->call('pesanKamar', $kamar->id);
        $component->set('tanggalMasuk', today()->toDateString())
            ->set('periodeSewa', 'harian')
            ->set('durasiHari', 3)
            ->call('konfirmasiSewa')
            ->assertSet('modalKamarId', null)
            ->assertSet('pesan', fn ($pesan) => str_contains($pesan, '3 hari'));

        $kamar->refresh();
        $this->assertSame('terisi', $kamar->status->value);

        $penyewaan = Penyewaan::where('anak_kos_id', $user->id)
            ->where('kamar_id', $kamar->id)
            ->firstOrFail();
        $this->assertSame('aktif', $penyewaan->status);
        $this->assertSame(1, $penyewaan->tagihans()->count());
        $tagihan = $penyewaan->tagihans()->first();
        $this->assertEquals(150000.0, (float) $tagihan->jumlah);
        $this->assertSame('belum_bayar', $tagihan->status);
    }

    public function test_service_sewa_harian_set_tanggal_keluar_dan_proses_tagihan_dilewati(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $kamar->update(['harga_sewa_harian' => 75000]);

        $service = app(PenyewaanService::class);
        $penyewaan = $service->sewaKamar($user, $kamar, today()->toDateString(), 1, 4);

        $this->assertSame('aktif', $penyewaan->status);
        $this->assertEquals(today()->addDays(3)->toDateString(), $penyewaan->tanggal_keluar->toDateString());
        $this->assertSame(1, $penyewaan->tagihans()->count());
        $this->assertEquals(300000.0, (float) $penyewaan->tagihans()->first()->jumlah);

        // ProsesTagihan tidak membuat tagihan bulanan tambahan untuk sewa harian.
        Artisan::call('app:proses-tagihan');
        $penyewaan->refresh();
        $this->assertSame(1, $penyewaan->tagihans()->count());
    }

    public function test_sewa_harian_ditolak_saat_kamar_tanpa_harga_harian(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $kamar->update(['harga_sewa_harian' => null]);

        $service = app(PenyewaanService::class);

        $this->expectException(\DomainException::class);
        $service->sewaKamar($user, $kamar, today()->toDateString(), 1, 2);
    }

    public function test_modal_bayar_tagihan_pertama_berlabel_dp(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $penyewaan = app(PenyewaanService::class)->sewaKamar($user, $kamar, today()->toDateString(), 1, null, null, 'bukti-dp-test.jpg');
        $tagihan = $penyewaan->tagihans()->where('jenis', 'dp')->firstOrFail();

        // Tanpa KTP: ktp_path null; booking masih pending sehingga belum bisa dibayar.
        $this->assertNull($penyewaan->ktp_path);
        $this->assertSame('menunggu_verifikasi', $penyewaan->status);

        Volt::actingAs($user)->test('pages.anak-kos.tagihan')
            ->call('bayarTagihan', $tagihan->id)
            ->assertSet('galat', fn ($galat) => str_contains($galat, 'belum diverifikasi'));

        // Setelah booking disetujui: booking fiks (dibooking) dan DP lunas.
        app(PenyewaanService::class)->setujuiBooking($penyewaan->refresh(), $properti->pemilik_id);
        $this->assertSame('dibooking', $penyewaan->refresh()->status);
        $this->assertSame('lunas', $tagihan->refresh()->status);

        // Simulasi data lama (sewa aktif dengan DP belum dibayar):
        // modal bayar tagihan DP tetap berlabel Uang Muka (DP).
        $tagihan->pembayarans()->delete();
        $tagihan->update(['status' => 'belum_bayar']);

        Volt::actingAs($user)->test('pages.anak-kos.tagihan')
            ->call('bayarTagihan', $tagihan->id)
            ->assertSee('Bayar Uang Muka (DP)');
    }

    public function test_klik_kamar_membuka_modal_detail_dengan_fasilitas(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();
        $kamar->update(['fasilitas' => 'AC, Kasur, Kamar Mandi Dalam']);

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);

        // Fasilitas ikut ter-query sehingga tampil di card kamar.
        $component->assertSee('Kasur');

        // Klik card (foto/nama/tombol Detail) membuka modal rincian.
        $component->call('bukaDetailKamar', $kamar->id)
            ->assertSet('detailKamarId', $kamar->id)
            ->assertSee('Spesifikasi tipe kamar')
            ->assertSee('Fasilitas kamar')
            ->assertSee('Kasur')
            ->assertSee('Harga sewa');

        $component->call('tutupDetailKamar')
            ->assertSet('detailKamarId', null);
    }
}
