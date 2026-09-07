<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Volt\Volt;
use Tests\TestCase;

class DashboardGrafikBaruTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_admin_dashboard_menampilkan_chart_analitik_baru(): void
    {
        $user = User::where('email', 'admin.ngekos@gmail.com')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.dashboard.admin');

        $component->assertViewHas('chartNilaiTransaksi')
            ->assertViewHas('userGrowthMonth')
            ->assertViewHas('statusPenyewaan')
            ->assertViewHas('statusPembayaran')
            ->assertSet('periode', '6');

        $this->actingAs($user)
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee('Pertumbuhan Pengguna')
            ->assertSee('Nilai Transaksi Terverifikasi')
            ->assertSee('Distribusi Status Penyewaan')
            ->assertSee('Distribusi Status Pembayaran');
    }

    public function test_super_admin_dashboard_menampilkan_transaction_growth_top_properti_dan_empty_state(): void
    {
        $user = User::where('email', 'superadmin.ngekos@gmail.com')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.dashboard.super-admin');

        $component->assertViewHas('chartTransaksiJumlah')
            ->assertViewHas('chartTransaksiNilai')
            ->assertViewHas('topPropertis', fn ($value) => ! empty($value));

        $this->actingAs($user)
            ->get(route('dashboard.super-admin'))
            ->assertOk()
            ->assertSee('Pertumbuhan Transaksi')
            ->assertSee('Top Properti Berkinerja')
            ->assertSee('Platform Revenue')
            ->assertSee('Data belum tersedia')
            ->assertSee('Premium Conversion');
    }

    public function test_admin_dashboard_status_penyewaan_memuat_aktif_dan_selesai(): void
    {
        $user = User::where('email', 'admin.ngekos@gmail.com')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.dashboard.admin');
        $statusPenyewaan = $component->viewData('statusPenyewaan');
        $statusPembayaran = $component->viewData('statusPembayaran');

        $this->assertArrayHasKey('aktif', $statusPenyewaan);
        $this->assertArrayHasKey('selesai', $statusPenyewaan);
        $this->assertArrayHasKey('diverifikasi', $statusPembayaran);
    }

    public function test_api_admin_mengandung_analitik_baru_dengan_nilai_benar(): void
    {
        $user = User::where('email', 'admin.ngekos@gmail.com')->firstOrFail();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/dashboard/admin?periode=6')
            ->assertOk()
            ->assertJsonStructure([
                'user_growth' => ['labels', 'anak_kos', 'pemilik'],
                'transaksi_nilai',
                'sewaan_status',
                'pembayaran_status',
            ])
            ->json();

        $this->assertSame(6, count($response['user_growth']['anak_kos']));
        $this->assertSame(6, count($response['transaksi_nilai']));
        $this->assertArrayHasKey('diverifikasi', $response['pembayaran_status']);
    }

    public function test_api_super_admin_top_propertis_terurut_pendapatan_desc(): void
    {
        $user = User::where('email', 'superadmin.ngekos@gmail.com')->firstOrFail();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/dashboard/super-admin?periode=6')
            ->assertOk()
            ->json();

        $this->assertNotEmpty($response['top_propertis']);

        $pendapatans = collect($response['top_propertis'])->pluck('pendapatan')->values()->all();
        $sorted = collect($pendapatans)->sortDesc()->values()->all();

        $this->assertSame($sorted, $pendapatans);
        foreach ($response['top_propertis'] as $p) {
            $this->assertGreaterThan(0, $p['pendapatan']);
            $this->assertArrayHasKey('okupansi', $p);
            $this->assertArrayHasKey('rating', $p);
        }
    }
}