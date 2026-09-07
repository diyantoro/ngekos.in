<?php

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class]);
        $this->withoutVite();
    }

    public function test_login_anak_kos_berhasil(): void
    {
        $component = Volt::test('pages.auth.login', ['peran' => 'anak_kos', 'peranUrl' => 'anak_kos'])
            ->set('form.email', 'anak1@ngekos.test')
            ->set('form.password', 'password');

        $component->call('login');

        $this->assertAuthenticated();
    }

    public function test_login_pemilik_berhasil(): void
    {
        $component = Volt::test('pages.auth.login', ['peran' => 'pemilik', 'peranUrl' => 'pemilik'])
            ->set('form.email', 'pemilik1@ngekos.test')
            ->set('form.password', 'password');

        $component->call('login');

        $this->assertAuthenticated();
    }

    public function test_login_password_salah_tidak_berhasil(): void
    {
        $component = Volt::test('pages.auth.login', ['peran' => 'anak_kos', 'peranUrl' => 'anak_kos'])
            ->set('form.email', 'anak1@ngekos.test')
            ->set('form.password', 'salah-password')
            ->call('login');

        $this->assertGuest();
    }

    public function test_admin_login_lewat_halaman_pencari_kos_mengarah_ke_dashboard_admin(): void
    {
        $component = Volt::test('pages.auth.login', ['peran' => 'anak_kos', 'peranUrl' => 'anak_kos'])
            ->set('form.email', 'admin.ngekos@gmail.com')
            ->set('form.password', 'Admin.ngekos123');

        $component->call('login');

        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->getRoleNames()->first());

        $response = $this->get('/dashboard/admin');
        $response->assertOk();
    }

    public function test_super_admin_login_lewat_halaman_pemilik_mengarah_ke_dashboard_super_admin(): void
    {
        $component = Volt::test('pages.auth.login', ['peran' => 'pemilik', 'peranUrl' => 'pemilik'])
            ->set('form.email', 'superadmin.ngekos@gmail.com')
            ->set('form.password', 'Superadmin.ngekos123');

        $component->call('login');

        $this->assertAuthenticated();
        $this->assertEquals('super_admin', auth()->user()->getRoleNames()->first());

        $response = $this->get('/dashboard/super-admin');
        $response->assertOk();
    }
}