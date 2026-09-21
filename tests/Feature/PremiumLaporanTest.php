<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Properti;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PremiumLaporanTest extends TestCase
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

    private function langganan(User $user, string $plan, string $status = 'active', ?string $expiresAt = null): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'status' => $status,
            'starts_at' => now()->subDay(),
            'expires_at' => $expiresAt,
        ]);
    }

    private function buatProperti(User $pemilik, string $nama = 'Kos A'): Properti
    {
        return Properti::create([
            'pemilik_id' => $pemilik->id,
            'nama' => $nama,
            'status' => 'aktif',
        ]);
    }

    private function buatKamar(Properti $properti, string $nama = 'A1'): Kamar
    {
        return Kamar::create([
            'properti_id' => $properti->id,
            'nama' => $nama,
            'kapasitas' => 1,
            'harga_sewa_bulanan' => 1000000,
            'status' => 'tersedia',
        ]);
    }

    public function test_halaman_laporan_premium_free_menampilkan_lock(): void
    {
        $user = $this->pemilik();
        $this->buatProperti($user);
        $this->buatKamar($this->buatProperti($user, 'Kos B'));

        $content = $this->actingAs($user)->get(route('pemilik.laporan'))->getContent();

        $this->assertStringContainsString('Laporan Premium', $content);
        $this->assertStringContainsString('Tersedia di paket PRO', $content);
        $this->assertStringNotContainsString('Ekspor Laporan Premium', $content);
        $this->assertStringNotContainsString('Kos Pemasukan Terbesar', $content);
        $this->assertStringNotContainsString('Siapa yang belum bayar', $content);
    }

    public function test_halaman_laporan_premium_pro_menampilkan_data(): void
    {
        $user = $this->pemilik(['email' => 'pro@test.id']);
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());
        $this->buatProperti($user);

        $content = $this->actingAs($user)->get(route('pemilik.laporan'))->getContent();

        $this->assertStringNotContainsString('Tersedia di paket PRO', $content);
        $this->assertStringContainsString('Ekspor Laporan Premium', $content);
        $this->assertStringContainsString('Kos Pemasukan Terbesar', $content);
        $this->assertStringContainsString('Naik Turun Tiap Bulan', $content);
    }

    public function test_ekspor_pdf_premium_ditolak_free_dengan_403(): void
    {
        $user = $this->pemilik();

        $this->actingAs($user)->get(route('pemilik.laporan.pdf'))->assertForbidden();
    }

    public function test_ekspor_excel_premium_ditolak_free_dengan_403(): void
    {
        $user = $this->pemilik();

        $this->actingAs($user)->get(route('pemilik.laporan.excel'))->assertForbidden();
    }

    public function test_ekspor_pdf_premium_diizinkan_pro(): void
    {
        $user = $this->pemilik(['email' => 'pro2@test.id']);
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());
        $bulan = now()->format('Y-m');

        $response = $this->actingAs($user)
            ->get(route('pemilik.laporan.pdf', ['bulan' => $bulan]))
            ->assertOk()
            ->assertHeader('Content-Disposition', "attachment; filename=laporan-premium-{$bulan}.pdf");

        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type') ?? '');
    }

    public function test_ekspor_excel_premium_diizinkan_pro(): void
    {
        $user = $this->pemilik(['email' => 'pro3@test.id']);
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());
        $bulan = now()->format('Y-m');

        $response = $this->actingAs($user)
            ->get(route('pemilik.laporan.excel', ['bulan' => $bulan]))
            ->assertOk()
            ->assertHeader('Content-Disposition', "attachment; filename=laporan-premium-{$bulan}.xlsx");

        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument',
            $response->headers->get('Content-Type') ?? ''
        );
    }

    public function test_ekspor_premium_ditolak_setelah_expired(): void
    {
        $user = $this->pemilik(['email' => 'exp@test.id']);
        $this->langganan($user, 'pro', 'active', now()->subDay()->toDateTimeString());

        $this->actingAs($user)->get(route('pemilik.laporan.pdf'))->assertForbidden();
    }

    public function test_ekspor_premium_diizinkan_admin_exempt(): void
    {
        $admin = User::factory()->create(['email' => 'admin@test.id']);
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('pemilik.laporan.pdf'))->assertOk();
    }

    public function test_ekspor_premium_bulan_tidak_valid_mengembalikan_422(): void
    {
        $user = $this->pemilik(['email' => 'pro4@test.id']);
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());

        $this->actingAs($user)
            ->from(route('pemilik.laporan'))
            ->get(route('pemilik.laporan.pdf', ['bulan' => 'bukan-bulan']))
            ->assertStatus(422);
    }

    public function test_halaman_laporan_premium_ditolak_anak_kos(): void
    {
        $anakKos = User::factory()->create(['email' => 'anak@test.id']);
        $anakKos->assignRole('anak_kos');

        $this->actingAs($anakKos)->get(route('pemilik.laporan'))->assertForbidden();
    }

    public function test_service_store_mencatat_riwayat_dan_plan_terbaru(): void
    {
        $user = $this->pemilik();

        SubscriptionService::store($user->id, [
            'plan' => 'business',
            'status' => 'active',
            'starts_at' => now()->toDateTimeString(),
            'expires_at' => now()->addMonth()->toDateTimeString(),
        ]);

        $this->assertSame('business', SubscriptionService::getPlan($user));
        $this->assertSame(1, SubscriptionService::history($user)->count());

        SubscriptionService::store($user->id, [
            'plan' => 'pro',
            'status' => 'active',
            'starts_at' => now()->toDateTimeString(),
            'expires_at' => now()->addMonth()->toDateTimeString(),
        ]);

        $this->assertSame('pro', SubscriptionService::getPlan($user));
        $this->assertSame(2, SubscriptionService::history($user)->count());
        $this->assertSame('pro', SubscriptionService::history($user)->sortByDesc('id')->first()->plan);
        $this->assertSame(['business', 'pro'], SubscriptionService::history($user)->pluck('plan')->sort()->values()->all());
    }

    public function test_service_store_menolak_plan_tidak_valid(): void
    {
        $user = $this->pemilik();

        $this->assertThrows(
            fn () => SubscriptionService::store($user->id, ['plan' => 'platinum', 'status' => 'active']),
            \InvalidArgumentException::class,
        );
        $this->assertSame(0, SubscriptionService::history($user)->count());
    }

    public function test_service_perpanjang_dan_akhiri(): void
    {
        $user = $this->pemilik(['email' => 'svc@test.id']);
        $subscription = $this->langganan($user, 'pro', 'expired', now()->subDay()->toDateTimeString());

        SubscriptionService::perpanjang($subscription, 30);

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->expires_at->isFuture());
        $this->assertTrue($subscription->isActive());

        SubscriptionService::akhiri($subscription);
        $subscription->refresh();
        $this->assertSame('expired', $subscription->status);
        $this->assertFalse($subscription->isActive());
    }

    public function test_api_super_admin_store_langganan_via_service(): void
    {
        $admin = User::factory()->create(['email' => 'su@test.id']);
        $admin->assignRole('super_admin');
        $token = $admin->createToken('test')->plainTextToken;

        $user = $this->pemilik(['email' => 'target@test.id']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/subscriptions', [
                'user_id' => $user->id,
                'plan' => 'pro',
                'status' => 'active',
                'starts_at' => now()->toDateTimeString(),
                'expires_at' => now()->addMonth()->toDateTimeString(),
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('subscription.plan', 'pro');
        $this->assertSame('pro', SubscriptionService::getPlan($user));

        $invalid = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/subscriptions', [
                'user_id' => $user->id,
                'plan' => 'platinum',
                'status' => 'active',
            ]);

        $invalid->assertStatus(422);
        $this->assertSame(1, SubscriptionService::history($user)->count());
    }

    public function test_trial_dibuat_sekali_dan_tidak_dobel(): void
    {
        $user = $this->pemilik(['email' => 'trial@test.id']);

        $pertama = SubscriptionService::mulaiTrialFree($user);
        $kedua = SubscriptionService::mulaiTrialFree($user);

        $this->assertNotNull($pertama);
        $this->assertTrue($pertama->is_trial);
        $this->assertNull($kedua);
        $this->assertTrue(SubscriptionService::pernahTrial($user));
        $this->assertNotNull(SubscriptionService::sisaTrialHari($user));
        $this->assertFalse(SubscriptionService::trialExpired($user));
        $this->assertSame('basic', SubscriptionService::reportTier($user));
        $this->assertSame(3, SubscriptionService::maxPeriode($user));
        $this->assertSame(3, SubscriptionService::clampPeriode($user, 24));
        $this->assertTrue(SubscriptionService::canExportPdf($user));
        $this->assertFalse(SubscriptionService::canExportExcel($user));
    }

    public function test_trial_habis_maka_limit_ditolak_walau_belum_penuh(): void
    {
        $user = $this->pemilik(['email' => 'trialhabis@test.id']);
        SubscriptionService::mulaiTrialFree($user);
        Subscription::where('user_id', $user->id)->update(['expires_at' => now()->subDay()]);

        $this->assertTrue(SubscriptionService::trialExpired($user));

        $cek = SubscriptionService::checkLimit($user, 'property');

        $this->assertFalse($cek['allowed']);
        $this->assertSame('pro', $cek['required_plan']);
    }

    public function test_user_lama_tanpa_subscription_dianggap_habis(): void
    {
        $user = $this->pemilik(['email' => 'lama@test.id']);

        $this->assertTrue(SubscriptionService::trialExpired($user));
        $this->assertFalse(SubscriptionService::canExportExcel($user));
        $this->assertSame('basic', SubscriptionService::reportTier($user));
    }

    public function test_pro_clamp_12_dan_business_24(): void
    {
        $pro = $this->pemilik(['email' => 'proclamp@test.id']);
        $this->langganan($pro, 'pro', 'active', now()->addMonth()->toDateTimeString());

        $biz = $this->pemilik(['email' => 'bizclamp@test.id']);
        $this->langganan($biz, 'business', 'active', now()->addMonth()->toDateTimeString());

        $this->assertSame('pro', SubscriptionService::reportTier($pro));
        $this->assertSame('business', SubscriptionService::reportTier($biz));
        $this->assertSame(12, SubscriptionService::maxPeriode($pro));
        $this->assertSame(24, SubscriptionService::maxPeriode($biz));
        $this->assertSame(12, SubscriptionService::clampPeriode($pro, 24));
        $this->assertSame(24, SubscriptionService::clampPeriode($biz, 99));
    }

    public function test_excel_business_7_sheet_dan_pro_5_sheet(): void
    {
        $pro = $this->pemilik(['email' => 'prosheet@test.id']);
        $this->langganan($pro, 'pro', 'active', now()->addMonth()->toDateTimeString());

        $biz = $this->pemilik(['email' => 'bizsheet@test.id']);
        $this->langganan($biz, 'business', 'active', now()->addMonth()->toDateTimeString());

        $bulan = now()->format('Y-m');

        $proXls = $this->actingAs($pro)->get(route('pemilik.laporan.excel', ['bulan' => $bulan]))->assertOk()->streamedContent();
        $proTmp = tempnam(sys_get_temp_dir(), 'pro').'.xlsx';
        file_put_contents($proTmp, $proXls);
        $proBook = \PhpOffice\PhpSpreadsheet\IOFactory::load($proTmp);
        $this->assertSame(5, $proBook->getSheetCount());
        @unlink($proTmp);

        $bizXls = $this->actingAs($biz)->get(route('pemilik.laporan.excel', ['bulan' => $bulan]))->assertOk()->streamedContent();
        $bizTmp = tempnam(sys_get_temp_dir(), 'biz').'.xlsx';
        file_put_contents($bizTmp, $bizXls);
        $bizBook = \PhpOffice\PhpSpreadsheet\IOFactory::load($bizTmp);
        $this->assertSame(7, $bizBook->getSheetCount());

        $titles = [];
        foreach ($bizBook->getAllSheets() as $s) {
            $titles[] = $s->getTitle();
        }
        $this->assertContains('Rincian Tiap Kos', $titles);
        $this->assertContains('Daftar Transaksi Detail', $titles);
    }

    public function test_admin_exempt_tetap_lolos(): void
    {
        $admin = User::factory()->create(['email' => 'exempt@test.id']);
        $admin->assignRole('admin');

        $this->assertFalse(SubscriptionService::trialExpired($admin));
        $this->assertTrue(SubscriptionService::canExportExcel($admin));
        $this->assertSame('business', SubscriptionService::reportTier($admin));
        $this->assertSame(24, SubscriptionService::maxPeriode($admin));
        $this->actingAs($admin)->get(route('pemilik.laporan.pdf'))->assertOk();
    }
}