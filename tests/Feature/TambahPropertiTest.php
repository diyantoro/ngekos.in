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

    public function test_form_fasilitas_render_dan_centang_tersimpan(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

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
