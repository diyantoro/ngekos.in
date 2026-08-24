<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_dashboard_redirects_each_role_to_its_own_dashboard(): void
    {
        $cases = [
            'super_admin' => 'dashboard.super-admin',
            'pemilik' => 'dashboard.pemilik',
            'admin' => 'dashboard.admin',
            'anak_kos' => 'dashboard.anak-kos',
        ];

        foreach ($cases as $role => $expectedRoute) {
            $user = User::factory()->withRole($role)->create();

            $this->actingAs($user)
                ->get('/dashboard')
                ->assertRedirect(route($expectedRoute));

            $this->actingAs($user)
                ->get(route($expectedRoute))
                ->assertOk()
                ->assertSee(auth()->user()->nama);
        }
    }

    public function test_dashboard_for_user_without_role_falls_back_to_anak_kos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('dashboard.anak-kos'));
    }
}
