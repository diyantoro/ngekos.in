<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PengaturanAksesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dapat_mengelola_pengaturan(): void
    {
        $user = User::factory()->withRole(UserRole::SuperAdmin)->create();
        $this->actingAs($user);

        $component = Volt::test('pages.pengaturan');

        // mount() super admin membuka tab situs & memuat nilai awal.
        $component->assertSet('tab', 'situs');

        $component
            ->set('situsNama', 'Ngekos.in Test')
            ->set('situsEmail', 'halo@example.com')
            ->call('simpanSitus')
            ->assertHasNoErrors();

        $this->assertSame('Ngekos.in Test', Pengaturan::ambil('situs.nama'));

        $component
            ->set('kosJatuhTempo', 'akhir')
            ->set('kosDendaPerHari', '1000')
            ->call('simpanKos')
            ->assertHasNoErrors();

        $component
            ->set('qrisString', 'QRIS-TEST-123')
            ->set('petunjukBayar', 'Scan lalu simpan bukti.')
            ->call('simpanPembayaran')
            ->assertHasNoErrors();

        $this->assertSame('QRIS-TEST-123', Pengaturan::ambil('pay.qris'));
    }

    #[DataProvider('peranTanpaAksesKelolaProvider')]
    public function test_role_lain_ditolak_mengelola_pengaturan(string $peran): void
    {
        $user = User::factory()->withRole($peran)->create();
        $this->actingAs($user);

        // Halaman tetap bisa dibuka (tab profil & notifikasi untuk semua role).
        $this->get(route('pengaturan'))->assertOk();

        $component = Volt::test('pages.pengaturan');

        // mount() non-pengelola tetap di tab profil.
        $component->assertSet('tab', 'profil');

        Volt::test('pages.pengaturan')->set('situsNama', 'Coba Ubah')->call('simpanSitus')->assertForbidden();
        Volt::test('pages.pengaturan')->set('kosJatuhTempo', 'akhir')->set('kosDendaPerHari', '1000')->call('simpanKos')->assertForbidden();
        Volt::test('pages.pengaturan')->call('simpanPembayaran')->assertForbidden();

        // Nilai konfigurasi tidak berubah akibat upaya di atas.
        $this->assertNotSame('Coba Ubah', Pengaturan::ambil('situs.nama'));
    }

    #[DataProvider('peranTanpaAksesKelolaProvider')]
    public function test_preferensi_notifikasi_tetap_bisa_disimpan_semua_role(string $peran): void
    {
        $user = User::factory()->withRole($peran)->create();
        $this->actingAs($user);

        Volt::test('pages.pengaturan')
            ->set('notifikasi.tagihan_baru', false)
            ->call('simpanNotifikasi')
            ->assertHasNoErrors();

        $this->assertFalse($user->refresh()->notif('tagihan_baru'));
    }

    public static function peranTanpaAksesKelolaProvider(): array
    {
        return [
            'admin' => ['admin'],
            'pemilik' => ['pemilik'],
            'anak_kos' => ['anak_kos'],
        ];
    }
}
