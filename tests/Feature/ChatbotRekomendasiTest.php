<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Properti;
use App\Models\User;
use App\Services\RekomendasiKosService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ChatbotRekomendasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_mengenali_niat_rekomendasi_dan_bukan_pertanyaan_panduan(): void
    {
        $service = app(RekomendasiKosService::class);

        $this->assertTrue($service->cocok('ada kos di bawah 1,5 juta di Bandung?'));
        $this->assertTrue($service->cocok('rekomendasi kos putri budget 800rb'));
        $this->assertTrue($service->cocok('dekat kampus UNSRI'));
        $this->assertTrue($service->cocok('Kota mana saja?'));
        $this->assertTrue($service->cocok('Dekat kampus mana?'));
        $this->assertTrue($service->cocok('di budget 700000', ['kota' => 'Palembang']));
        $this->assertFalse($service->cocok('cara cari kos'));
        $this->assertFalse($service->cocok('cara bayar tagihan kos'));
        $this->assertFalse($service->cocok('cara cari kos dekat kampus'));
    }

    public function test_rekomendasi_hanya_menampilkan_kamar_tersedia_sesuai_budget(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        $murah = $this->buatProperti($pemilik, 'Kos Uji Murah', 'Bandung', 900000);
        $mahal = $this->buatProperti($pemilik, 'Kos Uji Mahal', 'Bandung', 2500000);

        $hasil = app(RekomendasiKosService::class)->cari('ada kos di bawah 1 juta di Bandung');

        $this->assertStringContainsString('Kos Uji Murah', $hasil['teks']);
        $this->assertStringNotContainsString('Kos Uji Mahal', $hasil['teks']);
        $this->assertNotEmpty($hasil['tautan']);
        $this->assertContains(route('kos.detail', $murah), array_column($hasil['tautan'], 'url'));
        $this->assertNotContains(route('kos.detail', $mahal), array_column($hasil['tautan'], 'url'));
    }

    public function test_chatbot_menjawab_rekomendasi_dari_database(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $anak = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $this->buatProperti($pemilik, 'Kos Uji Chatbot', 'Bandung', 750000);

        Volt::actingAs($anak)
            ->test('chatbot')
            ->set('input', 'ada kos di bawah 1 juta di Bandung')
            ->call('kirim')
            ->assertHasNoErrors()
            ->assertSee('Kos Uji Chatbot');
    }

    public function test_chatbot_tampil_di_dashboard_anak_kos_dan_pemilik(): void
    {
        $anak = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        $this->actingAs($anak)
            ->get(route('dashboard.anak-kos'))
            ->assertOk()
            ->assertSee('Buka chatbot bantuan');

        $this->actingAs($pemilik)
            ->get(route('dashboard.pemilik'))
            ->assertOk()
            ->assertSee('Buka chatbot bantuan');
    }

    public function test_komponen_spesifikasi_menampilkan_accordion_lengkap(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        $properti = $this->buatProperti($pemilik, 'Kos Uji Spesifikasi', 'Bandung', 900000, [
            'aturan' => "Tamu boleh menginap\nDilarang merokok",
        ]);

        $kamar = Kamar::where('properti_id', $properti->id)->firstOrFail();
        $kamar->update([
            'ukuran' => '3.1 x 3.4 meter',
            'termasuk_listrik' => false,
            'deposit' => 500000,
            'peraturan' => 'Maks. 2 orang/kamar',
            'fasilitas' => 'AC, Kasur, Shower, Ember',
        ]);

        $html = Blade::render(
            '<x-spesifikasi-kamar :kamar="$kamar" :properti="$properti" />',
            ['kamar' => $kamar->fresh(), 'properti' => $properti]
        );

        $this->assertStringContainsString('Spesifikasi tipe kamar', $html);
        $this->assertStringContainsString('3.1 x 3.4 meter', $html);
        $this->assertStringContainsString('Tidak Termasuk Listrik', $html);
        $this->assertStringContainsString('Fasilitas kamar', $html);
        $this->assertStringContainsString('Fasilitas kamar mandi', $html);
        $this->assertStringContainsString('Shower', $html);
        $this->assertStringContainsString('Peraturan khusus tipe kamar ini', $html);
        $this->assertStringContainsString('Maks. 2 orang/kamar', $html);
        $this->assertStringContainsString('Tamu boleh menginap', $html);
        $this->assertStringContainsString('Rp500.000', $html);
    }

    public function test_chatbot_memahami_kampus_dan_budget_lanjutan(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $anak = User::where('email', 'anak1@ngekos.test')->firstOrFail();

        $this->buatProperti($pemilik, 'Kos Uji Bukit Murah', 'Palembang', 700000, [
            'alamat' => 'Bukit Besar, Ilir Barat I',
        ]);
        $this->buatProperti($pemilik, 'Kos Uji Bukit Mahal', 'Palembang', 2000000, [
            'alamat' => 'Bukit Besar, Ilir Barat I',
        ]);
        $this->buatProperti($pemilik, 'Kos Uji Sako Jauh', 'Palembang', 500000, [
            'alamat' => 'Sako, Kenten',
        ]);

        $komponen = Volt::actingAs($anak)
            ->test('chatbot')
            ->set('input', 'halo kak, ada kos di kota Palembang yang dekat kampus UNSRI?')
            ->call('kirim')
            ->assertHasNoErrors();

        $percakapan = $komponen->get('percakapan');
        $jawabanPertama = end($percakapan)['teks'];

        $this->assertStringContainsString('Universitas Sriwijaya', $jawabanPertama);
        $this->assertStringContainsString('Kos Uji Bukit Murah', $jawabanPertama);
        $this->assertStringContainsString('Kos Uji Bukit Mahal', $jawabanPertama);
        $this->assertStringNotContainsString('Kos Uji Sako Jauh', $jawabanPertama);

        // Pertanyaan lanjutan hanya menyebut budget — konteks kampus/kota tetap dipakai.
        $komponen->set('input', 'di budget 700000')->call('kirim')->assertHasNoErrors();

        $percakapan = $komponen->get('percakapan');
        $jawabanKedua = end($percakapan)['teks'];

        $this->assertStringContainsString('Kos Uji Bukit Murah', $jawabanKedua);
        $this->assertStringNotContainsString('Kos Uji Bukit Mahal', $jawabanKedua);
        $this->assertStringNotContainsString('Kos Uji Sako Jauh', $jawabanKedua);
        $this->assertEquals(700000, $komponen->get('konteks')['budget']);
    }

    public function test_variasi_budget_dikenali_dan_kota_kosong_tidak_bocor(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $this->buatProperti($pemilik, 'Kos Uji Budget Variasi', 'Palembang', 700000, [
            'alamat' => 'Bukit Besar, Ilir Barat I',
        ]);

        $service = app(RekomendasiKosService::class);

        foreach ([
            'kos di palembang budget 700rb',
            'kos di palembang budget Rp 700.000',
            'kos di palembang budget perbulannya 700000',
            'kos di palembang maks 700 k',
            'kos di palembang budget 700',
        ] as $pertanyaan) {
            $hasil = $service->cari($pertanyaan);

            $this->assertStringContainsString('Kos Uji Budget Variasi', $hasil['teks'], $pertanyaan);
            $this->assertEquals(700000, $hasil['konteks']['budget'], $pertanyaan);
        }

        $this->buatProperti($pemilik, 'Kos Uji Bandung Lain', 'Bandung', 750000);

        $kosong = $service->cari('kos di Palembang budget 500rb');
        $this->assertStringContainsString('Belum ada kos', $kosong['teks']);
        $this->assertStringNotContainsString('Kos Uji Bandung Lain', $kosong['teks']);
    }

    public function test_ringkasan_kota_kampus_area_dan_fallback_kampus(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $this->buatProperti($pemilik, 'Kos Uji Ringkasan', 'Palembang', 900000, [
            'alamat' => 'Bukit Besar, Ilir Barat I',
        ]);

        $service = app(RekomendasiKosService::class);

        $kota = $service->cari('Kota mana saja?');
        $this->assertStringContainsString('Palembang', $kota['teks']);
        $this->assertStringContainsString('Bandung', $kota['teks']);

        $kampus = $service->cari('Dekat kampus mana?');
        $this->assertStringContainsString('Universitas Sriwijaya', $kampus['teks']);
        $this->assertStringContainsString('Palembang', $kampus['teks']);

        $area = $service->cari('Kos di Bandung dekat mana?');
        $this->assertStringContainsString('Kos di Bandung', $area['teks']);
        $this->assertSame('Bandung', $area['konteks']['kota']);

        // Alamat di luar area kampus: jangan mengaku "dekat kampus",
        // tetap tampilkan kos kota yang sama secara jujur.
        $this->buatProperti($pemilik, 'Kos Uji Sako Fallback', 'Palembang', 500000, [
            'alamat' => 'Sako, Kenten',
        ]);

        $fallback = $service->cari('kos di Palembang dekat kampus UNSRI budget 700rb');

        $this->assertStringContainsString('Belum ada kos yang cocok', $fallback['teks']);
        $this->assertStringContainsString('Kos Uji Sako Fallback', $fallback['teks']);
        $this->assertStringNotContainsString('di sekitar Kampus', $fallback['teks']);
    }

    /**
     * @param  array<string, mixed>  $tambahan
     */
    private function buatProperti(User $pemilik, string $nama, string $kota, float $harga, array $tambahan = []): Properti
    {
        $properti = Properti::create(array_merge([
            'pemilik_id' => $pemilik->id,
            'nama' => $nama,
            'kota' => $kota,
            'alamat' => 'Jl. Uji No. 1',
            'status' => 'aktif',
            'tipe_hunian' => 'campur',
        ], $tambahan));

        Kamar::create([
            'properti_id' => $properti->id,
            'nama' => 'A1',
            'kapasitas' => 2,
            'harga_sewa_bulanan' => $harga,
            'status' => 'tersedia',
        ]);

        return $properti;
    }
}
