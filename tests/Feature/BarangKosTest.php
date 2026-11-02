<?php

namespace Tests\Feature;

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use App\Enums\UserRole;
use App\Models\BarangKos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class BarangKosTest extends TestCase
{
    use RefreshDatabase;

    private User $anakKos;

    private User $pemilik;

    protected function setUp(): void
    {
        parent::setUp();

        $this->anakKos = User::factory()->create([
            'role' => UserRole::AnakKos,
            'email_verified_at' => now(),
            'no_hp' => '081234567891',
        ]);

        $this->pemilik = User::factory()->create([
            'role' => UserRole::Pemilik,
            'email_verified_at' => now(),
            'no_hp' => '081234567892',
        ]);
    }

    public function test_katalog_barang_kos_dapat_diakses_publik(): void
    {
        BarangKos::create([
            'user_id' => $this->anakKos->id,
            'nama' => 'Kipas Angin Dinding Cosmos',
            'kategori' => BarangKategori::Elektronik,
            'kondisi' => BarangKondisi::BekasMulus,
            'harga' => 120000,
            'bisa_nego' => true,
            'deskripsi' => 'Kipas angin dinding masih sangat bagus dan normal.',
            'kota' => 'Yogyakarta',
            'no_wa' => '081234567891',
            'status' => BarangStatus::Tersedia,
        ]);

        $response = $this->get(route('barang.index'));
        $response->assertStatus(200);
        $response->assertSee('Kipas Angin Dinding Cosmos');
        $response->assertSee('Rp 120.000');
    }

    public function test_detail_barang_kos_dapat_dilihat_dan_menambah_counter(): void
    {
        $barang = BarangKos::create([
            'user_id' => $this->pemilik->id,
            'nama' => 'Kulkas Mini Sharp 50L',
            'kategori' => BarangKategori::Elektronik,
            'kondisi' => BarangKondisi::BekasSepertiBaru,
            'harga' => 850000,
            'bisa_nego' => false,
            'deskripsi' => 'Kulkas mini normal dingin maksimal siap pakai.',
            'kota' => 'Yogyakarta',
            'no_wa' => '081234567892',
            'status' => BarangStatus::Tersedia,
            'dilihat' => 5,
        ]);

        $response = $this->get(route('barang.detail', $barang));
        $response->assertStatus(200);
        $response->assertSee('Kulkas Mini Sharp 50L');
        $response->assertSee('Rp 850.000');

        $this->assertEquals(6, $barang->fresh()->dilihat);
    }

    public function test_tamu_tidak_bisa_mengakses_halaman_pasang_atau_kelola(): void
    {
        $this->get(route('barang.pasang'))->assertRedirect(route('login'));
        $this->get(route('barang.kelola'))->assertRedirect(route('login'));
    }

    public function test_anak_kos_dapat_memasang_iklan_barang_baru(): void
    {
        $this->actingAs($this->anakKos);

        Volt::test('pages.barang.form')
            ->set('nama', 'Meja Belajar Lipat')
            ->set('kategori', 'perabot')
            ->set('kondisi', 'bekas_mulus')
            ->set('harga', 75000)
            ->set('bisa_nego', true)
            ->set('deskripsi', 'Meja lipat kayu cocok untuk belajar di kasur/lantai.')
            ->set('minus', null)
            ->set('kota', 'Yogyakarta')
            ->set('lokasi_detail', 'Pogung Baru')
            ->set('no_wa', '081234567891')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('barang_kos', [
            'user_id' => $this->anakKos->id,
            'nama' => 'Meja Belajar Lipat',
            'harga' => 75000,
            'bisa_nego' => true,
            'kategori' => 'perabot',
            'kondisi' => 'bekas_mulus',
            'kota' => 'Yogyakarta',
        ]);
    }

    public function test_pemilik_kos_dapat_memasang_iklan_barang(): void
    {
        $this->actingAs($this->pemilik);

        Volt::test('pages.barang.form')
            ->set('nama', 'Kasur Single Inoac 90x200')
            ->set('kategori', 'perabot')
            ->set('kondisi', 'baru')
            ->set('harga', 500000)
            ->set('bisa_nego', false)
            ->set('deskripsi', 'Kasur single baru stok pengadaan kosan.')
            ->set('kota', 'Yogyakarta')
            ->set('no_wa', '081234567892')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('barang_kos', [
            'user_id' => $this->pemilik->id,
            'nama' => 'Kasur Single Inoac 90x200',
            'harga' => 500000,
        ]);
    }

    public function test_user_dapat_mengubah_status_barang_menjadi_terjual(): void
    {
        $barang = BarangKos::create([
            'user_id' => $this->anakKos->id,
            'nama' => 'Rice Cooker Mini',
            'kategori' => BarangKategori::Dapur,
            'kondisi' => BarangKondisi::BekasMulus,
            'harga' => 120000,
            'deskripsi' => 'Rice cooker mini serbaguna.',
            'kota' => 'Yogyakarta',
            'no_wa' => '081234567891',
            'status' => BarangStatus::Tersedia,
        ]);

        $this->actingAs($this->anakKos);

        Volt::test('pages.barang.kelola')
            ->call('toggleStatus', $barang->id);

        $this->assertEquals(BarangStatus::Terjual, $barang->fresh()->status);
    }

    public function test_user_tidak_dapat_mengedit_barang_milik_orang_lain(): void
    {
        $barangMilikPemilik = BarangKos::create([
            'user_id' => $this->pemilik->id,
            'nama' => 'Dispenser Modena',
            'kategori' => BarangKategori::Elektronik,
            'kondisi' => BarangKondisi::BekasMulus,
            'harga' => 300000,
            'deskripsi' => 'Dispenser air galon bawah.',
            'kota' => 'Yogyakarta',
            'no_wa' => '081234567892',
            'status' => BarangStatus::Tersedia,
        ]);

        $this->actingAs($this->anakKos);

        $this->get(route('barang.ubah', $barangMilikPemilik))
            ->assertStatus(403);
    }

    public function test_user_dapat_menghapus_barang_miliknya(): void
    {
        $barang = BarangKos::create([
            'user_id' => $this->anakKos->id,
            'nama' => 'Rak Sepatu Plastik',
            'kategori' => BarangKategori::Perabot,
            'kondisi' => BarangKondisi::BekasMulus,
            'harga' => 30000,
            'deskripsi' => 'Rak sepatu 4 susun.',
            'kota' => 'Yogyakarta',
            'no_wa' => '081234567891',
            'status' => BarangStatus::Tersedia,
        ]);

        $this->actingAs($this->anakKos);

        Volt::test('pages.barang.kelola')
            ->call('hapus', $barang->id);

        $this->assertDatabaseMissing('barang_kos', [
            'id' => $barang->id,
        ]);
    }

    public function test_filter_kota_memakai_ketikan_dan_memfilter_sebagian_nama(): void
    {
        foreach ([['Bandung', 'Blender Portable'], ['Malang', 'Rak Buku Kayu']] as [$kota, $nama]) {
            BarangKos::create([
                'user_id' => $this->anakKos->id,
                'nama' => $nama,
                'kategori' => BarangKategori::Perabot,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 100000,
                'deskripsi' => 'Barang demo untuk filter kota.',
                'kota' => $kota,
                'no_wa' => '081234567891',
                'status' => BarangStatus::Tersedia,
            ]);
        }

        Volt::test('pages.barang.index')
            ->assertSeeHtml('wire:model.live.debounce.300ms="kota"')
            ->assertDontSeeHtml('<select wire:model.live="kota"', false)
            ->set('kota', 'band')
            ->assertSee('Blender Portable')
            ->assertDontSee('Rak Buku Kayu')
            ->set('kota', 'MALANG')
            ->assertSee('Rak Buku Kayu')
            ->assertDontSee('Blender Portable');
    }

    public function test_foto_demo_dari_folder_public_tampil_dengan_benar(): void
    {
        $barang = new BarangKos(['foto' => 'images/barang/kipas-angin.jpg']);

        $this->assertSame(asset('images/barang/kipas-angin.jpg'), $barang->fotoUrl());
        $this->assertContains(asset('images/barang/kipas-angin.jpg'), $barang->semuaFoto());
        $this->assertFileExists(public_path('images/barang/kipas-angin.jpg'));
        $this->assertFileExists(public_path('images/default-properti.jpg'));
    }

    public function test_barang_tanpa_foto_memakai_gambar_fallback(): void
    {
        $barang = new BarangKos(['foto' => null]);

        $this->assertSame(asset('images/default-properti.jpg'), $barang->fotoUrl());
        $this->assertSame([asset('images/default-properti.jpg')], $barang->semuaFoto());
    }
}
