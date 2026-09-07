<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PengeluaranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_pemilik_lihat_halaman_pengeluaran(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        $this->actingAs($pemilik)
            ->get(route('pemilik.pengeluaran'))
            ->assertOk()
            ->assertSeeVolt('pages.pemilik.pengeluaran');
    }

    public function test_pemilik_tambah_pengeluaran_via_livewire(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();

        Volt::actingAs($pemilik)
            ->test('pages.pemilik.pengeluaran')
            ->set('properti_id', $properti->id)
            ->set('kategori', 'listrik')
            ->set('keterangan', 'Tagihan listrik September')
            ->set('jumlah', '275000')
            ->set('tanggal', today()->toDateString())
            ->call('simpanPengeluaran')
            ->assertSet('pesan', 'Pengeluaran berhasil dicatat.');

        $this->assertDatabaseHas('pengeluarans', [
            'properti_id' => $properti->id,
            'kategori' => 'listrik',
            'jumlah' => '275000',
        ]);
    }

    public function test_pemilik_tidak_bisa_mengatur_pengeluaran_properti_orang_lain(): void
    {
        $pemilik1 = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $pemilik2 = User::where('email', 'pemilik2@ngekos.test')->firstOrFail();
        $propertiPemilik2 = Properti::where('pemilik_id', $pemilik2->id)->firstOrFail();

        Volt::actingAs($pemilik1)
            ->test('pages.pemilik.pengeluaran')
            ->set('properti_id', $propertiPemilik2->id)
            ->set('kategori', 'listrik')
            ->set('keterangan', 'Dicoba dari akun lain')
            ->set('jumlah', '100000')
            ->set('tanggal', today()->toDateString())
            ->call('simpanPengeluaran')
            ->assertHasErrors('properti_id');

        $this->assertDatabaseMissing('pengeluarans', [
            'properti_id' => $propertiPemilik2->id,
            'kategori' => 'listrik',
            'keterangan' => 'Dicoba dari akun lain',
        ]);
    }

    public function test_api_pemilik_tambah_pengeluaran(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();

        Sanctum::actingAs($pemilik, ['*']);

        $this->postJson('/api/pemilik/pengeluaran', [
            'properti_id' => $properti->id,
            'kategori' => 'maintenance',
            'keterangan' => 'Servis AC',
            'jumlah' => 175000,
            'tanggal' => today()->toDateString(),
        ])->assertCreated()->assertJsonPath('message', 'Pengeluaran berhasil dicatat.');

        $this->assertDatabaseHas('pengeluarans', [
            'properti_id' => $properti->id,
            'kategori' => 'maintenance',
            'jumlah' => '175000.00',
        ]);
    }

    public function test_api_pemilik_tidak_bisa_tambah_pengeluaran_properti_orang_lain(): void
    {
        $pemilik1 = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $pemilik2 = User::where('email', 'pemilik2@ngekos.test')->firstOrFail();
        $propertiPemilik2 = Properti::where('pemilik_id', $pemilik2->id)->firstOrFail();

        Sanctum::actingAs($pemilik1, ['*']);

        $this->postJson('/api/pemilik/pengeluaran', [
            'properti_id' => $propertiPemilik2->id,
            'kategori' => 'listrik',
            'jumlah' => 100000,
            'tanggal' => today()->toDateString(),
        ])->assertNotFound();
    }

    public function test_api_anak_kos_tidak_bisa_akses_pengeluaran(): void
    {
        $anakKos = User::where('email', 'anak1@ngekos.test')->firstOrFail();

        Sanctum::actingAs($anakKos, ['*']);

        $this->getJson('/api/pemilik/pengeluaran')->assertForbidden();
    }
}