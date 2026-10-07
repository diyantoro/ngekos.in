<?php

namespace Tests\Feature;

use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuBaruTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
        $this->withoutVite();
    }

    private function pemilik(): \App\Models\User
    {
        $user = \App\Models\User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $user->markEmailAsVerified();

        return $user;
    }

    private function anakKos(): \App\Models\User
    {
        $user = \App\Models\User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $user->markEmailAsVerified();

        return $user;
    }

    public function test_pemilik_bisa_buka_halaman_tagihan_dan_penyewa(): void
    {
        $this->actingAs($this->pemilik())->get(route('pemilik.tagihan'))
            ->assertOk()->assertSee('Tagihan Penyewa', false);
        $this->actingAs($this->pemilik())->get(route('pemilik.penyewa'))
            ->assertOk()->assertSee('Penyewa Saya', false);
    }

    public function test_anak_kos_bisa_buka_halaman_tagihan(): void
    {
        $this->actingAs($this->anakKos())->get(route('anak-kos.tagihan'))
            ->assertOk()->assertSee('Tagihan &', false);
    }

    public function test_halaman_baru_tertutup_untuk_role_lain(): void
    {
        $this->actingAs($this->anakKos())->get(route('pemilik.tagihan'))->assertForbidden();
        $this->actingAs($this->anakKos())->get(route('pemilik.penyewa'))->assertForbidden();
        $this->actingAs($this->pemilik())->get(route('anak-kos.tagihan'))->assertForbidden();
    }

    public function test_halaman_baru_mengarahkan_tamu_ke_login(): void
    {
        $this->get(route('pemilik.tagihan'))->assertRedirect(route('login'));
        $this->get(route('pemilik.penyewa'))->assertRedirect(route('login'));
        $this->get(route('anak-kos.tagihan'))->assertRedirect(route('login'));
    }

    public function test_menu_cari_kos_disembunyikan_untuk_pemilik(): void
    {
        $htmlPemilik = $this->actingAs($this->pemilik())->get(route('dashboard.pemilik'))->getContent();
        $this->assertStringNotContainsString('/kos"', $htmlPemilik);

        $htmlAnak = $this->actingAs($this->anakKos())->get(route('dashboard.anak-kos'))->getContent();
        $this->assertStringContainsString('/kos"', $htmlAnak);
    }

    public function test_halaman_bayar_menyebut_persetujuan_superadmin(): void
    {
        $content = $this->actingAs($this->pemilik())->get(route('langganan.bayar', 'pro'))->assertOk()->getContent();

        $this->assertStringContainsString('disetujui superadmin', $content);
        $this->assertStringNotContainsString('langsung aktif otomatis', $content);
    }
}
