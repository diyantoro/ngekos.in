<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class NewUserLoginCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_new_random_user_can_register_and_access_dashboard(): void
    {
        Volt::test('pages.auth.register')
            ->set('nama', 'Budi Santoso')
            ->set('email', 'budi.random@example.com')
            ->set('no_hp', '081234567890')
            ->set('password', 'rahasia123')
            ->set('password_confirmation', 'rahasia123')
            ->call('register')
            ->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'budi.random@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('anak_kos'));

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('dashboard.anak-kos'));

        $this->actingAs($user)
            ->get(route('dashboard.anak-kos'))
            ->assertOk()
            ->assertSee('Budi Santoso');
    }
}
