<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regresi batas kuota kamar: trial membuka fitur PRO, bukan kuota.
 * Pemilik seeded (5 kamar, batas FREE 1) tetap ditolak tambah kamar
 * meski trial aktif; kamar tidak tersimpan dan pesan sukses null.
 */
class KamarLimitRegresiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_tambah_kamar_ditolak_bila_kuota_free_tercapai_meski_trial_aktif(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        SubscriptionService::mulaiTrialFree($pemilik);
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();

        // 5 kamar seeded > batas FREE (1); trial tidak menghapus batas kuota.
        $this->assertSame(5, SubscriptionService::usage($pemilik, 'room'));
        $this->assertFalse(SubscriptionService::checkLimit($pemilik, 'room')['allowed']);

        $component = Livewire::actingAs($pemilik)->test('pages.pemilik.kamar', ['properti' => $properti]);
        $component->set('nama', 'Kamar B2')
            ->set('kapasitas', 2)
            ->set('harga', '1500000')
            ->call('simpanKamar')
            ->assertSet('pesan', null);

        $this->assertNotNull($component->get('galat'));
        $this->assertDatabaseMissing('kamars', [
            'properti_id' => $properti->id,
            'nama' => 'Kamar B2',
        ]);
    }

    public function test_tambah_kamar_diizinkan_dalam_kuota_free(): void
    {
        $pemilik = User::factory()->create();
        $pemilik->assignRole('pemilik');
        $pemilik = $pemilik->refresh();
        SubscriptionService::mulaiTrialFree($pemilik);
        $properti = Properti::create([
            'pemilik_id' => $pemilik->id,
            'nama' => 'Kos Uji Kuota',
            'status' => 'aktif',
        ]);

        Livewire::actingAs($pemilik)->test('pages.pemilik.kamar', ['properti' => $properti])
            ->set('nama', 'Kamar B2')
            ->set('kapasitas', 2)
            ->set('harga', '1500000')
            ->call('simpanKamar')
            ->assertSet('pesan', 'Kamar Kamar B2 berhasil ditambahkan.');

        $this->assertDatabaseHas('kamars', [
            'properti_id' => $properti->id,
            'nama' => 'Kamar B2',
        ]);
    }
}
