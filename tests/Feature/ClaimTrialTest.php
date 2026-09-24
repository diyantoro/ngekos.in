<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ClaimTrialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class]);
    }

    private function pemilik(array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->assignRole('pemilik');

        return $user->refresh();
    }

    public function test_pemilik_baru_free_murni_belum_trial_tapi_bisa_klaim(): void
    {
        $user = $this->pemilik(['email' => 'baru@test.id']);

        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertFalse(SubscriptionService::pernahTrial($user));
        $this->assertNull(SubscriptionService::trialAktif($user));
        $this->assertTrue(SubscriptionService::bisaKlaimTrial($user));
        $this->assertTrue(SubscriptionService::trialExpired($user));
        // Free murni tetap boleh tambah kos pertama tanpa klaim trial.
        $this->assertTrue(SubscriptionService::checkLimit($user, 'property')['allowed']);
        $this->assertTrue(SubscriptionService::checkLimit($user, 'room')['allowed']);
    }

    public function test_klaim_trial_sekali_lalu_dobel_ditolak(): void
    {
        $user = $this->pemilik(['email' => 'klaim@test.id']);

        $pertama = SubscriptionService::klaimTrialFree($user);

        $this->assertNotNull($pertama);
        $this->assertTrue($pertama->is_trial);
        $this->assertFalse(SubscriptionService::bisaKlaimTrial($user));

        $kedua = SubscriptionService::klaimTrialFree($user);

        $this->assertNull($kedua);
        $this->assertTrue(SubscriptionService::pernahTrial($user));
    }

    public function test_trial_klaim_membuka_fitur_pro(): void
    {
        $user = $this->pemilik(['email' => 'pro-trial@test.id']);

        SubscriptionService::klaimTrialFree($user);

        $this->assertTrue(SubscriptionService::hasFeature($user, 'advanced_analytics'));
        $this->assertTrue(SubscriptionService::hasFeature($user, 'advanced_report'));
        $this->assertTrue(SubscriptionService::hasFeature($user, 'export_report'));
        $this->assertSame('pro', SubscriptionService::reportTier($user));
        $this->assertSame(12, SubscriptionService::maxPeriode($user));
        $this->assertFalse(SubscriptionService::perluWatermark($user));
        $this->assertFalse(SubscriptionService::laporanDikunci($user));
    }

    public function test_pro_aktif_tidak_bisa_klaim(): void
    {
        $user = $this->pemilik(['email' => 'sudahpro@test.id']);

        SubscriptionService::store($user->id, [
            'plan' => 'pro',
            'status' => 'active',
            'starts_at' => now()->toDateTimeString(),
            'expires_at' => now()->addMonth()->toDateTimeString(),
        ]);

        $this->assertFalse(SubscriptionService::bisaKlaimTrial($user));
        $this->assertNull(SubscriptionService::klaimTrialFree($user));
    }

    public function test_anak_kos_tidak_bisa_klaim(): void
    {
        $user = User::factory()->create(['email' => 'anak@test.id']);
        $user->assignRole('anak_kos');

        $this->assertFalse(SubscriptionService::bisaKlaimTrial($user->refresh()));
    }

    public function test_tombol_klaim_di_halaman_subscription(): void
    {
        $user = $this->pemilik(['email' => 'volt@test.id']);

        $this->actingAs($user);

        Volt::test('pages.langganan.subscription')
            ->call('klaimTrial')
            ->assertHasNoErrors();

        $this->assertTrue(SubscriptionService::pernahTrial($user->refresh()));
        $this->assertNotNull(SubscriptionService::trialAktif($user->refresh()));
    }

    public function test_tombol_klaim_tampil_di_semua_halaman_pemilik(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('pemilik');
        $this->actingAs($user->refresh());

        foreach ([
            route('langganan.subscription'),
            route('langganan.plans'),
            route('pemilik.grafik'),
            route('pemilik.laporan'),
            route('dashboard.pemilik'),
        ] as $url) {
            $this->get($url)->assertSee('Klaim Trial', false);
        }
    }

    public function test_api_klaim_trial(): void
    {
        $user = $this->pemilik(['email' => 'api@test.id']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/subscription/claim-trial')
            ->assertStatus(201)
            ->assertJsonPath('subscription.is_trial', true);

        // Klaim kedua ditolak.
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/subscription/claim-trial')
            ->assertStatus(422);
    }
}
