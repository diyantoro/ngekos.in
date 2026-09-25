<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Pengaturan;
use App\Models\Properti;
use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Notifications\LanggananDibayarNotification;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SubscriptionTest extends TestCase
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

    public function test_user_tanpa_subscription_dianggap_free(): void
    {
        $user = $this->pemilik();

        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertFalse(SubscriptionService::isPremium($user));
    }

    public function test_plan_free_pro_business_terbaca(): void
    {
        $free = $this->pemilik(['email' => 'free@test.id']);
        $pro = $this->pemilik(['email' => 'pro@test.id']);
        $business = $this->pemilik(['email' => 'biz@test.id']);

        $this->langganan($free, 'free');
        $this->langganan($pro, 'pro', 'active', now()->addMonth()->toDateTimeString());
        $this->langganan($business, 'business', 'active', now()->addMonth()->toDateTimeString());

        $this->assertSame('free', SubscriptionService::getPlan($free));
        $this->assertSame('pro', SubscriptionService::getPlan($pro));
        $this->assertSame('business', SubscriptionService::getPlan($business));
        $this->assertTrue(SubscriptionService::isPremium($pro));
        $this->assertTrue(SubscriptionService::isPremium($business));
    }

    public function test_feature_checking(): void
    {
        $free = $this->pemilik(['email' => 'free@test.id']);
        $pro = $this->pemilik(['email' => 'pro@test.id']);
        $business = $this->pemilik(['email' => 'biz@test.id']);

        $this->langganan($pro, 'pro', 'active', now()->addMonth()->toDateTimeString());
        $this->langganan($business, 'business', 'active', now()->addMonth()->toDateTimeString());

        $this->assertFalse(SubscriptionService::hasFeature($free, 'export_report'));
        $this->assertTrue(SubscriptionService::hasFeature($pro, 'export_report'));
        $this->assertTrue(SubscriptionService::hasFeature($business, 'export_report'));
        $this->assertTrue(SubscriptionService::hasFeature($business, 'unlimited_property'));
        $this->assertTrue(SubscriptionService::hasFeature($pro, 'basic_property'));
    }

    public function test_free_max_1_property(): void
    {
        $user = $this->pemilik();
        SubscriptionService::mulaiTrialFree($user);
        $this->buatProperti($user);

        $cek = SubscriptionService::checkLimit($user, 'property');

        $this->assertFalse($cek['allowed']);
        $this->assertSame('pro', $cek['required_plan']);
    }

    public function test_free_max_10_room(): void
    {
        $user = $this->pemilik();
        SubscriptionService::mulaiTrialFree($user);
        $properti = $this->buatProperti($user);

        for ($i = 1; $i <= 10; $i++) {
            $this->buatKamar($properti, 'K'.$i);
        }

        $cek = SubscriptionService::checkLimit($user, 'room');

        $this->assertFalse($cek['allowed']);
        $this->assertSame('pro', $cek['required_plan']);
    }

    public function test_trial_dibuat_sekali_dan_tidak_dobel(): void
    {
        $user = $this->pemilik(['email' => 'trialx@test.id']);

        $this->assertNotNull(SubscriptionService::mulaiTrialFree($user));
        $this->assertNull(SubscriptionService::mulaiTrialFree($user));
        $this->assertTrue(SubscriptionService::pernahTrial($user));
        $this->assertFalse(SubscriptionService::trialExpired($user));
        $this->assertSame(7, SubscriptionService::trialDays());
    }

    public function test_trial_habis_maka_tambah_tetap_ikut_batas_free(): void
    {
        $user = $this->pemilik(['email' => 'trialhabisx@test.id']);
        SubscriptionService::mulaiTrialFree($user);
        Subscription::where('user_id', $user->id)->update(['expires_at' => now()->subDay()]);

        $this->assertTrue(SubscriptionService::trialExpired($user));
        // Free murni tetap boleh tambah sampai batas paket walau trial habis.
        $this->assertTrue(SubscriptionService::checkLimit($user, 'property')['allowed']);
        $this->assertTrue(SubscriptionService::checkLimit($user, 'room')['allowed']);
        // Laporan premium tetap dikunci.
        $this->assertTrue(SubscriptionService::laporanDikunci($user));
    }

    public function test_grafik_free_partial_dan_lock_laporan(): void
    {
        $user = $this->pemilik(['email' => 'grafikfree@test.id']);
        SubscriptionService::mulaiTrialFree($user);

        $content = $this->actingAs($user)->get(route('pemilik.grafik'))->getContent();

        $this->assertStringContainsString('Uang Masuk vs Uang Keluar', $content);
        $this->assertStringContainsString('Siapa yang belum bayar', $content);
        $this->assertStringContainsString('Kos Pemasukan Terbesar', $content);
        $this->assertStringContainsString('Tren Transaksi', $content);
        $this->assertStringContainsString(route('pemilik.rekap.excel'), $content);
        $this->assertStringNotContainsString('Excel · PRO', $content);

        // Trial klaim = PRO penuh: laporan premium ikut terbuka.
        $laporan = $this->actingAs($user)->get(route('pemilik.laporan'))->getContent();
        $this->assertStringNotContainsString('Tersedia di paket PRO', $laporan);
    }

    public function test_pro_max_5_property_dan_100_room(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());

        $this->assertSame(5, SubscriptionService::limitFor('pro', 'property'));
        $this->assertSame(100, SubscriptionService::limitFor('pro', 'room'));

        $properti = $this->buatProperti($user);
        $cekAwal = SubscriptionService::checkLimit($user, 'property');
        $this->assertTrue($cekAwal['allowed']);

        for ($i = 2; $i <= 5; $i++) {
            $this->buatProperti($user, 'Kos '.$i);
        }

        $cekPenuh = SubscriptionService::checkLimit($user, 'property');
        $this->assertFalse($cekPenuh['allowed']);
        $this->assertSame('business', $cekPenuh['required_plan']);
        $this->assertSame(5, $properti->fresh() ? SubscriptionService::usage($user, 'property') : 0);
    }

    public function test_expired_kembali_ke_free_tanpa_hapus_data(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'pro', 'active', now()->subDay()->toDateTimeString());

        $this->assertSame('free', SubscriptionService::getPlan($user));

        $properti = $this->buatProperti($user);
        $kamar = $this->buatKamar($properti);

        $propertiBaru = $this->buatProperti($user, 'Kos Kedua');
        $this->assertDatabaseHas('propertis', ['id' => $properti->id]);
        $this->assertDatabaseHas('propertis', ['id' => $propertiBaru->id]);
        $this->assertDatabaseHas('kamars', ['id' => $kamar->id]);

        $cek = SubscriptionService::checkLimit($user, 'property');
        $this->assertFalse($cek['allowed']);

        $this->assertTrue($properti->update(['nama' => 'Kos A Updated']));
        $properti->delete();
        $this->assertSoftDeleted('propertis', ['id' => $properti->id]);
    }

    public function test_api_tolak_property_kedua_dengan_403(): void
    {
        $user = $this->pemilik();
        $token = $user->createToken('test')->plainTextToken;
        $this->buatProperti($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/pemilik/properti', ['nama' => 'Kos Kedua', 'status' => 'aktif']);

        $response->assertStatus(403);
        $response->assertJsonPath('required_plan', 'pro');
        $this->assertDatabaseMissing('propertis', ['nama' => 'Kos Kedua']);
    }

    public function test_api_tolak_kamar_melebihi_limit_dengan_403(): void
    {
        $user = $this->pemilik();
        $token = $user->createToken('test')->plainTextToken;
        $properti = $this->buatProperti($user);

        for ($i = 1; $i <= 10; $i++) {
            $this->buatKamar($properti, 'K'.$i);
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/pemilik/properti/{$properti->id}/kamar", [
                'nama' => 'K11',
                'kapasitas' => 1,
                'harga_sewa_bulanan' => 500000,
                'status' => 'tersedia',
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('required_plan', 'pro');
    }

    public function test_kamar_soft_deleted_tidak_dihitung(): void
    {
        $user = $this->pemilik();
        SubscriptionService::mulaiTrialFree($user);
        $properti = $this->buatProperti($user);
        $kamar = $this->buatKamar($properti);
        $kamar->delete();

        $this->assertSame(0, SubscriptionService::usage($user, 'room'));
        $this->assertTrue(SubscriptionService::checkLimit($user, 'room')['allowed']);
    }

    public function test_admin_exempt_dari_limit(): void
    {
        $admin = User::factory()->create(['email' => 'admin@test.id']);
        $admin->assignRole('admin');

        $this->buatProperti($admin, 'Kos 1');
        $this->buatProperti($admin, 'Kos 2');

        $this->assertTrue(SubscriptionService::checkLimit($admin, 'property')['allowed']);
    }

    public function test_business_unlimited(): void
    {
        $user = $this->pemilik(['email' => 'biz@test.id']);
        $this->langganan($user, 'business', 'active', now()->addMonth()->toDateTimeString());

        $this->assertNull(SubscriptionService::limitFor('business', 'property'));
        $this->assertNull(SubscriptionService::limitFor('business', 'room'));
        $this->assertTrue(SubscriptionService::checkLimit($user, 'property')['allowed']);
        $this->assertTrue(SubscriptionService::checkLimit($user, 'room')['allowed']);
    }

    public function test_feature_required_plan_dan_feature_check(): void
    {
        $this->assertNull(SubscriptionService::getFeatureRequiredPlan('basic_property'));
        $this->assertSame('pro', SubscriptionService::getFeatureRequiredPlan('advanced_analytics'));
        $this->assertSame('pro', SubscriptionService::getFeatureRequiredPlan('export_report'));
        $this->assertSame('business', SubscriptionService::getFeatureRequiredPlan('unlimited_property'));

        $free = $this->pemilik(['email' => 'free2@test.id']);
        $cek = SubscriptionService::featureCheck($free, 'advanced_analytics');

        $this->assertFalse($cek['allowed']);
        $this->assertSame('pro', $cek['required_plan']);
    }

    public function test_cancelled_kembali_ke_free_tanpa_hapus_data(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'pro', 'cancelled', now()->addMonth()->toDateTimeString());

        $this->assertSame('free', SubscriptionService::getPlan($user));

        $properti = $this->buatProperti($user);
        $this->assertDatabaseHas('propertis', ['id' => $properti->id]);
    }

    public function test_feature_inheritance_business_mewarisi_pro_dan_free(): void
    {
        $business = $this->pemilik(['email' => 'biz2@test.id']);
        $this->langganan($business, 'business', 'active', now()->addMonth()->toDateTimeString());

        $this->assertTrue(SubscriptionService::hasFeature($business, 'advanced_analytics'));
        $this->assertTrue(SubscriptionService::hasFeature($business, 'basic_dashboard'));
        $this->assertTrue(SubscriptionService::hasFeature($business, 'laporan_24_bulan'));
    }

    public function test_downgrade_tidak_menghapus_data_dan_blokir_create_baru(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'business', 'active', now()->addMonth()->toDateTimeString());

        for ($i = 1; $i <= 6; $i++) {
            $this->buatProperti($user, 'Kos '.$i);
        }

        $this->langganan($user, 'pro', 'cancelled', now()->addMonth()->toDateTimeString());
        $this->langganan($user, 'pro', 'active', now()->subDay()->toDateTimeString());

        $this->assertSame('free', SubscriptionService::getPlan($user));
        $this->assertSame(6, SubscriptionService::usage($user, 'property'));

        $cek = SubscriptionService::checkLimit($user, 'property');
        $this->assertFalse($cek['allowed']);

        $properti = Properti::where('pemilik_id', $user->id)->firstOrFail();
        $this->assertTrue($properti->update(['nama' => 'Kos Update']));
        $this->assertSame(6, SubscriptionService::usage($user, 'property'));
    }

    public function test_api_analytics_ditolak_free_dengan_403(): void
    {
        $user = $this->pemilik();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard/pemilik/analytics');

        $response->assertStatus(403);
        $response->assertJsonPath('required_plan', 'pro');
    }

    public function test_api_analytics_diizinkan_pro(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard/pemilik/analytics');

        $response->assertOk();
        $response->assertJsonStructure(['periode', 'chart', 'rekap']);
    }

    public function test_api_rekap_premium_ditolak_free_dengan_403(): void
    {
        $user = $this->pemilik();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard/pemilik/rekap-premium');

        $response->assertStatus(403);
        $response->assertJsonPath('required_plan', 'pro');
    }

    public function test_api_rekap_premium_diizinkan_pro(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/dashboard/pemilik/rekap-premium?bulan='.now()->format('Y-m'));

        $response->assertOk();
        $response->assertJsonStructure(['periode', 'bulan', 'ringkasan']);
    }

    public function test_halaman_grafik_free_menampilkan_semua_grafik(): void
    {
        $user = $this->pemilik();
        SubscriptionService::mulaiTrialFree($user);

        $content = $this->actingAs($user)->get(route('pemilik.grafik'))->getContent();

        $this->assertStringContainsString('Kos Pemasukan Terbesar', $content);
        $this->assertStringContainsString('Tren Transaksi', $content);
        $this->assertStringContainsString('Tren Kamar Terisi', $content);
        $this->assertStringNotContainsString('Tersedia di paket PRO', $content);
    }

    public function test_halaman_grafik_dikunci_setelah_trial_habis(): void
    {
        $user = $this->pemilik(['email' => 'grafiklock@test.id']);
        SubscriptionService::mulaiTrialFree($user);
        Subscription::where('user_id', $user->id)->update(['expires_at' => now()->subDay()]);

        $this->assertTrue(SubscriptionService::laporanDikunci($user));

        $content = $this->actingAs($user)->get(route('pemilik.grafik'))->getContent();

        $this->assertStringContainsString('Masa coba 7 hari sudah habis', $content);
        $this->assertStringContainsString('Grafik & Analitik', $content);
        $this->assertStringNotContainsString('Uang Masuk vs Uang Keluar', $content);
        $this->assertStringNotContainsString('Ekspor Rekap Bulanan', $content);
    }

    public function test_halaman_grafik_pro_tidak_menampilkan_premium_lock(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());

        $content = $this->actingAs($user)->get(route('pemilik.grafik'))->getContent();

        $this->assertStringNotContainsString('Tersedia di paket PRO', $content);
    }

    public function test_subscription_history_tersedia(): void
    {
        $user = $this->pemilik();
        $this->langganan($user, 'pro', 'active', now()->addMonth()->toDateTimeString());
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/subscription/history');

        $response->assertOk();
        $response->assertJsonFragment(['plan' => 'pro']);
    }

    public function test_service_request_upgrade_membuat_permintaan_pending(): void
    {
        $user = $this->pemilik();

        $request = SubscriptionService::requestUpgrade($user, 'pro');

        $this->assertSame('pro', $request->requested_plan);
        $this->assertSame('pending', $request->status);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseHas('subscription_requests', [
            'user_id' => $user->id,
            'requested_plan' => 'pro',
            'status' => 'pending',
        ]);
        $this->assertSame('free', SubscriptionService::getPlan($user));
    }

    public function test_service_request_upgrade_menolak_plan_tidak_valid(): void
    {
        $user = $this->pemilik();

        $this->assertThrows(
            fn () => SubscriptionService::requestUpgrade($user, 'platinum'),
            \InvalidArgumentException::class,
        );
        $this->assertDatabaseCount('subscription_requests', 0);
    }

    public function test_service_request_upgrade_menolak_duplikat_pending(): void
    {
        $user = $this->pemilik();
        SubscriptionService::requestUpgrade($user, 'pro');

        $this->assertThrows(
            fn () => SubscriptionService::requestUpgrade($user, 'business'),
            \InvalidArgumentException::class,
        );
        $this->assertDatabaseCount('subscription_requests', 1);
    }

    public function test_service_approve_request_mengaktifkan_subscription_dan_history(): void
    {
        $user = $this->pemilik();
        $request = SubscriptionService::requestUpgrade($user, 'pro', 'Butuh laporan premium.');

        SubscriptionService::approveRequest($request, 30);

        $this->assertSame('approved', $request->refresh()->status);
        $this->assertDatabaseHas('subscription_requests', ['id' => $request->id, 'status' => 'approved']);
        $this->assertSame('pro', SubscriptionService::getPlan($user));
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan' => 'pro',
            'status' => 'active',
        ]);
        $this->assertSame(1, SubscriptionService::history($user)->count());
    }

    public function test_service_reject_request_tidak_mengubah_plan(): void
    {
        $user = $this->pemilik();
        $request = SubscriptionService::requestUpgrade($user, 'business');

        SubscriptionService::rejectRequest($request);

        $this->assertSame('rejected', $request->refresh()->status);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertSame('free', SubscriptionService::getPlan($user));
    }

    public function test_service_request_upgrade_menyimpan_data_pembayaran(): void
    {
        $user = $this->pemilik();

        $request = SubscriptionService::requestUpgrade($user, 'business', null, [
            'amount' => 99000,
            'payment_method' => 'qris',
            'bukti_path' => 'bukti/bukti.png',
        ]);

        $this->assertSame(99000, $request->amount);
        $this->assertSame('qris', $request->payment_method);
        $this->assertSame('bukti/bukti.png', $request->bukti_path);
        $this->assertDatabaseHas('subscription_requests', [
            'id' => $request->id,
            'amount' => 99000,
            'payment_method' => 'qris',
            'bukti_path' => 'bukti/bukti.png',
        ]);
    }

    public function test_halaman_paket_free_menampilkan_tombol_upgrade(): void
    {
        $user = $this->pemilik();
        $user->markEmailAsVerified();

        $content = $this->actingAs($user)->get(route('langganan.plans'))->assertOk()->getContent();

        $this->assertStringContainsString('/langganan/bayar/pro', $content);
        $this->assertStringContainsString('/langganan/bayar/business', $content);
        $this->assertStringContainsString('Upgrade ke Pro', $content);
    }

    public function test_halaman_paket_menampilkan_status_menunggu_saat_pending(): void
    {
        $user = $this->pemilik();
        $user->markEmailAsVerified();
        SubscriptionService::requestUpgrade($user, 'pro');

        $content = $this->actingAs($user)->get(route('langganan.plans'))->assertOk()->getContent();

        $this->assertStringContainsString('Menunggu Persetujuan', $content);
        $this->assertStringNotContainsString('/langganan/bayar/pro', $content);
    }

    public function test_halaman_bayar_menampilkan_qris_bila_dikonfigurasi(): void
    {
        Pengaturan::simpanBanyak([
            'pay.qris' => 'QRISTESTCODE-000201010211',
            'pay.petunjuk' => 'Scan dengan e-wallet lalu unggah bukti.',
        ]);

        $user = $this->pemilik();
        $user->markEmailAsVerified();

        $content = $this->actingAs($user)->get(route('langganan.bayar', 'pro'))->assertOk()->getContent();

        $this->assertStringContainsString('QRISTESTCODE-000201010211', $content);
        $this->assertStringContainsString('49.000', $content);
        $this->assertStringContainsString('Scan dengan e-wallet lalu unggah bukti.', $content);
        $this->assertStringContainsString('Kirim Pembayaran', $content);
        $this->assertStringContainsString('wire:model="bukti"', $content);
    }

    public function test_halaman_bayar_menampilkan_notice_bila_qris_belum_diatur(): void
    {
        Pengaturan::simpanBanyak([
            'pay.qris' => '',
            'pay.qris_image' => '',
        ]);

        $user = $this->pemilik();
        $user->markEmailAsVerified();

        $content = $this->actingAs($user)->get(route('langganan.bayar', 'pro'))->assertOk()->getContent();

        $this->assertStringContainsString('Admin belum mengatur pembayaran QRIS', $content);
        $this->assertStringNotContainsString('DEMOQRIS', $content);
        $this->assertStringNotContainsString('Unduh QRIS', $content);
    }

    public function test_bayar_langsung_terkonfirmasi_dan_notifikasi_admin(): void
    {
        Notification::fake();
        Storage::fake('public');

        Pengaturan::simpanBanyak([
            'pay.qris' => 'QRISTESTCODE-000201010211',
            'pay.qris_image' => '',
        ]);

        $pemilik = $this->pemilik();
        $pemilik->markEmailAsVerified();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $component = Volt::actingAs($pemilik)->test('pages.langganan.bayar', ['plan' => 'pro']);
        $component->set('bukti', UploadedFile::fake()->image('bukti.png'));
        $component->call('bayar')->assertHasNoErrors();
        $component->assertRedirect(route('langganan.plans'));

        $this->assertSame('pro', SubscriptionService::getPlan($pemilik));
        $this->assertDatabaseHas('subscription_requests', [
            'user_id' => $pemilik->id,
            'requested_plan' => 'pro',
            'status' => 'approved',
            'amount' => 49000,
        ]);
        Notification::assertSentTo($admin, LanggananDibayarNotification::class);
        Notification::assertSentTo($superAdmin, LanggananDibayarNotification::class);
    }

    public function test_halaman_kelola_super_admin_menampilkan_permintaan_dan_aksi(): void
    {
        $user = $this->pemilik();
        SubscriptionService::requestUpgrade($user, 'pro');

        $admin = User::factory()->create(['email' => 'root@test.id']);
        $admin->assignRole('super_admin');
        $admin->markEmailAsVerified();

        $content = $this->actingAs($admin)->get(route('langganan.kelola'))->assertOk()->getContent();

        $this->assertStringContainsString('Permintaan Upgrade', $content);
        $this->assertStringContainsString($user->nama, $content);
        $this->assertStringContainsString('Setujui', $content);
        $this->assertStringContainsString('Tolak', $content);
    }
}
