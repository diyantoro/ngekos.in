<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kriteria;
use App\Models\Properti;
use App\Models\TitikAcuan;
use App\Models\User;
use App\Services\MlModelService;
use App\Services\SpkTopsisService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SpkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpkTopsisDanHargaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class, SpkSeeder::class]);
    }

    public function test_kriteria_dan_titik_acuan_tersedia(): void
    {
        $this->assertGreaterThanOrEqual(5, Kriteria::count());
        $this->assertNotNull(Kriteria::where('kode', 'C1')->first());
        $this->assertNotNull(Kriteria::where('kode', 'C2')->first());
        $this->assertNotNull(TitikAcuan::first());
    }

    public function test_kamar_hitung_luas_m2(): void
    {
        $kamar1 = new Kamar(['ukuran' => '3x4']);
        $this->assertEquals(12.0, $kamar1->hitungLuasM2());

        $kamar2 = new Kamar(['ukuran' => '3.5 x 4']);
        $this->assertEquals(14.0, $kamar2->hitungLuasM2());

        $kamar3 = new Kamar(['ukuran' => '16 m2']);
        $this->assertEquals(16.0, $kamar3->hitungLuasM2());

        $kamarDefault = new Kamar(['ukuran' => null]);
        $this->assertEquals(12.0, $kamarDefault->hitungLuasM2());
    }

    public function test_ml_model_service_fallback_estimator(): void
    {
        $service = new MlModelService;
        $res = $service->fallbackEstimator([
            'luas_m2' => 12.0,
            'jarak_km' => 1.0,
            'kawasan' => 'Coblong',
            'fasilitas' => ['WiFi', 'AC', 'Kamar Mandi Dalam'],
            'harga_aktual' => 1800000,
        ]);

        $this->assertArrayHasKey('harga_prediksi', $res);
        $this->assertGreaterThan(1000000, $res['harga_prediksi']);
        $this->assertArrayHasKey('label_kewajaran', $res);
        $this->assertContains($res['label_kewajaran'], ['di_bawah_pasar', 'wajar', 'di_atas_pasar']);
    }

    public function test_spk_topsis_ranking(): void
    {
        $titik = TitikAcuan::first();
        $this->assertNotNull($titik);

        // Ambil atau buat kamar simulasi
        $kamars = Kamar::with('properti')->whereNotNull('harga_sewa_bulanan')->take(5)->get();
        if ($kamars->isEmpty()) {
            $user = User::factory()->create();
            $prop = Properti::create([
                'pemilik_id' => $user->id,
                'nama' => 'Kos Uji TOPSIS',
                'latitude' => $titik->latitude + 0.005,
                'longitude' => $titik->longitude + 0.005,
                'status' => 'aktif',
            ]);
            $kamars = collect([
                Kamar::create([
                    'properti_id' => $prop->id,
                    'nama' => 'Kamar A (Murah Dekat)',
                    'harga_sewa_bulanan' => 800000,
                    'ukuran' => '3x3',
                    'fasilitas' => 'WiFi, Kasur',
                    'status' => 'tersedia',
                ]),
                Kamar::create([
                    'properti_id' => $prop->id,
                    'nama' => 'Kamar B (Mahal Lengkap)',
                    'harga_sewa_bulanan' => 2000000,
                    'ukuran' => '4x5',
                    'fasilitas' => 'WiFi, AC, Kamar Mandi Dalam, Kasur, Lemari',
                    'status' => 'tersedia',
                ]),
            ]);
        }

        $service = app(SpkTopsisService::class);
        $ranking = $service->rank($kamars, $titik);

        $this->assertArrayHasKey('hasil', $ranking);
        $this->assertArrayHasKey('sensitivitas', $ranking);
        $this->assertNotEmpty($ranking['hasil']);

        // Verifikasi hasil berurutan dari peringkat 1
        $this->assertEquals(1, $ranking['hasil'][0]['peringkat']);
        $this->assertGreaterThanOrEqual(0, $ranking['hasil'][0]['skor']);
        $this->assertLessThanOrEqual(1, $ranking['hasil'][0]['skor']);
    }
}
