<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi penentuan paket: baris langganan terbaru yang sudah mulai
 * menentukan paket berjalan. Baris lama yang masih "valid" tidak boleh
 * menghidupkan kembali akses PRO/BUSINESS setelah downgrade.
 */
class SubscriptionPlanRegresiTest extends TestCase
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

    private function langganan(User $user, string $plan, string $status = 'active', ?string $expiresAt = null, ?string $startsAt = null): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'status' => $status,
            'starts_at' => $startsAt ?? now()->subDay(),
            'expires_at' => $expiresAt,
        ]);
    }

    public function test_langganan_active_yang_kedaluwarsa_tidak_memberi_akses_berbayar(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'pro', 'active', now()->subDay()->toDateTimeString());

        $this->assertNull(SubscriptionService::getSubscriptionAktif($user));
        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertFalse(SubscriptionService::isPremium($user));
        $this->assertFalse(SubscriptionService::hasFeature($user, 'export_report'));
    }

    public function test_langganan_cancelled_belum_kedaluwarsa_tidak_memberi_akses_berbayar(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'business', 'cancelled', now()->addMonth()->toDateTimeString());

        $this->assertNull(SubscriptionService::getSubscriptionAktif($user));
        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertFalse(SubscriptionService::isPremium($user));
        $this->assertFalse(SubscriptionService::hasFeature($user, 'laporan_24_bulan'));
    }

    public function test_tanpa_langganan_fallback_free(): void
    {
        $user = $this->pemilik();

        $this->assertNull(SubscriptionService::getSubscriptionAktif($user));
        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertFalse(SubscriptionService::isPremium($user));
    }

    public function test_baris_lama_business_tidak_menghidupkan_akses_setelah_cancelled(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'business', 'active', now()->addMonth()->toDateTimeString());
        $this->langganan($user, 'pro', 'cancelled', now()->addMonth()->toDateTimeString());

        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertFalse(SubscriptionService::isPremium($user));
    }

    public function test_baris_lama_business_tidak_menghidupkan_akses_setelah_expired(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'business', 'active', now()->addMonth()->toDateTimeString());
        $this->langganan($user, 'pro', 'active', now()->subDay()->toDateTimeString());

        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertFalse(SubscriptionService::isPremium($user));
    }

    public function test_limit_dan_usage_konsisten_dengan_plan_free_hasil_downgrade(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'business', 'active', now()->addMonth()->toDateTimeString());

        for ($i = 1; $i <= 6; $i++) {
            Properti::create(['pemilik_id' => $user->id, 'nama' => 'Kos '.$i, 'status' => 'aktif']);
        }

        $this->langganan($user, 'pro', 'cancelled', now()->addMonth()->toDateTimeString());
        $this->langganan($user, 'pro', 'active', now()->subDay()->toDateTimeString());

        $this->assertSame('free', SubscriptionService::getPlan($user));
        // Data lama tidak dihapus.
        $this->assertSame(6, SubscriptionService::usage($user, 'property'));

        $cek = SubscriptionService::checkLimit($user, 'property');
        $this->assertSame('free', $cek['plan']);
        $this->assertSame(6, $cek['used']);
        $this->assertFalse($cek['allowed']);

        $this->assertDatabaseCount('propertis', 6);
    }

    public function test_baris_terjadwal_masa_depan_diabaikan_dan_tidak_memberi_akses(): void
    {
        $user = $this->pemilik();
        SubscriptionService::mulaiTrialFree($user);
        $this->langganan(
            $user,
            'pro',
            'active',
            now()->addMonths(2)->toDateTimeString(),
            now()->addDay()->toDateTimeString()
        );

        // Paket tetap free (trial), bukan pro dari baris terjadwal.
        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertNotNull(SubscriptionService::getSubscriptionTerjadwal($user));
        $this->assertSame('pro', SubscriptionService::getSubscriptionTerjadwal($user)->plan);
    }

    public function test_api_batas_tercapai_tetap_403_meski_fasilitas_kosong(): void
    {
        $user = $this->pemilik();
        SubscriptionService::mulaiTrialFree($user);
        Properti::create(['pemilik_id' => $user->id, 'nama' => 'Kos Pertama', 'status' => 'aktif']);
        $token = $user->createToken('test')->plainTextToken;

        // Tanpa fasilitas: batas tercapai => 403 didahulukan, bukan 422.
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/pemilik/properti', ['nama' => 'Kos Kedua', 'status' => 'aktif']);

        $response->assertStatus(403);
        $response->assertJsonPath('required_plan', null);
        $this->assertDatabaseMissing('propertis', ['nama' => 'Kos Kedua']);
        $this->assertSame(1, SubscriptionService::usage($user, 'property'));
    }

    public function test_api_batas_belum_tercapai_fasilitas_kosong_tetap_422(): void
    {
        $user = $this->pemilik();
        SubscriptionService::mulaiTrialFree($user);
        $token = $user->createToken('test')->plainTextToken;

        // Kuota masih tersedia: validasi normal berjalan (fasilitas wajib).
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/pemilik/properti', ['nama' => 'Kos Pertama', 'status' => 'aktif']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('fasilitas');
        $this->assertDatabaseMissing('propertis', ['nama' => 'Kos Pertama']);
    }
}
