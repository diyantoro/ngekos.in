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

        $response = $this->actingAs($this->pemilik())
            ->get(route('pemilik.rekap.pdf', ['bulan' => $bulan]))
            ->assertOk()
            ->assertHeader('Content-Disposition', "attachment; filename=rekap-pemilik-{$bulan}.pdf");

        $this->assertStringContainsString(
            'application/pdf',
            $response->headers->get('Content-Type') ?? ''
        );
    }

    public function test_ekspor_excel_mengunduh_file_xlsx(): void
    {
        $bulan = now()->format('Y-m');

        $response = $this->actingAs($this->pemilik())
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
        $response = $this->actingAs($this->pemilik())
            ->get(route('pemilik.grafik'));

        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('Ekspor Rekap Bulanan', $content);
        $this->assertStringContainsString(route('pemilik.rekap.pdf'), $content);
        $this->assertStringContainsString(route('pemilik.rekap.excel'), $content);
        $this->assertStringContainsString('type="month"', $content);
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