<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
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

    public function test_admin_dashboard_renders_component_and_actions_work(): void
    {
        $user = User::where('email', 'admin.ngekos@gmail.com')->first();

        $this->actingAs($user)
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSeeVolt('pages.dashboard.admin');

        $component = Volt::test('pages.dashboard.admin');
        $component->call('setujuiBooking', Booking::where('status', 'menunggu')->first()->id);
        $component->assertHasNoErrors();
    }

    public function test_anak_kos_dashboard_renders_component_and_actions_work(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();

        $this->actingAs($user)
            ->get(route('dashboard.anak-kos'))
            ->assertOk()
            ->assertSeeVolt('pages.dashboard.anak-kos')
            ->assertSee('Cari Kamar');
    }

    public function test_anak_kos_can_book_available_kamar(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();
        $kamar = Kamar::where('status', 'tersedia')
            ->whereDoesntHave('bookings', fn ($q) => $q->where('anak_kos_id', $user->id)->whereIn('status', ['menunggu', 'disetujui']))
            ->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.dashboard.anak-kos');

        // pesanKamar membuka modal booking, konfirmasiPesan mengajukan booking.
        $component->call('pesanKamar', $kamar->id)
            ->assertSet('modalKamarId', $kamar->id);

        $component->call('konfirmasiPesan')
            ->assertHasNoErrors()
            ->assertSet('modalKamarId', null);

        $this->assertNotNull($component->get('pesan'));

        $this->assertDatabaseHas('bookings', [
            'anak_kos_id' => $user->id,
            'kamar_id' => $kamar->id,
            'status' => 'menunggu',
        ]);

        $booking = Booking::where('anak_kos_id', $user->id)->where('kamar_id', $kamar->id)->firstOrFail();
        $component->call('batalBooking', $booking->id)
            ->assertSet('pesan', 'Booking dibatalkan.');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'batal',
        ]);
    }

    public function test_anak_kos_cannot_book_same_kamar_twice(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();
        $kamar = Kamar::where('status', 'tersedia')->firstOrFail();

        $component = Volt::actingAs($user)->test('pages.dashboard.anak-kos');

        $component->call('pesanKamar', $kamar->id);
        $component->call('konfirmasiPesan');
        $this->assertDatabaseHas('bookings', [
            'anak_kos_id' => $user->id,
            'kamar_id' => $kamar->id,
            'status' => 'menunggu',
        ]);

        $component->call('pesanKamar', $kamar->id);
        $component->call('konfirmasiPesan')
            ->assertHasNoErrors()
            ->assertSet('galat', 'Kamu sudah memiliki booking aktif untuk kamar ini.');

        $this->assertSame(1, Booking::where('anak_kos_id', $user->id)->where('kamar_id', $kamar->id)->whereIn('status', ['menunggu', 'disetujui'])->count());
    }

    public function test_dashboard_route_blocked_for_wrong_role(): void
    {
        $user = User::where('email', 'anak1@ngekos.test')->first();

        $this->actingAs($user)
            ->get(route('dashboard.admin'))
            ->assertForbidden();
    }
}
