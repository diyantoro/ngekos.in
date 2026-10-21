<?php

namespace Tests\Feature;

use App\Models\ChatPesan;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\User;
use App\Services\BuktiStorage;
use App\Services\KwitansiService;
use App\Services\PembayaranService;
use App\Services\SubscriptionService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Volt\Volt;
use Tests\TestCase;

class FiturBaruTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    // ---------- 1. Booking tanpa KTP, konfirmasi via DP ----------

    public function test_api_sewa_berhasil_tanpa_ktp(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        Sanctum::actingAs($anak, ['*']);

        // Tanpa bukti transfer DP: booking bulanan ditolak.
        $this->postJson("/api/kos/{$properti->id}/kamar/{$kamar->id}/sewa", [
            'tanggal_masuk' => today()->toDateString(),
            'durasi_bulan' => 1,
        ])->assertStatus(422);

        // Dengan bukti DP: booking tercatat tapi BELUM otomatis tersewa.
        $this->post("/api/kos/{$properti->id}/kamar/{$kamar->id}/sewa", [
            'tanggal_masuk' => today()->toDateString(),
            'durasi_bulan' => 1,
            'bukti_dp' => UploadedFile::fake()->image('dp.jpg'),
        ])->assertCreated();

        // Kamar ditahan untuk pemesan, tapi sewa menunggu verifikasi.
        $this->assertSame('terisi', $kamar->refresh()->status->value);

        $sewa = Penyewaan::where('anak_kos_id', $anak->id)->where('kamar_id', $kamar->id)->firstOrFail();
        $this->assertSame('menunggu_verifikasi', $sewa->status);
        $this->assertSame(2, $sewa->tagihans()->count());
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $sewa->tagihans()->where('jenis', 'dp')->firstOrFail()->id,
            'jumlah' => 100000,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    // ---------- 2. Kwitansi otomatis ----------

    public function test_verifikasi_membuat_kwitansi_pdf_dan_chat(): void
    {
        Storage::fake('public');
        // Kwitansi wajib di disk privat (kontrak BuktiStorage); samakan fake.
        Storage::fake(BuktiStorage::DISK);

        $pemilik = $this->user('pemilik1@ngekos.test');
        $pembayaran = Pembayaran::where('status', 'menunggu_verifikasi')->firstOrFail();
        $pembayaran->tagihan->update(['jatuh_tempo' => today()->toDateString(), 'denda' => 0]);

        $hasil = PembayaranService::verifikasi($pembayaran, $pemilik->id, 'diverifikasi');

        $this->assertTrue($hasil['tagihan_lunas']);
        $this->assertNotNull($hasil['pembayaran']->nomor_kwitansi);
        $this->assertMatchesRegularExpression('/^KWT-\d{6}-\d{4}$/', $hasil['pembayaran']->nomor_kwitansi);
        $this->assertNotNull($hasil['kwitansi_url']);
        Storage::disk(BuktiStorage::DISK)->assertExists($hasil['pembayaran']->file_kwitansi);

        $this->assertDatabaseHas('chat_pesans', ['anak_kos_id' => $pembayaran->anak_kos_id]);

        $chat = ChatPesan::where('anak_kos_id', $pembayaran->anak_kos_id)
            ->latest('id')->firstOrFail();
        $this->assertStringContainsString('Kwitansi', $chat->isi);
        $this->assertStringContainsString($hasil['pembayaran']->nomor_kwitansi, $chat->isi);
    }

    public function test_kwitansi_idempoten_tidak_dibuat_ulang(): void
    {
        Storage::fake('public');
        Storage::fake(BuktiStorage::DISK);

        $pemilik = $this->user('pemilik1@ngekos.test');
        $pembayaran = Pembayaran::where('status', 'menunggu_verifikasi')->firstOrFail();

        $pertama = PembayaranService::verifikasi($pembayaran, $pemilik->id)['pembayaran'];
        $kedua = KwitansiService::untuk($pertama->refresh());

        $this->assertSame($pertama->nomor_kwitansi, $kedua->nomor_kwitansi);
        $this->assertSame($pertama->file_kwitansi, $kedua->file_kwitansi);
    }

    public function test_anak_kos_bisa_unduh_kwitansi_web(): void
    {
        $pemilik = $this->user('pemilik1@ngekos.test');
        $pembayaran = Pembayaran::where('status', 'menunggu_verifikasi')->firstOrFail();

        PembayaranService::verifikasi($pembayaran, $pemilik->id);

        $anak = User::findOrFail($pembayaran->anak_kos_id);

        $this->actingAs($anak)
            ->get(route('pembayaran.kwitansi', $pembayaran->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    // ---------- 4. Grafik API ----------

    public function test_api_grafik_mendukung_filter_periode_dan_properti(): void
    {
        $pemilik = $this->user('pemilik1@ngekos.test');
        SubscriptionService::mulaiTrialFree($pemilik);
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();

        Sanctum::actingAs($pemilik, ['*']);

        $res = $this->getJson('/api/dashboard/pemilik/grafik?periode=3')
            ->assertOk()
            ->assertJsonStructure([
                'periode', 'propertis', 'chart', 'rekap',
                'tren_transaksi', 'aging_piutang', 'tagihan_belum', 'top_properti',
            ])->json();

        $this->assertSame(3, count($res['chart']['labels']));
        $this->assertSame(3, count($res['rekap']['pendapatan']));

        $this->getJson("/api/dashboard/pemilik/grafik?periode=6&properti_id={$properti->id}")
            ->assertOk()
            ->assertJsonPath('properti_id', $properti->id);
    }

    // ---------- 5. Galeri ----------

    public function test_api_update_properti_dengan_multi_foto(): void
    {
        Storage::fake('public');

        $pemilik = $this->user('pemilik1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Mawar')->firstOrFail();

        Sanctum::actingAs($pemilik, ['*']);

        $res = $this->putJson("/api/pemilik/properti/{$properti->id}", [
            'nama' => $properti->nama,
            'jenis_harga' => 'bulanan',
            'status' => 'aktif',
            'fasilitas' => 'WiFi, Parkir',
            'fotos' => [
                UploadedFile::fake()->image('g1.jpg'),
                UploadedFile::fake()->image('g2.jpg'),
                UploadedFile::fake()->image('g3.jpg'),
            ],
        ])->assertOk()->json();

        $this->assertCount(3, $res['properti']['fotos']);
        $this->assertSame($res['properti']['fotos'][0], $res['properti']['foto']);
        $this->assertSame(3, $properti->refresh()->fotos()->count());
    }

    public function test_halaman_grafik_web_bisa_diakses_pemilik_dan_ada_di_sidebar(): void
    {
        $pemilik = $this->user('pemilik1@ngekos.test');
        SubscriptionService::mulaiTrialFree($pemilik);

        $this->actingAs($pemilik)
            ->get(route('pemilik.grafik'))
            ->assertOk()
            ->assertSee('Grafik')
            ->assertSee('Uang Masuk vs Uang Keluar')
            ->assertSee('Siapa yang belum bayar');

        $this->actingAs($pemilik)
            ->get(route('dashboard.pemilik'))
            ->assertOk()
            ->assertSee(route('pemilik.grafik', absolute: false), false);
    }

    public function test_dashboard_anak_kos_tanpa_ktp_tetap_terbuka(): void
    {
        $anak = $this->user('anak1@ngekos.test');

        // Booking baru tidak memakai KTP sama sekali.
        // Pastikan halaman dashboard anak kos tetap terbuka normal.
        Volt::actingAs($anak)->test('pages.dashboard.anak-kos')
            ->assertHasNoErrors();
    }
}
