<?php

namespace Tests\Feature;

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use App\Enums\UserRole;
use App\Models\BarangKos;
use App\Models\Properti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasarKosKurasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_barang_baru_otomatis_tampil_di_toko_pemilik_dan_katalog(): void
    {
        [$penjualA, $penjualB] = $this->duaPenjual();

        $this->assertDatabaseHas('barang_kos_akun', [
            'barang_id' => $penjualA->barangKos()->firstOrFail()->id,
            'user_id' => $penjualA->id,
        ]);

        $this->get(route('barang.index'))
            ->assertOk()
            ->assertSee('Barang Penjual A')
            ->assertSee('Barang Penjual B');
    }

    public function test_super_admin_dapat_mengatur_barang_tampil_di_beberapa_akun(): void
    {
        [$penjualA, $penjualB] = $this->duaPenjual();
        $barang = $penjualA->barangKos()->firstOrFail();

        $this->actingAs($this->superAdmin());

        Volt::test('pages.super-admin.pasar-kos')
            ->set('barangIds', [(string) $barang->id])
            ->set('akunIds', [(string) $penjualA->id, (string) $penjualB->id])
            ->call('tampilkanDiAkun')
            ->assertHasNoErrors();

        $this->assertEqualsCanonicalizing(
            [$penjualA->id, $penjualB->id],
            $barang->fresh()->akunTampil()->pluck('users.id')->all()
        );

        // Menambah akun lain tidak boleh melepas akun yang sudah tampil.
        $penjualC = User::factory()->create([
            'nama' => 'Penjual C',
            'role' => UserRole::AnakKos,
            'email_verified_at' => now(),
        ]);

        Volt::test('pages.super-admin.pasar-kos')
            ->set('barangIds', [(string) $barang->id])
            ->set('akunIds', [(string) $penjualC->id])
            ->call('tampilkanDiAkun')
            ->assertHasNoErrors();

        $this->assertEqualsCanonicalizing(
            [$penjualA->id, $penjualB->id, $penjualC->id],
            $barang->fresh()->akunTampil()->pluck('users.id')->all()
        );
    }

    public function test_katalog_menampilkan_barang_sekali_walau_tampil_di_banyak_akun(): void
    {
        [$penjualA, $penjualB] = $this->duaPenjual();
        $barang = $penjualA->barangKos()->firstOrFail();

        $barang->akunTampil()->sync([$penjualA->id, $penjualB->id]);

        $html = $this->get(route('barang.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'data-barang-id="'.$barang->id.'"'));
    }

    public function test_toko_akun_menampilkan_barang_yang_di_assign(): void
    {
        [$penjualA, $penjualB] = $this->duaPenjual();
        $barang = $penjualA->barangKos()->firstOrFail();

        $barang->akunTampil()->sync([$penjualA->id, $penjualB->id]);

        $this->get(route('pasar.toko', $penjualB))
            ->assertOk()
            ->assertSee('Barang Penjual A')
            ->assertSee('Dijual oleh Penjual A');

        $this->get(route('pasar.toko', $penjualA))
            ->assertOk()
            ->assertSee('Barang Penjual A')
            ->assertDontSee('Dijual oleh Penjual A');
    }

    public function test_barang_tanpa_akun_disembunyikan_dari_katalog_dan_toko(): void
    {
        [$penjualA] = $this->duaPenjual();
        $barang = $penjualA->barangKos()->firstOrFail();

        $barang->akunTampil()->detach();

        $this->get(route('barang.index'))
            ->assertOk()
            ->assertDontSee('Barang Penjual A');

        $this->get(route('pasar.toko', $penjualA))
            ->assertOk()
            ->assertDontSee('Barang Penjual A');
    }

    public function test_super_admin_dapat_menyembunyikan_barang_lewat_halaman_kelola(): void
    {
        [$penjualA] = $this->duaPenjual();
        $barang = $penjualA->barangKos()->firstOrFail();

        $this->actingAs($this->superAdmin());

        Volt::test('pages.super-admin.pasar-kos')
            ->set('barangIds', [(string) $barang->id])
            ->call('sembunyikanBarang')
            ->assertHasNoErrors();

        $this->assertSame(0, $barang->fresh()->akunTampil()->count());
    }

    public function test_selain_super_admin_tidak_bisa_membuka_kurasi_katalog(): void
    {
        $anakKos = User::factory()->create([
            'role' => UserRole::AnakKos,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($anakKos)
            ->get(route('pasar.kelola'))
            ->assertForbidden();
    }

    public function test_carousel_promo_partner_tampil_di_pasar_kos_dan_detail(): void
    {
        [$penjualA] = $this->duaPenjual();
        $barang = $penjualA->barangKos()->firstOrFail();

        $this->get(route('barang.index'))
            ->assertOk()
            ->assertSee('data-promo', false)
            ->assertSee('Shopee')
            ->assertSee('Lazada')
            ->assertSee('OLX')
            ->assertSee('Tokopedia');

        $this->get(route('barang.detail', $barang))
            ->assertOk()
            ->assertSee('data-promo', false)
            ->assertSee('Lazada')
            ->assertSee('Tokopedia');
    }

    public function test_akun_di_kota_barang_didahulukan_saat_barang_dipilih(): void
    {
        [$penjualA] = $this->duaPenjual();
        $barang = $penjualA->barangKos()->firstOrFail();

        $akunJogja = User::factory()->create([
            'nama' => 'Zaki Jogja',
            'role' => UserRole::Pemilik,
            'email_verified_at' => now(),
        ]);
        Properti::create([
            'pemilik_id' => $akunJogja->id,
            'nama' => 'Kos Zaki',
            'kota' => 'Yogyakarta',
        ]);

        $akunBandung = User::factory()->create([
            'nama' => 'Andi Bandung',
            'role' => UserRole::Pemilik,
            'email_verified_at' => now(),
        ]);
        Properti::create([
            'pemilik_id' => $akunBandung->id,
            'nama' => 'Kos Andi',
            'kota' => 'Bandung',
        ]);

        $this->actingAs($this->superAdmin());

        $html = Volt::test('pages.super-admin.pasar-kos')
            ->set('barangIds', [(string) $barang->id])
            ->assertSee('Didahulukan: akun di kota')
            ->assertSee('Kota sama')
            ->html();

        $posisiJogja = strpos($html, 'Zaki Jogja');
        $posisiBandung = strpos($html, 'Andi Bandung');

        $this->assertNotFalse($posisiJogja);
        $this->assertNotFalse($posisiBandung);
        $this->assertLessThan($posisiBandung, $posisiJogja);
    }

    private function superAdmin(): User
    {
        return User::factory()->withRole(UserRole::SuperAdmin)->create([
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function duaPenjual(): array
    {
        $penjualA = User::factory()->create([
            'nama' => 'Penjual A',
            'role' => UserRole::AnakKos,
            'email_verified_at' => now(),
        ]);
        $penjualB = User::factory()->create([
            'nama' => 'Penjual B',
            'role' => UserRole::Pemilik,
            'email_verified_at' => now(),
        ]);

        $this->buatBarang($penjualA, 'Barang Penjual A');
        $this->buatBarang($penjualB, 'Barang Penjual B');

        return [$penjualA, $penjualB];
    }

    private function buatBarang(User $penjual, string $nama): BarangKos
    {
        return BarangKos::create([
            'user_id' => $penjual->id,
            'nama' => $nama,
            'kategori' => BarangKategori::Perabot,
            'kondisi' => BarangKondisi::BekasMulus,
            'harga' => 100000,
            'deskripsi' => 'Barang layak pakai untuk menguji kurasi katalog.',
            'kota' => 'Yogyakarta',
            'no_wa' => '081234567890',
            'status' => BarangStatus::Tersedia,
        ]);
    }
}
