<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeSemuaHalamanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
        $this->withoutVite();
    }

    public function test_beranda_dan_katalog_publik(): void
    {
        $properti = Properti::firstOrFail();

        $this->get('/')->assertOk();
        $this->get('/kos')->assertOk();
        $this->get(route('kos.detail', $properti->id))->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/bantuan')->assertOk();
    }

    public function test_semua_halaman_pemilik(): void
    {
        $user = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $properti = Properti::where('pemilik_id', $user->id)->firstOrFail();

        $this->actingAs($user)->get(route('dashboard.pemilik'))->assertOk();
        $this->actingAs($user)->get(route('pemilik.properti'))->assertOk();
        $this->actingAs($user)->get(route('pemilik.properti.buat'))->assertOk();
        $this->actingAs($user)->get(route('pemilik.properti.ubah', $properti->id))->assertOk();
        $this->actingAs($user)->get(route('pemilik.kamar', $properti->id))->assertOk();
        $this->actingAs($user)->get(route('pemilik.pengeluaran'))->assertOk();
        $r = $this->actingAs($user)->get(route('chat.index'));
        fwrite(STDERR, "chat.index=".$r->getStatusCode().PHP_EOL);
        $r->assertOk();
        $r = $this->actingAs($user)->get(route('chat.room', $properti->id));
        fwrite(STDERR, "chat.room=".$r->getStatusCode().PHP_EOL);
        $this->assertTrue(in_array($r->getStatusCode(), [200, 302]), 'chat.room harus render atau arahkan ke daftar chat');
        $this->actingAs($user)->get(route('pengaturan'))->assertOk();
        $this->actingAs($user)->get(route('bantuan.riwayat'))->assertOk();
    }

    public function test_semua_halaman_anak_kos(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::firstOrFail();

        $this->actingAs($user)->get(route('dashboard.anak-kos'))->assertOk();
        $this->actingAs($user)->get(route('favorit'))->assertOk();
        $this->actingAs($user)->get(route('chat.index'))->assertOk();
        $this->actingAs($user)->get(route('chat.room', $properti->id))->assertOk();
        $this->actingAs($user)->get(route('chat.room.anak', ['properti' => $properti->id, 'anakKos' => $user->id]))->assertOk();
        $this->actingAs($user)->get(route('kos.detail', $properti->id))->assertOk();
        $this->actingAs($user)->get(route('pengaturan'))->assertOk();
        $this->actingAs($user)->get(route('bantuan.riwayat'))->assertOk();
    }

    public function test_semua_halaman_admin(): void
    {
        $user = User::where('email', 'admin.ngekos@gmail.com')->firstOrFail();
        $properti = Properti::firstOrFail();

        $this->actingAs($user)->get(route('dashboard.admin'))->assertOk();
        $this->actingAs($user)->get(route('pemilik.properti'))->assertOk();
        $this->actingAs($user)->get(route('pemilik.properti.buat'))->assertOk();
        $this->actingAs($user)->get(route('pemilik.properti.ubah', $properti->id))->assertOk();
        $this->actingAs($user)->get(route('pemilik.kamar', $properti->id))->assertOk();
        $this->actingAs($user)->get(route('pemilik.pengeluaran'))->assertOk();
        $this->actingAs($user)->get(route('bantuan.masuk'))->assertOk();
        $this->actingAs($user)->get(route('chat.index'))->assertOk();
        $this->actingAs($user)->get(route('pengaturan'))->assertOk();
    }

    public function test_semua_halaman_super_admin(): void
    {
        $user = User::where('email', 'superadmin.ngekos@gmail.com')->firstOrFail();

        $this->actingAs($user)->get(route('dashboard.super-admin'))->assertOk();
        $this->actingAs($user)->get(route('dashboard.admin'))->assertOk();
        $this->actingAs($user)->get(route('pengguna'))->assertOk();
        $this->actingAs($user)->get(route('pemilik.properti'))->assertOk();
        $this->actingAs($user)->get(route('bantuan.masuk'))->assertOk();
        $this->actingAs($user)->get(route('pengaturan'))->assertOk();
    }
}