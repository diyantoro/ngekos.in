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
}
