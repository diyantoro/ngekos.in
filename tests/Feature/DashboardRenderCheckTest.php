<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Penyewaan;
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
            ->assertSee('Kos Melati');
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
        $component->call('ajukanKeluar', $sewaan->id)
            ->assertSet('pesan', fn ($pesan) => str_contains($pesan, 'check-out'));

        $this->assertNotNull($sewaan->refresh()->permintaan_keluar_pada);
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

    public function test_dashboard_route_blocked_for_wrong_role(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();

        $this->actingAs($user)
            ->get(route('dashboard.admin'))
            ->assertForbidden();
    }
}
