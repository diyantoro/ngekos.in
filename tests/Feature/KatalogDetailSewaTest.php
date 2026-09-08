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
use Illuminate\Support\Facades\Artisan;
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

        $component->set('tanggalMasuk', today()->toDateString())
            ->call('konfirmasiSewa')
            ->assertSet('modalKamarId', null)
            ->assertSet('pesan', fn ($pesan) => str_contains($pesan, 'berhasil dipesan'));

        $kamar->refresh();
        $this->assertSame('terisi', $kamar->status);

        $penyewaan = Penyewaan::where('anak_kos_id', $user->id)
            ->where('kamar_id', $kamar->id)
            ->firstOrFail();
        $this->assertSame('aktif', $penyewaan->status);
        $this->assertSame(1, $penyewaan->tagihans()->count());
        $tagihan = $penyewaan->tagihans()->first();
        $this->assertSame('belum_bayar', $tagihan->status);
        $this->assertEquals((float) $kamar->harga_sewa_bulanan, (float) $tagihan->jumlah);

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
        $penyewaan = $service->sewaKamar($user, $kamar, today()->toDateString(), 3);

        $this->assertSame(3, $penyewaan->tagihans()->count());
        $this->assertSame(3, $penyewaan->tagihans()->where('status', 'belum_bayar')->count());
        $periodes = $penyewaan->tagihans()->pluck('periode')->all();
        $this->assertSame(
            [
                today()->startOfMonth()->translatedFormat('F Y'),
                today()->startOfMonth()->addMonthsNoOverflow(1)->translatedFormat('F Y'),
                today()->startOfMonth()->addMonthsNoOverflow(2)->translatedFormat('F Y'),
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
        $this->assertSame('tersedia', $kamar->status);
    }

    public function test_double_booking_kamar_yang_sama_ditolak(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        $service = app(PenyewaanService::class);

        $sewaanPertama = $service->sewaKamar($user, $kamar, today()->toDateString());
        $this->assertNotNull($sewaanPertama);

        $this->expectException(\DomainException::class);

        try {
            $service->sewaKamar($user, $kamar, today()->toDateString());
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
        $this->assertSame('terisi', $kamar->status);

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
}
