<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class TambahPropertiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class]);
    }

    public function test_tambah_properti_dengan_foto(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);

        $component = Livewire::actingAs($pemilik)->test('pages.pemilik.properti-form');

        $component->set('nama', 'Kos Mawar')
            ->set('kota', 'Bandung')
            ->set('alamat', 'Jl. Mawar No. 1, Bandung')
            ->set('latitude', '-6.9175')
            ->set('longitude', '107.6191')
            ->set('fasilitasTerpilih', ['WiFi'])
            ->set('fotoBaru', UploadedFile::fake()->image('kos.jpg', 800, 600))
            ->call('simpan');

        $this->assertDatabaseHas('propertis', [
            'pemilik_id' => $pemilik->id,
            'nama' => 'Kos Mawar',
        ]);

        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();
        $this->assertNotNull($properti->foto);
    }

    public function test_pendaftar_baru_tanpa_trial_bisa_tambah_kos_pertama(): void
    {
        $pemilik = User::factory()->create();
        $pemilik->assignRole('pemilik');
        $pemilik = $pemilik->refresh();

        // Free murni tanpa klaim trial tetap boleh tambah kos pertama.
        $this->assertSame('free', \App\Services\SubscriptionService::getPlan($pemilik));
        $this->assertTrue(\App\Services\SubscriptionService::checkLimit($pemilik, 'property')['allowed']);

        Livewire::actingAs($pemilik)->test('pages.pemilik.properti-form')
            ->set('nama', 'Kos Pertama')
            ->set('kota', 'Bandung')
            ->set('alamat', 'Jl. Pertama No. 1, Bandung')
            ->set('latitude', '-6.9175')
            ->set('longitude', '107.6191')
            ->set('status', 'aktif')
            ->set('fasilitasTerpilih', ['WiFi'])
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('propertis', [
            'pemilik_id' => $pemilik->id,
            'nama' => 'Kos Pertama',
        ]);
    }

    public function test_widget_fasilitas_tanpa_alpine_toggle_tersimpan(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);

        $component = Livewire::actingAs($pemilik)->test('pages.pemilik.properti-form');

        // Widget harus memakai wire:model (bukan entangle Alpine) agar ceklis
        // tetap sinkron walau form di-render ulang.
        $component->assertSee('wire:model.live="fasilitasTerpilih"', false);

        $component->set('fasilitasTerpilih', ['WiFi', 'AC'])
            ->set('nama', 'Kos Fasilitas')
            ->set('kota', 'Bandung')
            ->set('alamat', 'Jl. Fasilitas No. 1, Bandung')
            ->set('latitude', '-6.9175')
            ->set('longitude', '107.6191')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('propertis', [
            'pemilik_id' => $pemilik->id,
            'fasilitas' => 'WiFi, AC',
        ]);
    }

    public function test_simpan_kos_dengan_foto_dan_galeri_tanpa_trial(): void
    {
        $pemilik = User::factory()->create();
        $pemilik->assignRole('pemilik');
        $pemilik = $pemilik->refresh();

        $this->assertSame('free', \App\Services\SubscriptionService::getPlan($pemilik));

        \Illuminate\Support\Facades\Storage::fake('public');

        Livewire::actingAs($pemilik)->test('pages.pemilik.properti-form')
            ->set('nama', 'Kos Repro Penuh')
            ->set('kota', 'Jakarta')
            ->set('alamat', 'Jl. Repro No. 1, Jakarta')
            ->set('latitude', '-6.2088')
            ->set('longitude', '106.8456')
            ->set('status', 'aktif')
            ->set('fotoBaru', UploadedFile::fake()->image('cover.jpg', 800, 600))
            ->set('galeriBaru', [
                UploadedFile::fake()->image('g1.jpg', 800, 600),
                UploadedFile::fake()->image('g2.jpg', 800, 600),
            ])
            ->set('fasilitasTerpilih', ['WiFi', 'AC'])
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('propertis', [
            'pemilik_id' => $pemilik->id,
            'nama' => 'Kos Repro Penuh',
        ]);
    }

    public function test_form_fasilitas_render_dan_centang_tersimpan(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);

        $component = Livewire::actingAs($pemilik)->test('pages.pemilik.properti-form');

        $component->assertViewHas('pilihPemilik', false)
            ->set('nama', 'Kos Mawar')
            ->set('kota', 'Bandung')
            ->set('alamat', 'Jl. Mawar No. 1, Bandung')
            ->set('latitude', '-6.9175')
            ->set('longitude', '107.6191')
            ->set('fasilitasTerpilih', ['WiFi', 'AC', 'Kamar Mandi Dalam'])
            ->call('simpan');

        $this->assertDatabaseHas('propertis', [
            'pemilik_id' => $pemilik->id,
            'fasilitas' => 'WiFi, AC, Kamar Mandi Dalam',
        ]);
    }

    public function test_form_fasilitas_grouped_options_tampil(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        $view = Livewire::actingAs($pemilik)
            ->test('pages.pemilik.properti-form')
            ->html();

        // Form kos hanya menampilkan fasilitas umum/bersama + peraturan kos
        // (fasilitas privat kamar pindah ke Kelola Kamar).
        $this->assertStringContainsString('Fasilitas &amp; Peraturan Kos', $view);
        $this->assertStringContainsString('Fasilitas Umum Kos', $view);
        $this->assertStringContainsString('Fasilitas Parkir Kos', $view);
        $this->assertStringContainsString('Peraturan Kos', $view);
        $this->assertStringContainsString('Dilarang Merokok', $view);
        $this->assertStringContainsString('images/fasilitas/wifi.png', $view);
        $this->assertStringNotContainsString('Fasilitas Kamar Mandi', $view);
    }

    public function test_form_kamar_hanya_tampil_fasilitas_kamar(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);
        $properti = Properti::create([
            'pemilik_id' => $pemilik->id,
            'nama' => 'Kos Uji Kamar',
            'kota' => 'Bandung',
            'fasilitas' => 'WiFi',
            'status' => 'aktif',
        ]);

        $view = Livewire::actingAs($pemilik)
            ->test('pages.pemilik.kamar', ['properti' => $properti])
            ->html();

        $this->assertStringContainsString('Fasilitas Kamar', $view);
        $this->assertStringContainsString('Fasilitas Kamar Mandi', $view);
        $this->assertStringNotContainsString('Fasilitas Umum Kos', $view);
    }

    public function test_form_kos_ada_pilihan_tipe_hunian(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);

        $view = Livewire::actingAs($pemilik)
            ->test('pages.pemilik.properti-form')
            ->html();

        $this->assertStringContainsString('Tipe Kos', $view);
        $this->assertStringContainsString('Kos Putra', $view);
        $this->assertStringContainsString('Kos Putri', $view);
        $this->assertStringContainsString('Kos Campur', $view);
        $this->assertStringContainsString('Kos Bebas', $view);
    }

    public function test_tipe_hunian_tersimpan_dari_form_kos(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);

        Livewire::actingAs($pemilik)->test('pages.pemilik.properti-form')
            ->set('nama', 'Kos Tipe Putri')
            ->set('kota', 'Bandung')
            ->set('alamat', 'Jl. Tipe No. 1, Bandung')
            ->set('latitude', '-6.9175')
            ->set('longitude', '107.6191')
            ->set('status', 'aktif')
            ->set('tipe_hunian', 'putri')
            ->set('fasilitasTerpilih', ['WiFi'])
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('propertis', [
            'pemilik_id' => $pemilik->id,
            'nama' => 'Kos Tipe Putri',
            'tipe_hunian' => 'putri',
        ]);
    }
}
