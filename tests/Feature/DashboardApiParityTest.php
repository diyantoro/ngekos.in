<?php

namespace Tests\Feature;

use App\Models\ChatPesan;
use App\Models\Pembayaran;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiParityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_api_anak_kos_dashboard_mengandung_favorit_pesan_dan_rekomendasi(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();

        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/dashboard/anak-kos')
            ->assertOk()
            ->assertJsonStructure([
                'penyewaan_aktif',
                'tagihan_belum_bayar',
                'total_dibayar',
                'jumlah_favorit',
                'pesan_belum_dibaca',
                'tagihan_berikutnya' => ['id', 'periode', 'jumlah', 'jatuh_tempo', 'properti'],
                'rekomendasi',
            ]);
    }

    public function test_api_pemilik_dashboard_mengandung_finansial_dan_okupansi(): void
    {
        $user = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/dashboard/pemilik')
            ->assertOk()
            ->assertJsonStructure([
                'total_properti',
                'total_kamar',
                'kamar_terisi',
                'kamar_kosong',
                'kamar_perbaikan',
                'okupansi',
                'pendapatan_bulan_ini',
                'pengeluaran_bulan_ini',
                'laba_bersih_bulan_ini',
                'tagihan_belum_bayar',
                'nilai_tagihan_belum',
                'tagihan_telat',
                'kategori_pengeluaran',
                'penyewaan_aktif',
                'chart' => ['labels', 'pendapatan', 'lunas', 'belum'],
                'rekap' => ['labels', 'pendapatan', 'pengeluaran', 'laba', 'okupansi'],
'pembayaran_terbaru',
            'pembayaran_menunggu',
            'tagihan_list',
                'funnel',
            ])
            ->json();

        $this->assertGreaterThan(0, $response['kamar_terisi'] + $response['kamar_kosong'] + $response['kamar_perbaikan']);
        $this->assertSame(
            $response['pendapatan_bulan_ini'] - $response['pengeluaran_bulan_ini'],
            $response['laba_bersih_bulan_ini']
        );
        $this->assertSame(
            $response['pendapatan_bulan_ini'] - $response['pengeluaran_bulan_ini'],
            (int) $response['rekap']['laba'][count($response['rekap']['laba']) - 1]
        );
    }

    public function test_api_pemilik_rekap_mengandung_pengeluaran_dan_laba(): void
    {
        $user = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/dashboard/pemilik/rekap?bulan='.now()->format('Y-m'))
            ->assertOk()
            ->assertJsonStructure([
                'periode',
                'bulan',
                'ringkasan' => ['pendapatan', 'pengeluaran', 'laba_bersih'],
                'kategori_pengeluaran',
                'propertis',
                'sewaans',
                'transaksi',
            ])
            ->json();

        $this->assertSame(
            $response['ringkasan']['pendapatan'] - $response['ringkasan']['pengeluaran'],
            $response['ringkasan']['laba_bersih']
        );
    }

    public function test_api_admin_dashboard_mengandung_growth_dan_total_kamar(): void
    {
        $user = User::where('email', 'admin.ngekos@gmail.com')->firstOrFail();

        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/dashboard/admin?periode=6')
            ->assertOk()
            ->assertJsonStructure([
                'total_tugas',
                'pembayaran_menunggu',
                'penyewaan_aktif',
                'total_kamar',
                'kamar_terisi',
                'kamar_kosong',
                'tagihan_belum_bayar',
                'growth' => ['labels', 'properti', 'penyewaan', 'pembayaran'],
                'funnel',
                'chart',
                'user_growth' => ['labels', 'anak_kos', 'pemilik'],
                'transaksi_nilai',
                'sewaan_status',
                'pembayaran_status',
                'pembayarans',
                'checkouts',
            ]);
    }

    public function test_api_super_admin_dashboard_mengandung_growth_dan_peran(): void
    {
        $user = User::where('email', 'superadmin.ngekos@gmail.com')->firstOrFail();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/dashboard/super-admin?periode=6')
            ->assertOk()
            ->assertJsonStructure([
                'total_user',
                'total_pemilik',
                'total_anak_kos',
                'total_admin',
                'total_properti',
                'total_kamar',
                'penyewaan_aktif',
                'pendapatan',
                'growth' => ['labels', 'user_total', 'user_anak_kos', 'user_pemilik', 'properti', 'penyewaan'],
                'funnel',
                'chart',
                'transaction_growth' => ['labels', 'jumlah', 'nilai'],
                'top_propertis',
                'sewaan_status',
                'platform_revenue',
                'premium_conversion',
                'propertis',
                'checkouts',
            ])
            ->json();

        $this->assertGreaterThan(0, $response['total_user']);
        $this->assertSame(count($response['growth']['labels']), 6);
        $this->assertSame(count($response['growth']['user_total']), 6);
        $this->assertSame(count($response['transaction_growth']['jumlah']), 6);
        $this->assertSame(count($response['transaction_growth']['nilai']), 6);
        $this->assertNotEmpty($response['top_propertis']);
        $this->assertNull($response['platform_revenue']);
        $this->assertNull($response['premium_conversion']);
    }

    public function test_api_pemilik_bisa_verifikasi_pembayaran_dan_membalas_chat(): void
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $pembayaran = Pembayaran::where('status', 'menunggu_verifikasi')->firstOrFail();
        $pembayaran->tagihan->update(['jatuh_tempo' => today()->toDateString(), 'denda' => 0]);
        $anakId = $pembayaran->anak_kos_id;

        Sanctum::actingAs($pemilik, ['*']);

        $this->postJson("/api/dashboard/pemilik/pembayaran/{$pembayaran->id}/verifikasi", ['status' => 'diverifikasi'])
            ->assertOk();

        $pembayaran->refresh();
        $this->assertSame('diverifikasi', $pembayaran->status);
        $this->assertSame($pemilik->id, $pembayaran->diverifikasi_oleh);

        $pembayaran->tagihan->refresh();
        $this->assertSame('lunas', $pembayaran->tagihan->status);

        $chat = ChatPesan::where('anak_kos_id', $anakId)
            ->where('pengirim_id', $pemilik->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($chat, 'Anak kos harus mendapat chat balasan verifikasi via API.');
        $this->assertStringContainsString('telah saya verifikasi', $chat->isi);
    }

    public function test_api_admin_dashboard_ditolak_untuk_anak_kos(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();

        Sanctum::actingAs($user, ['*']);

        $this->getJson('/api/dashboard/admin')->assertForbidden();
    }
}