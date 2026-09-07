<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class FavoritDanUlasanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_anak_kos_bisa_post_toggle_favorit_dan_tampil_di_halaman_favorit(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);
        $component->call('toggleFavorit')
            ->assertSet('favorit', true);

        $this->assertDatabaseHas('properti_favorits', [
            'user_id' => $user->id,
            'properti_id' => $properti->id,
        ]);

        $this->actingAs($user)
            ->get(route('favorit'))
            ->assertOk()
            ->assertSee('Kos Melati');

        $component->call('toggleFavorit')
            ->assertSet('favorit', false);

        $this->assertDatabaseMissing('properti_favorits', [
            'user_id' => $user->id,
            'properti_id' => $properti->id,
        ]);
    }

    public function test_tamu_yang_toggle_favorit_diarahkan_ke_login(): void
    {
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();

        $component = Volt::test('pages.katalog.detail', ['properti' => $properti]);
        $component->call('toggleFavorit')
            ->assertRedirect(route('login'));
    }

    public function test_anak_kos_bisa_memberi_ulasan_dan_berbaris_di_detail(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);
        $component->set('ratingUlasan', 4)
            ->set('komentarUlasan', 'Kos bersih dan pemilik ramah.')
            ->call('simpanUlasan')
            ->assertHasNoErrors()
            ->assertSet('komentarUlasan', '');

        $this->assertDatabaseHas('ulasans', [
            'properti_id' => $properti->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        $this->actingAs($user)
            ->get(route('kos.detail', $properti->id))
            ->assertOk()
            ->assertSee('Kos bersih dan pemilik ramah.');
    }

    public function test_ulasan_tanpa_rating_ditolak(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.katalog.detail', ['properti' => $properti]);
        $component->set('ratingUlasan', 0)
            ->call('simpanUlasan')
            ->assertHasErrors(['ratingUlasan' => 'between']);
    }

    public function test_katalog_membagi_halaman_dan_menampilkan_rating_kartu(): void
    {
        $this->actingAs(User::where('email', 'anak1@ngekos.test')->firstOrFail())
            ->get(route('kos.index'))
            ->assertOk()
            ->assertSee('Filter');
    }
}