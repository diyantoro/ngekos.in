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
        $this->assertFalse($service->cocok('cara cari kos'));
        $this->assertFalse($service->cocok('cara bayar tagihan kos'));
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
