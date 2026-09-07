<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->assertDatabaseHas('penyewaans', [
            'anak_kos_id' => $user->id,
            'kamar_id' => $kamar->id,
            'status' => 'aktif',
        ]);
        $this->assertDatabaseHas('chat_pesans', [
            'properti_id' => $properti->id,
            'anak_kos_id' => $user->id,
            'pengirim_id' => $user->id,
        ]);
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

        $service = app(\App\Services\PenyewaanService::class);

        $sewaanPertama = $service->sewaKamar($user, $kamar, today()->toDateString());
        $this->assertNotNull($sewaanPertama);

        $this->expectException(\DomainException::class);

        try {
            $service->sewaKamar($user, $kamar, today()->toDateString());
        } finally {
            $this->assertSame(1, \App\Models\Penyewaan::where('kamar_id', $kamar->id)->count());
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
}