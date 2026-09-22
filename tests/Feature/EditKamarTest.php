<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Properti;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditKamarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_pemilik_bisa_edit_kamar_yang_sudah_ada(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();
        $kamar = $properti->kamars()->firstOrFail();

        $component = Livewire::actingAs($pemilik)->test('pages.pemilik.kamar', ['properti' => $properti]);

        $component->call('editKamar', $kamar->id)
            ->assertSet('mode', 'ubah')
            ->assertSet('kamarId', $kamar->id)
            ->assertSet('nama', $kamar->nama)
            ->assertSet('kapasitas', (int) $kamar->kapasitas)
            ->assertSet('harga', $kamar->harga_sewa_bulanan)
            ->assertSet('harga_harian', $kamar->harga_sewa_harian)
            ->assertSet('status', $kamar->status);

        $component->set('nama', 'Kamar A1 Revisi')
            ->set('harga', '1200000')
            ->set('harga_harian', '45000')
            ->call('simpanKamar');

        $kamar->refresh();
        $this->assertSame('Kamar A1 Revisi', $kamar->nama);
        $this->assertEquals('1200000.00', $kamar->harga_sewa_bulanan);
        $this->assertEquals('45000.00', $kamar->harga_sewa_harian);
        $this->assertSame('buat', $component->get('mode'));
    }

    public function test_halaman_route_membuka_via_http_dan_menampilkan_tombol_ubah(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();

        $this->actingAs($pemilik)
            ->get(route('pemilik.kamar', $properti->id))
            ->assertOk();
    }

    public function test_ubah_muncul_di_form_dan_kamar_baru_bisa_langsung_diedit(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();

        // Tambah kamar baru via komponen.
        $component = Livewire::actingAs($pemilik)->test('pages.pemilik.kamar', ['properti' => $properti]);
        $component->set('nama', 'Kamar B2')
            ->set('kapasitas', 2)
            ->set('harga', '1500000')
            ->call('simpanKamar')
            ->assertSet('pesan', 'Kamar Kamar B2 berhasil ditambahkan.');

        $kamarBaru = $properti->kamars()->where('nama', 'Kamar B2')->firstOrFail();

        // Edit kamar yang baru ditambahkan tadi.
        $component->call('editKamar', $kamarBaru->id)
            ->assertSet('mode', 'ubah')
            ->assertSet('kamarId', $kamarBaru->id);

        $component->set('nama', 'Kamar B2 Plus')
            ->set('harga', '2000000')
            ->call('simpanKamar')
            ->assertSet('pesan', 'Kamar Kamar B2 Plus berhasil diperbarui.');

        $kamarBaru->refresh();
        $this->assertSame('Kamar B2 Plus', $kamarBaru->nama);
        $this->assertEquals('2000000.00', $kamarBaru->harga_sewa_bulanan);
    }
}