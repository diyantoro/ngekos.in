<?php

namespace Tests\Feature;

use App\Models\DataLatih;
use App\Models\ModelVersi;
use App\Models\TitikAcuan;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SpkSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SpkHalamanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class, SpkSeeder::class]);
        $this->withoutVite();
    }

    public function test_halaman_rekomendasi_dapat_dijalankan_pencarian(): void
    {
        $titik = TitikAcuan::firstOrFail();

        $komponen = Volt::test('pages.spk.rekomendasi')
            ->set('titikAcuanId', $titik->id)
            ->set('bobotC1', 30)
            ->set('bobotC2', 20)
            ->set('bobotC3', 30)
            ->set('bobotC4', 10)
            ->set('bobotC5', 10)
            ->call('cariDanPeringkatkan');

        $komponen->assertHasNoErrors();
        $komponen->assertSet('hasilPeringkat', fn ($hasil) => is_array($hasil));
        $komponen->assertSet('sensitivitas', fn ($s) => is_array($s) && count($s) === 5);
    }

    public function test_admin_dapat_melihat_dan_menandai_outlier_data_latih(): void
    {
        $admin = User::where('email', 'superadmin.ngekos@gmail.com')->firstOrFail();
        $data = DataLatih::firstOrFail();

        $this->actingAs($admin);

        Volt::test('pages.super-admin.data-latih')
            ->assertSee('Manajemen Data Latih')
            ->call('toggleOutlier', $data->id)
            ->assertHasNoErrors();

        $this->assertTrue($data->refresh()->ditandai_outlier);
    }

    public function test_admin_dapat_menambah_data_latih_baru(): void
    {
        $admin = User::where('email', 'superadmin.ngekos@gmail.com')->firstOrFail();
        $sebelum = DataLatih::count();

        $this->actingAs($admin);

        Volt::test('pages.super-admin.data-latih')
            ->set('luasM2', 14.0)
            ->set('jarakKm', 1.2)
            ->set('kawasan', 'Coblong')
            ->set('tipePenghuni', 'Putri')
            ->set('hargaAktual', 1500000)
            ->call('tambahData')
            ->assertHasNoErrors();

        $this->assertSame($sebelum + 1, DataLatih::count());
        $this->assertDatabaseHas('data_latih', [
            'kawasan' => 'Coblong',
            'tipe_penghuni' => 'Putri',
        ]);
    }

    public function test_admin_dapat_mengaktifkan_versi_model(): void
    {
        $admin = User::where('email', 'superadmin.ngekos@gmail.com')->firstOrFail();

        $lama = ModelVersi::firstOrFail();

        $baru = ModelVersi::create([
            'versi' => 'v9.0-uji',
            'algoritma' => 'Multilayer Perceptron (MLP)',
            'mae' => 70000,
            'rmse' => 90000,
            'r2' => 0.9300,
            'mape' => 6.5,
            'status' => 'arsip',
            'dilatih_pada' => now(),
        ]);

        $this->actingAs($admin);

        Volt::test('pages.super-admin.model-ai')
            ->call('aktifkanVersi', $baru->id)
            ->assertHasNoErrors();

        $this->assertSame('aktif', $baru->refresh()->status);
        $this->assertSame('arsip', $lama->refresh()->status);
    }
}
