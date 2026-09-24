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
            ->set('status', 'aktif')
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

        $this->assertStringContainsString('Fasilitas Kamar', $view);
        $this->assertStringContainsString('Fasilitas Kamar Mandi', $view);
        $this->assertStringContainsString('Fasilitas Parkir', $view);
        $this->assertStringContainsString('Fasilitas Umum', $view);
        $this->assertStringContainsString('Peraturan Khusus', $view);
        $this->assertStringContainsString('images/fasilitas/ac.png', $view);
    }
}
