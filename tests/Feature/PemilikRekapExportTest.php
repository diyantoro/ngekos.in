<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemilikRekapExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    private function pemilik(): User
    {
        return User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
    }

    public function test_route_ekspor_pdf_tanpa_login_diarahkan_ke_login(): void
    {
        $this->get(route('pemilik.rekap.pdf'))
            ->assertRedirect(route('login'));
    }

    public function test_route_ekspor_excel_tanpa_login_diarahkan_ke_login(): void
    {
        $this->get(route('pemilik.rekap.excel'))
            ->assertRedirect(route('login'));
    }

    public function test_ekspor_pdf_mengunduh_file_pdf(): void
    {
        $bulan = now()->format('Y-m');
        \App\Services\SubscriptionService::mulaiTrialFree($this->pemilik());

        $response = $this->actingAs($this->pemilik())
            ->get(route('pemilik.rekap.pdf', ['bulan' => $bulan]))
            ->assertOk()
            ->assertHeader('Content-Disposition', "attachment; filename=rekap-pemilik-{$bulan}.pdf");

        $this->assertStringContainsString(
            'application/pdf',
            $response->headers->get('Content-Type') ?? ''
        );
        // Trial klaim = tier PRO.
        $this->assertSame('pro', $response->headers->get('X-Report-Tier'));
    }

    public function test_ekspor_pdf_trial_ada_watermark(): void
    {
        $bulan = now()->format('Y-m');
        \App\Services\SubscriptionService::mulaiTrialFree($this->pemilik());

        $pdf = $this->actingAs($this->pemilik())
            ->get(route('pemilik.rekap.pdf', ['bulan' => $bulan]))
            ->assertOk()
            ->streamedContent();

        $this->assertNotEmpty($pdf);
    }

    public function test_ekspor_excel_diizinkan_free_dan_trial(): void
    {
        $bulan = now()->format('Y-m');

        // Free tanpa trial (dianggap habis) ditolak 403.
        $this->actingAs($this->pemilik())
            ->get(route('pemilik.rekap.excel', ['bulan' => $bulan]))
            ->assertForbidden();

        \App\Services\SubscriptionService::mulaiTrialFree($this->pemilik());

        $this->actingAs($this->pemilik())
            ->get(route('pemilik.rekap.excel', ['bulan' => $bulan]))
            ->assertOk();
    }

    public function test_ekspor_rekap_ditolak_setelah_trial_habis(): void
    {
        $bulan = now()->format('Y-m');
        $pemilik = $this->pemilik();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);
        \App\Models\Subscription::where('user_id', $pemilik->id)->update(['expires_at' => now()->subDay()]);

        $this->actingAs($pemilik)
            ->get(route('pemilik.rekap.pdf', ['bulan' => $bulan]))
            ->assertForbidden();

        $this->actingAs($pemilik)
            ->get(route('pemilik.rekap.excel', ['bulan' => $bulan]))
            ->assertForbidden();

        $this->actingAs($pemilik)
            ->get(route('pemilik.grafik'))
            ->assertOk()
            ->assertSee('Masa coba 7 hari sudah habis', false);
    }

    public function test_ekspor_excel_mengunduh_file_xlsx(): void
    {
        $bulan = now()->format('Y-m');
        $pemilik = $this->pemilik();
        \App\Models\Subscription::create([
            'user_id' => $pemilik->id, 'plan' => 'pro', 'status' => 'active',
            'starts_at' => now()->subDay(), 'expires_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($pemilik)
            ->get(route('pemilik.rekap.excel', ['bulan' => $bulan]))
            ->assertOk()
            ->assertHeader('Content-Disposition', "attachment; filename=rekap-pemilik-{$bulan}.xlsx");

        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument',
            $response->headers->get('Content-Type') ?? ''
        );
    }

    public function test_ekspor_pdf_bulan_tidak_valid_mengembalikan_422(): void
    {
        \App\Services\SubscriptionService::mulaiTrialFree($this->pemilik());
        $this->actingAs($this->pemilik())
            ->from(route('dashboard.pemilik'))
            ->get(route('pemilik.rekap.pdf', ['bulan' => 'bukan-bulan']))
            ->assertStatus(422);
    }

    public function test_ekspor_ditolak_untuk_anak_kos(): void
    {
        $anakKos = User::where('email', 'anak1@ngekos.test')->firstOrFail();

        $this->actingAs($anakKos)
            ->get(route('pemilik.rekap.pdf'))
            ->assertForbidden();
    }

    public function test_halaman_grafik_menampilkan_control_ekspor(): void
    {
        $pemilik = $this->pemilik();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);
        $response = $this->actingAs($pemilik)
            ->get(route('pemilik.grafik'));

        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Ekspor Rekap Bulanan', $content);
        $this->assertStringContainsString(route('pemilik.rekap.pdf'), $content);
        $this->assertStringContainsString('type="month"', $content);
    }

    public function test_halaman_grafik_free_partial_lock(): void
    {
        $pemilik = $this->pemilik();
        \App\Services\SubscriptionService::mulaiTrialFree($pemilik);

        $content = $this->actingAs($pemilik)->get(route('pemilik.grafik'))->getContent();

        $this->assertStringContainsString('Uang Masuk vs Uang Keluar', $content);
        $this->assertStringContainsString('Siapa yang belum bayar', $content);
        $this->assertStringContainsString('Kos Pemasukan Terbesar', $content);
        $this->assertStringContainsString('Pengeluaran per Kategori', $content);
        $this->assertStringContainsString('Kamar Terisi', $content);
    }

    public function test_dashboard_pemilik_tidak_menampilkan_grafik_hanya_tautan(): void
    {
        $response = $this->actingAs($this->pemilik())
            ->get(route('dashboard.pemilik'));

        $response->assertOk();

        $content = $response->getContent();
        // Dashboard hanya berisi kartu tautan ke menu Grafik, bukan grafik itu sendiri.
        $this->assertStringContainsString(route('pemilik.grafik'), $content);
        $this->assertStringContainsString('Buka Grafik', $content);
        $this->assertStringNotContainsString('Ekspor Rekap Bulanan', $content);
        $this->assertStringNotContainsString('id="rekap-data"', $content);
        $this->assertStringNotContainsString('id="chart-rekap-keuangan"', $content);
        $this->assertStringNotContainsString('Grafik Pipeline', $content);
    }
}