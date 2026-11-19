<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\PrediksiHarga;
use App\Models\Properti;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SpkSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SaranHargaKamarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class, SpkSeeder::class]);
        $this->withoutVite();
    }

    /**
     * Pemilik diberi paket BUSINESS agar batas jumlah kamar paket free
     * (1 kamar) tidak menghalangi pengujian pembuatan kamar baru.
     */
    private function aktifkanPro(User $pemilik): void
    {
        Subscription::create([
            'user_id' => $pemilik->id,
            'plan' => 'business',
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);
    }

    public function test_pemilik_mendapat_estimasi_harga_wajar_di_form_kamar(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $this->aktifkanPro($pemilik);
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();

        $this->actingAs($pemilik);

        $komponen = Volt::test('pages.pemilik.kamar', ['properti' => $properti])
            ->set('ukuran', '3x4')
            ->set('harga', '1200000')
            ->set('fasilitasKamarTerpilih', ['WiFi', 'Kasur'])
            ->call('hitungSaranHarga')
            ->assertHasNoErrors();

        $komponen->assertSet('estimasiHargaWajar', function ($hasil) {
            return is_array($hasil)
                && isset($hasil['harga_prediksi'])
                && $hasil['harga_prediksi'] > 0
                && in_array($hasil['label_kewajaran'], ['di_bawah_pasar', 'wajar', 'di_atas_pasar'], true);
        });
    }

    public function test_terapkan_saran_harga_mengisi_kolom_harga(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $this->aktifkanPro($pemilik);
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();

        $this->actingAs($pemilik);

        $komponen = Volt::test('pages.pemilik.kamar', ['properti' => $properti])
            ->set('ukuran', '3x4')
            ->set('fasilitasKamarTerpilih', ['WiFi'])
            ->call('hitungSaranHarga')
            ->call('terapkanSaranHarga')
            ->assertHasNoErrors();

        $harga = (string) $komponen->get('harga');
        $this->assertNotSame('', $harga);
        $this->assertGreaterThan(0, (float) $harga);
    }

    public function test_simpan_kamar_merekam_riwayat_prediksi_harga(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $this->aktifkanPro($pemilik);
        $properti = Properti::where('pemilik_id', $pemilik->id)->firstOrFail();

        $this->actingAs($pemilik);

        $komponen = Volt::test('pages.pemilik.kamar', ['properti' => $properti])
            ->set('nama', 'Kamar Prediksi')
            ->set('kapasitas', 1)
            ->set('ukuran', '3x4')
            ->set('harga', '1200000')
            ->set('fasilitasKamarTerpilih', ['WiFi', 'Kasur'])
            ->set('status', 'tersedia')
            ->call('simpanKamar')
            ->assertHasNoErrors();

        $komponen->assertSet('galat', null);

        $kamar = Kamar::where('nama', 'Kamar Prediksi')->firstOrFail();

        $this->assertDatabaseHas('prediksi_harga', [
            'kamar_id' => $kamar->id,
        ]);

        $prediksi = PrediksiHarga::where('kamar_id', $kamar->id)->latest('id')->first();
        $this->assertNotNull($prediksi);
        $this->assertGreaterThan(0, (float) $prediksi->harga_prediksi);
        $this->assertContains($prediksi->label_kewajaran, ['di_bawah_pasar', 'wajar', 'di_atas_pasar']);
        $this->assertNotNull($prediksi->model_versi_id);
    }
}
