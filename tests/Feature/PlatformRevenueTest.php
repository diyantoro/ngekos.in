<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class]);
    }

    private function pemilik(string $email): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole('pemilik');

        return $user->refresh();
    }

    public function test_revenue_menghitung_yang_approved_saja(): void
    {
        $a = $this->pemilik('a@test.id');
        $b = $this->pemilik('b@test.id');

        $reqA = SubscriptionService::requestUpgrade($a, 'pro', null, ['amount' => 49000]);
        SubscriptionService::approveRequest($reqA);

        // Pending & rejected tidak dihitung.
        SubscriptionService::requestUpgrade($b, 'business', null, ['amount' => 99000]);
        $reqTolak = SubscriptionRequest::where('user_id', $b->id)->firstOrFail();
        SubscriptionService::rejectRequest($reqTolak);

        $this->assertSame(49000, SubscriptionService::platformRevenue());
        $this->assertSame(1, SubscriptionService::jumlahUpgradeApproved());
    }

    public function test_konversi_menghitung_premium_aktif_bukan_trial(): void
    {
        $a = $this->pemilik('a@test.id');
        $b = $this->pemilik('b@test.id');
        $c = $this->pemilik('c@test.id');

        // A upgrade PRO via flow resmi.
        $req = SubscriptionService::requestUpgrade($a, 'pro', null, ['amount' => 49000]);
        SubscriptionService::approveRequest($req);

        // C hanya klaim trial Free — bukan premium.
        SubscriptionService::klaimTrialFree($c);

        $konversi = SubscriptionService::konversiPremium();

        $this->assertSame(3, $konversi['total_pemilik']);
        $this->assertSame(1, $konversi['premium_aktif']);
        $this->assertEquals(33.3, $konversi['persen']);
    }

    public function test_langganan_expired_tidak_dihitung_premium(): void
    {
        $a = $this->pemilik('a@test.id');

        Subscription::create([
            'user_id' => $a->id,
            'plan' => 'pro',
            'status' => 'active',
            'starts_at' => now()->subMonths(2),
            'expires_at' => now()->subDay(),
        ]);

        $konversi = SubscriptionService::konversiPremium();

        $this->assertSame(0, $konversi['premium_aktif']);
        $this->assertSame(0, SubscriptionService::platformRevenue());
    }

    public function test_halaman_super_admin_menampilkan_angka(): void
    {
        $admin = User::factory()->create(['email' => 'su@test.id']);
        $admin->assignRole('super_admin');

        $a = $this->pemilik('a@test.id');
        $req = SubscriptionService::requestUpgrade($a, 'pro', null, ['amount' => 49000]);
        SubscriptionService::approveRequest($req);

        $content = $this->actingAs($admin)->get(route('dashboard.super-admin'))->getContent();

        $this->assertStringContainsString('Platform Revenue', $content);
        $this->assertStringContainsString('Rp49.000', $content);
        $this->assertStringContainsString('Premium Conversion', $content);
        $this->assertStringContainsString('1 dari 1 pemilik memakai paket premium', $content);
    }
}
