<?php

namespace Tests\Feature;

use App\Models\ChatPesan;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Tagihan;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class DashboardRenderCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_super_admin_dashboard_renders_component(): void
    {
        $user = User::where('email', 'superadmin.ngekos@gmail.com')->first();

        $this->actingAs($user)
            ->get(route('dashboard.super-admin'))
            ->assertOk()
            ->assertSeeVolt('pages.dashboard.super-admin')
            ->assertSee('Properti');
    }

    public function test_pemilik_dashboard_renders_component(): void
    {
        $user = User::where('email', 'pemilik1@ngekos.test')->first();

        $this->actingAs($user)
            ->get(route('dashboard.pemilik'))
            ->assertOk()
            ->assertSeeVolt('pages.dashboard.pemilik')
            ->assertSee('Kos Melati')
            ->assertSee('Pembayaran Menunggu Verifikasi')
            ->assertSee('Paket Premium Kos')
            ->assertSee('Segera hadir');
    }

    public function test_admin_dashboard_renders_component_and_can_verify_payment(): void
    {
        $user = User::where('email', 'admin.ngekos@gmail.com')->first();

        $this->actingAs($user)
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSeeVolt('pages.dashboard.admin');

        $pembayaran = Pembayaran::where('status', 'menunggu_verifikasi')->first();

        $component = Volt::actingAs($user)->test('pages.dashboard.admin');
        $component->call('verifikasiPembayaran', $pembayaran->id)
            ->assertHasNoErrors()
            ->assertSet('pesan', fn ($pesan) => str_contains($pesan, 'diverifikasi'));

        $pembayaran->refresh();
        $this->assertSame('diverifikasi', $pembayaran->status);
    }

    public function test_anak_kos_dashboard_renders_component_and_actions_work(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();

        $this->actingAs($user)
            ->get(route('dashboard.anak-kos'))
            ->assertOk()
            ->assertSeeVolt('pages.dashboard.anak-kos')
            ->assertSee('Sewa Saya');

        $sewaan = Penyewaan::where('anak_kos_id', $user->id)->where('status', 'aktif')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.dashboard.anak-kos');
        $component->call('checkOut', $sewaan->id)
            ->assertSet('pesan', fn ($pesan) => str_contains(strtolower($pesan), 'check-out'));

        $sewaan->refresh();
        $this->assertEquals('selesai', $sewaan->status);
        $this->assertNotNull($sewaan->tanggal_keluar);
        $this->assertEquals('tersedia', $sewaan->kamar->status);
    }

    public function test_anak_kos_dashboard_shows_penyewaan_and_tagihan(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();

        $this->actingAs($user)
            ->get(route('dashboard.anak-kos'))
            ->assertOk()
            ->assertSee('Penyewaan Aktif')
            ->assertSee('Tagihan Belum Bayar');
    }

    public function test_anak_kos_bisa_bayar_tunai_tanpa_bukti(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();
        $tagihan = Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', $user->id))
            ->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.dashboard.anak-kos');
        $component->call('bayarTagihan', $tagihan->id)
            ->assertSet('modalBayarId', $tagihan->id)
            ->assertSet('metodeBayar', 'transfer');

        $component->call('ubahMetodeBayar', 'cash')
            ->assertSet('metodeBayar', 'cash')
            ->call('konfirmasiBayar')
            ->assertHasNoErrors()
            ->assertSet('modalBayarId', null)
            ->assertSet('pesan', fn ($pesan) => str_contains(strtolower($pesan), 'tunai'));

        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->firstOrFail();
        $this->assertSame('cash', $pembayaran->metode);
        $this->assertNull($pembayaran->bukti);
        $this->assertSame('menunggu_verifikasi', $pembayaran->status);

        $chat = ChatPesan::where('properti_id', $tagihan->penyewaan->properti_id)
            ->where('anak_kos_id', $user->id)
            ->where('pengirim_id', $user->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($chat, 'Pemilik harus mendapat chat notifikasi pembayaran.');
        $this->assertStringContainsString('Mohon diverifikasi', $chat->isi);
    }

    public function test_pemilik_bisa_verifikasi_pembayaran_dan_membalas_chat(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $pembayaran = Pembayaran::where('status', 'menunggu_verifikasi')->firstOrFail();
        $anakId = $pembayaran->anak_kos_id;

        $component = Volt::actingAs($pemilik)->test('pages.dashboard.pemilik');
        $component->assertViewHas('pembayaranMenunggu')
            ->call('verifikasiPembayaran', $pembayaran->id)
            ->assertHasNoErrors()
            ->assertSet('pesan', fn ($pesan) => str_contains($pesan, 'diverifikasi'));

        $pembayaran->refresh();
        $this->assertSame('diverifikasi', $pembayaran->status);
        $this->assertSame($pemilik->id, $pembayaran->diverifikasi_oleh);

        $pembayaran->tagihan->refresh();
        $this->assertSame('lunas', $pembayaran->tagihan->status);

        $chat = ChatPesan::where('anak_kos_id', $anakId)
            ->where('pengirim_id', $pemilik->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($chat, 'Anak kos harus mendapat chat balasan verifikasi.');
        $this->assertStringContainsString('telah saya verifikasi', $chat->isi);
    }

    public function test_pemilik_dashboard_menampilkan_ringkas_tanpa_grafik(): void
    {
        $user = User::where('email', 'pemilik1@ngekos.test')->first();

        $component = Volt::actingAs($user)->test('pages.dashboard.pemilik');

        $component->assertViewHas('okupansiSekarang')
            ->assertViewHas('kamarTersedia')
            ->assertViewHas('pengeluaranBulanIni')
            ->assertViewHas('labaBersihBulanIni')
            ->assertViewHas('tagihanBelumCount')
            ->assertViewMissing('chartKategori')
            ->assertViewMissing('chartOkupansi')
            ->assertViewMissing('bulanLabels')
            ->assertViewMissing('funnelStages');

        $this->actingAs($user)
            ->get(route('dashboard.pemilik'))
            ->assertOk()
            ->assertSee('Tingkat Okupansi')
            ->assertSee('Laba Bersih Bulan Ini')
            ->assertSee('Buka Grafik')
            ->assertDontSee('Pengeluaran per Kategori')
            ->assertDontSee('Grafik Pipeline');
    }

    public function test_super_admin_dashboard_menampilkan_pertumbuhan(): void
    {
        $user = User::where('email', 'superadmin.ngekos@gmail.com')->first();

        $component = Volt::actingAs($user)->test('pages.dashboard.super-admin');

        $component->assertViewHas('totalPemilik')
            ->assertViewHas('totalAnakKos')
            ->assertViewHas('bulanLabels')
            ->assertViewHas('chartGrowthTotal')
            ->assertViewHas('chartGrowthProperti')
            ->assertViewHas('chartGrowthPenyewaan')
            ->assertSet('periode', '6');

        $this->actingAs($user)
            ->get(route('dashboard.super-admin'))
            ->assertOk()
            ->assertSee('Pertumbuhan Pengguna')
            ->assertSee('Pertumbuhan Bisnis');
    }

    public function test_admin_dashboard_menampilkan_pertumbuhan_kelolaan(): void
    {
        $user = User::where('email', 'admin.ngekos@gmail.com')->first();

        $component = Volt::actingAs($user)->test('pages.dashboard.admin');

        $component->assertViewHas('totalKamar')
            ->assertViewHas('kamarTerisi')
            ->assertViewHas('tagihanBelum')
            ->assertViewHas('statusProperti')
            ->assertViewHas('bulanLabels')
            ->assertViewHas('chartGrowthProperti')
            ->assertViewHas('chartGrowthPenyewaan')
            ->assertViewHas('chartGrowthPembayaran')
            ->assertSet('periode', '6');

        $this->actingAs($user)
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee('Total Kamar Kelolaan')
            ->assertSee('Pertumbuhan Kelolaan')
            ->assertSee('Transaksi Terverifikasi');
    }

    public function test_anak_kos_dashboard_menampilkan_favorit_pesan_dan_rekomendasi(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();

        $component = Volt::actingAs($user)->test('pages.dashboard.anak-kos');

        $component->assertViewHas('jumlahFavorit')
            ->assertViewHas('pesanBelumDibaca')
            ->assertViewHas('tagihanBerikutnya')
            ->assertViewHas('rekomendasi');

        $this->actingAs($user)
            ->get(route('dashboard.anak-kos'))
            ->assertOk()
            ->assertSee('Pesan Belum Dibaca')
            ->assertSee('Rekomendasi untukmu');
    }

    public function test_dashboard_route_blocked_for_wrong_role(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();

        $this->actingAs($user)
            ->get(route('dashboard.admin'))
            ->assertForbidden();
    }
}
