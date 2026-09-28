<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatRoleGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_admin_dan_super_admin_ditolak_masuk_halaman_chat(): void
    {
        $properti = Properti::firstOrFail();

        foreach (['admin.ngekos@gmail.com', 'superadmin.ngekos@gmail.com'] as $email) {
            $user = User::where('email', $email)->firstOrFail();

            $this->actingAs($user)->get(route('chat.index'))->assertForbidden();
            $this->actingAs($user)->get(route('chat.room', $properti->id))->assertForbidden();
        }
    }

    public function test_anak_kos_dan_pemilik_tetap_bisa_membuka_chat(): void
    {
        $anak = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        $this->actingAs($anak)->get(route('chat.index'))->assertOk();
        $this->actingAs($pemilik)->get(route('chat.index'))->assertOk();
    }

    public function test_link_pesan_tidak_tampil_untuk_admin_tapi_tampil_untuk_pemilik(): void
    {
        $admin = User::where('email', 'admin.ngekos@gmail.com')->firstOrFail();
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        // href="/chat" hanya muncul sebagai tautan menu Pesan (sudah digate per role).
        $this->actingAs($admin)->get(route('dashboard.admin'))->assertOk()->assertDontSee('/chat"');
        $this->actingAs($pemilik)->get(route('dashboard.pemilik'))->assertOk()->assertSee('/chat"', false);
    }
}
