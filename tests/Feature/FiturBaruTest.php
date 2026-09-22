<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\User;
use App\Services\PatunganService;
use App\Services\PembayaranService;
use App\Services\PenyewaanService;
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

    // ---------- 1. KTP wajib saat pesan ----------

    public function test_api_sewa_ditolak_tanpa_ktp(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        Sanctum::actingAs($anak, ['*']);

        $this->postJson("/api/kos/{$properti->id}/kamar/{$kamar->id}/sewa", [
            'tanggal_masuk' => today()->toDateString(),
            'durasi_bulan' => 1,
        ])->assertStatus(422)->assertJsonValidationErrors('ktp');

        $this->assertSame('tersedia', $kamar->refresh()->status);
    }

    public function test_api_sewa_berhasil_dengan_ktp(): void
    {
        Storage::fake('public');
        Storage::fake('private');

        $anak = $this->user('anak1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = $properti->kamars()->where('status', 'tersedia')->firstOrFail();

        Sanctum::actingAs($anak, ['*']);

        $this->postJson("/api/kos/{$properti->id}/kamar/{$kamar->id}/sewa", [
            'tanggal_masuk' => today()->toDateString(),
            'durasi_bulan' => 1,
            'ktp' => UploadedFile::fake()->image('ktp.jpg'),
        ])->assertCreated();

        $sewa = Penyewaan::where('anak_kos_id', $anak->id)->where('kamar_id', $kamar->id)->firstOrFail();
        $this->assertNotNull($sewa->ktp_path);
        Storage::disk('private')->assertExists($sewa->ktp_path);
    }

    // ---------- 2. Kwitansi otomatis ----------

    public function test_verifikasi_membuat_kwitansi_pdf_dan_chat(): void
    {
        Storage::fake('public');

        $pemilik = $this->user('pemilik1@ngekos.test');
        $pembayaran = Pembayaran::where('status', 'menunggu_verifikasi')->firstOrFail();
        $pembayaran->tagihan->update(['jatuh_tempo' => today()->toDateString(), 'denda' => 0]);

        $hasil = PembayaranService::verifikasi($pembayaran, $pemilik->id, 'diverifikasi');

        $this->assertTrue($hasil['tagihan_lunas']);
        $this->assertNotNull($hasil['pembayaran']->nomor_kwitansi);
        $this->assertMatchesRegularExpression('/^KWT-\d{6}-\d{4}$/', $hasil['pembayaran']->nomor_kwitansi);
        $this->assertNotNull($hasil['kwitansi_url']);
        Storage::disk('public')->assertExists($hasil['pembayaran']->file_kwitansi);

        $this->assertDatabaseHas('chat_pesans', ['anak_kos_id' => $pembayaran->anak_kos_id]);

        $chat = \App\Models\ChatPesan::where('anak_kos_id', $pembayaran->anak_kos_id)
            ->latest('id')->firstOrFail();
        $this->assertStringContainsString('Kwitansi', $chat->isi);
        $this->assertStringContainsString($hasil['pembayaran']->nomor_kwitansi, $chat->isi);
    }

    public function test_kwitansi_idempoten_tidak_dibuat_ulang(): void
    {
        Storage::fake('public');

        $pemilik = $this->user('pemilik1@ngekos.test');
        $pembayaran = Pembayaran::where('status', 'menunggu_verifikasi')->firstOrFail();

        $pertama = PembayaranService::verifikasi($pembayaran, $pemilik->id)['pembayaran'];
        $kedua = \App\Services\KwitansiService::untuk($pertama->refresh());

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

    // ---------- 3. Patungan 50/50 ----------

    private function sewaPatunganA3(): Penyewaan
    {
        $rina = $this->user('anak1@ngekos.test');
        $yoga = $this->user('anak2@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();

        // Kamar fresh kapasitas 2 agar tidak konflik dengan seed demo.
        $kamar = Kamar::create([
            'properti_id' => $properti->id,
            'nama' => 'PAT-'.uniqid(),
            'kapasitas' => 2,
            'harga_sewa_bulanan' => 1200000,
            'harga_sewa_harian' => 60000,
            'status' => 'tersedia',
        ]);

        $sewa = app(PenyewaanService::class)->sewaKamar(
            $rina, $kamar, today()->toDateString(), 1, null, 'ktp/rina.jpg'
        );

        PatunganService::tambahAnggota($sewa->refresh(), $yoga, 'ktp/yoga.jpg');

        return $sewa->refresh();
    }

    public function test_tambah_anggota_mengubah_mode_patungan_dan_porsi_50(): void
    {
        $sewa = $this->sewaPatunganA3();

        $this->assertSame('patungan', $sewa->mode_hunian);

        $penghuni = collect($sewa->idPenghuniAktif())->sort()->values()->all();
        $ekspektasi = collect([$sewa->anak_kos_id, $this->user('anak2@ngekos.test')->id])->sort()->values()->all();
        $this->assertSame($ekspektasi, $penghuni);

        $tagihan = $sewa->tagihans()->firstOrFail();
        $porsi = PatunganService::porsiTagihan($sewa, $tagihan);
        $this->assertEquals(((float) $tagihan->jumlah + (float) $tagihan->denda) / 2, $porsi);
    }

    public function test_tambah_anggota_mengirim_chat_ke_teman_dan_utama(): void
    {
        $sewa = $this->sewaPatunganA3();
        $rina = $this->user('anak1@ngekos.test');
        $yoga = $this->user('anak2@ngekos.test');

        $this->assertDatabaseHas('chat_pesans', [
            'properti_id' => $sewa->properti_id,
            'anak_kos_id' => $yoga->id,
            'pengirim_id' => $rina->id,
        ]);
        $this->assertDatabaseHas('chat_pesans', [
            'properti_id' => $sewa->properti_id,
            'anak_kos_id' => $rina->id,
            'pengirim_id' => $yoga->id,
        ]);

        $chatTeman = \App\Models\ChatPesan::where('anak_kos_id', $yoga->id)
            ->where('properti_id', $sewa->properti_id)
            ->latest('id')->firstOrFail();
        $this->assertStringContainsString('patungan 50/50', $chatTeman->isi);
        $this->assertNull($chatTeman->dibaca_pada);

        Volt::actingAs($yoga)->test('pages.dashboard.anak-kos')
            ->assertSee('Kamu ditambahkan sebagai teman sekamar');
    }

    public function test_bayar_masing_masing_melunasi_tagihan(): void
    {
        $sewa = $this->sewaPatunganA3();
        $pemilik = $this->user('pemilik1@ngekos.test');
        $tagihan = $sewa->tagihans()->firstOrFail();
        $porsi = PatunganService::porsiTagihan($sewa, $tagihan);

        foreach ($sewa->idPenghuniAktif() as $uid) {
            $bayar = Pembayaran::create([
                'tagihan_id' => $tagihan->id,
                'anak_kos_id' => $uid,
                'metode' => 'transfer',
                'jumlah' => $porsi,
                'status' => 'menunggu_verifikasi',
            ]);
            PembayaranService::verifikasi($bayar, $pemilik->id);
        }

        $this->assertSame('lunas', $tagihan->refresh()->status);
    }

    public function test_keluar_ditolak_bila_porsi_belum_lunas(): void
    {
        $sewa = $this->sewaPatunganA3();
        $yoga = $this->user('anak2@ngekos.test');

        // Tagihan bawaan service sudah ada dan belum dibayar siapa pun.
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Lunasi dulu porsi/');

        PatunganService::keluarkan($sewa, $yoga->id);
    }

    public function test_keluar_berhasil_setelah_porsi_lunas_kamar_tetap_terisi(): void
    {
        $sewa = $this->sewaPatunganA3();
        $pemilik = $this->user('pemilik1@ngekos.test');
        $yoga = $this->user('anak2@ngekos.test');

        // Lunasi SEMUA tagihan belum lunas porsi yoga.
        foreach (PatunganService::sisaPorsi($sewa, $yoga->id) as $sisa) {
            $bayar = Pembayaran::create([
                'tagihan_id' => $sisa['tagihan_id'],
                'anak_kos_id' => $yoga->id,
                'metode' => 'transfer',
                'jumlah' => $sisa['sisa'],
                'status' => 'menunggu_verifikasi',
            ]);
            PembayaranService::verifikasi($bayar, $pemilik->id);
        }

        $this->assertSame([], PatunganService::sisaPorsi($sewa->refresh(), $yoga->id));

        PatunganService::keluarkan($sewa->refresh(), $yoga->id);

        $sewa->refresh();
        $this->assertSame('aktif', $sewa->status);
        $this->assertSame('tunggal', $sewa->mode_hunian);
        $this->assertSame('terisi', $sewa->kamar->refresh()->status);
        $this->assertSame(
            'keluar',
            $sewa->anggotas()->where('user_id', $yoga->id)->firstOrFail()->status
        );

        // Stayer (rina) dapat chat porsi 100%, leaver (yoga) dapat chat pamit.
        $rina = $this->user('anak1@ngekos.test');
        $chatStay = \App\Models\ChatPesan::where('anak_kos_id', $rina->id)
            ->where('properti_id', $sewa->properti_id)
            ->latest('id')->firstOrFail();
        $this->assertStringContainsString('porsimu 100%', $chatStay->isi);
        $chatLeaver = \App\Models\ChatPesan::where('anak_kos_id', $yoga->id)
            ->where('properti_id', $sewa->properti_id)
            ->latest('id')->firstOrFail();
        $this->assertStringContainsString('sudah keluar', $chatLeaver->isi);

        // Banner stay muncul di dashboard stayer.
        Volt::actingAs($rina)->test('pages.dashboard.anak-kos')
            ->assertSee('sudah keluar, kamu tetap stay');
    }

    public function test_utama_keluar_mempromosikan_anggota_yang_stay(): void
    {
        $maya = $this->user('anak3@ngekos.test');
        $rina = $this->user('anak1@ngekos.test');
        $pemilik = $this->user('pemilik1@ngekos.test');

        $b2 = Kamar::where('nama', 'B2')->firstOrFail();
        $b2->update(['kapasitas' => 2]);

        $sewa = app(PenyewaanService::class)->sewaKamar(
            $maya, $b2, today()->toDateString(), 1, null, 'ktp/maya.jpg'
        );
        PatunganService::tambahAnggota($sewa->refresh(), $rina, 'ktp/rina.jpg');
        $sewa->refresh();

        // Keduanya lunasi porsi agar boleh keluar.
        foreach ([$maya->id, $rina->id] as $uid) {
            foreach (PatunganService::sisaPorsi($sewa, $uid) as $sisa) {
                $bayar = Pembayaran::create([
                    'tagihan_id' => $sisa['tagihan_id'],
                    'anak_kos_id' => $uid,
                    'metode' => 'transfer',
                    'jumlah' => $sisa['sisa'],
                    'status' => 'menunggu_verifikasi',
                ]);
                PembayaranService::verifikasi($bayar, $pemilik->id);
            }
        }

        PatunganService::keluarkan($sewa->refresh(), $maya->id);

        $sewa->refresh();
        $this->assertSame('aktif', $sewa->status);
        $this->assertSame($rina->id, $sewa->anak_kos_id);
        $this->assertSame('terisi', $sewa->kamar->refresh()->status);
    }

    public function test_tambah_anggota_ditolak_untuk_kamar_kapasitas_1(): void
    {
        $rina = $this->user('anak1@ngekos.test');
        $yoga = $this->user('anak2@ngekos.test');
        $a1 = Kamar::where('nama', 'A1')->firstOrFail();

        $sewa = app(PenyewaanService::class)->sewaKamar(
            $rina, $a1, today()->toDateString(), 1, null, 'ktp/rina.jpg'
        );

        $this->expectException(\DomainException::class);
        PatunganService::tambahAnggota($sewa, $yoga, 'ktp/yoga.jpg');
    }

    public function test_tambah_anggota_ditolak_tanpa_ktp(): void
    {
        $rina = $this->user('anak1@ngekos.test');
        $yoga = $this->user('anak2@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = Kamar::create([
            'properti_id' => $properti->id,
            'nama' => 'KTP-'.uniqid(),
            'kapasitas' => 2,
            'harga_sewa_bulanan' => 1200000,
            'harga_sewa_harian' => 60000,
            'status' => 'tersedia',
        ]);
        $sewa = app(PenyewaanService::class)->sewaKamar(
            $rina, $kamar, today()->toDateString(), 1, null, 'ktp/rina.jpg'
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/KTP teman wajib/');
        PatunganService::tambahAnggota($sewa->refresh(), $yoga);
    }

    public function test_volt_tambah_teman_ditolak_tanpa_ktp(): void
    {
        $rina = $this->user('anak1@ngekos.test');
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $kamar = Kamar::create([
            'properti_id' => $properti->id,
            'nama' => 'VT-'.uniqid(),
            'kapasitas' => 2,
            'harga_sewa_bulanan' => 1200000,
            'harga_sewa_harian' => 60000,
            'status' => 'tersedia',
        ]);
        $sewa = app(PenyewaanService::class)->sewaKamar(
            $rina, $kamar, today()->toDateString(), 1, null, 'ktp/rina.jpg'
        );

        Volt::actingAs($rina)->test('pages.dashboard.anak-kos')
            ->call('bukaModalTeman', $sewa->id)
            ->set('emailTeman', 'anak2@ngekos.test')
            ->call('simpanTeman')
            ->assertHasErrors('ktpTeman');

        $this->assertSame('tunggal', $sewa->refresh()->mode_hunian);
    }

    public function test_api_tambah_anggota_dan_keluar_partial(): void
    {
        Storage::fake('public');
        Storage::fake('private');

        $sewa = $this->sewaPatunganA3();
        $rina = $this->user('anak1@ngekos.test');
        $yoga = $this->user('anak2@ngekos.test');

        // Tanpa KTP ditolak validasi.
        Sanctum::actingAs($rina, ['*']);

        $this->postJson("/api/penyewaan/{$sewa->id}/anggota", [
            'email' => 'anak3@ngekos.test',
        ])->assertStatus(422)->assertJsonValidationErrors('ktp');

        // Kamar penuh (2/2): tambah orang ketiga ditolak walau bawa KTP.
        $this->postJson("/api/penyewaan/{$sewa->id}/anggota", [
            'email' => 'anak3@ngekos.test',
            'ktp' => UploadedFile::fake()->image('ktp.jpg'),
        ])->assertStatus(422);

        // Yoga keluar partial via API (porsinya belum lunas -> 422).
        Sanctum::actingAs($yoga, ['*']);

        $res = $this->postJson("/api/penyewaan/{$sewa->id}/anggota/keluar")
            ->assertStatus(422)
            ->json();

        $this->assertStringContainsString('Lunasi dulu porsi', $res['message']);
    }

    // ---------- 4. Grafik API ----------

    public function test_api_grafik_mendukung_filter_periode_dan_properti(): void
    {
        $pemilik = $this->user('pemilik1@ngekos.test');
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);
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
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);

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

    public function test_ktp_hanya_bisa_dilihat_pemilik_kelolaan(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('ktp/test.jpg', 'fake-ktp');

        $sewa = Penyewaan::firstOrFail();
        $sewa->update(['ktp_path' => 'ktp/test.jpg']);

        // Pemilik lain (Siti) tidak boleh lihat KTP sewa milik Budi.
        $this->actingAs($this->user('pemilik2@ngekos.test'))
            ->get(route('penyewaan.ktp', $sewa->id))
            ->assertForbidden();
    }

    public function test_volt_lengkapi_ktp_susulan(): void
    {
        Storage::fake('public');
        Storage::fake('private');

        $anak = $this->user('anak1@ngekos.test');
        $sewa = Penyewaan::where('anak_kos_id', $anak->id)->where('status', 'aktif')->firstOrFail();
        $sewa->update(['ktp_path' => null]);

        Volt::actingAs($anak)->test('pages.dashboard.anak-kos')
            ->call('bukaModalKtp', $sewa->id)
            ->set('ktpSusulan', UploadedFile::fake()->image('ktp.jpg'))
            ->call('simpanKtpSusulan')
            ->assertSet('modalKtpId', null);

        $this->assertNotNull($sewa->refresh()->ktp_path);
    }
}
