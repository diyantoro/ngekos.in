<?php

namespace Tests\Feature;

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use App\Enums\UserRole;
use App\Models\BarangKos;
use App\Models\BarangPesan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class BarangChatTest extends TestCase
{
    use RefreshDatabase;

    private User $penjual;

    private User $pembeli;

    private User $pembeliLain;

    private BarangKos $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->penjual = User::factory()->create([
            'role' => UserRole::Pemilik,
            'email_verified_at' => now(),
            'no_hp' => '081234567801',
        ]);

        $this->pembeli = User::factory()->create([
            'role' => UserRole::AnakKos,
            'email_verified_at' => now(),
            'no_hp' => '081234567802',
        ]);

        $this->pembeliLain = User::factory()->create([
            'role' => UserRole::AnakKos,
            'email_verified_at' => now(),
            'no_hp' => '081234567803',
        ]);

        $this->barang = BarangKos::create([
            'user_id' => $this->penjual->id,
            'nama' => 'Meja Belajar Lipat',
            'kategori' => BarangKategori::Perabot,
            'kondisi' => BarangKondisi::BekasMulus,
            'harga' => 150000,
            'bisa_nego' => true,
            'deskripsi' => 'Meja belajar lipat kokoh, siap COD.',
            'kota' => 'Palembang',
            'no_wa' => '081234567801',
            'status' => BarangStatus::Tersedia,
        ]);

        $this->withoutVite();
    }

    public function test_tamu_diarahkan_login_untuk_chat_barang(): void
    {
        $this->get(route('barang.chat', $this->barang))
            ->assertRedirect(route('login'));
    }

    public function test_pembeli_dapat_mengirim_pesan_dan_penjual_melihatnya(): void
    {
        Volt::actingAs($this->pembeli)
            ->test('pages.barang.chat', ['barang' => $this->barang])
            ->set('pesanBaru', 'Halo, apakah meja ini masih ada?')
            ->call('kirim')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('barang_pesans', [
            'barang_kos_id' => $this->barang->id,
            'penjual_id' => $this->penjual->id,
            'pembeli_id' => $this->pembeli->id,
            'pengirim_id' => $this->pembeli->id,
            'pesan' => 'Halo, apakah meja ini masih ada?',
        ]);

        $pesan = BarangPesan::firstOrFail();
        $this->assertNull($pesan->dibaca_pada);

        $this->actingAs($this->penjual)
            ->get(route('barang.chat', $this->barang))
            ->assertOk()
            ->assertSee('Halo, apakah meja ini masih ada?');
    }

    public function test_penjual_dapat_membalas_dan_pesan_menjadi_dibaca(): void
    {
        BarangPesan::create([
            'barang_kos_id' => $this->barang->id,
            'penjual_id' => $this->penjual->id,
            'pembeli_id' => $this->pembeli->id,
            'pengirim_id' => $this->pembeli->id,
            'pesan' => 'Bisa kurang sedikit?',
        ]);

        Volt::actingAs($this->penjual)
            ->test('pages.barang.chat', ['barang' => $this->barang, 'pembeli' => $this->pembeli])
            ->set('pesanBaru', 'Bisa, COD di kampus ya.')
            ->call('kirim')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('barang_pesans', [
            'barang_kos_id' => $this->barang->id,
            'pengirim_id' => $this->penjual->id,
            'pembeli_id' => $this->pembeli->id,
            'pesan' => 'Bisa, COD di kampus ya.',
        ]);

        $this->assertSame(
            0,
            BarangPesan::where('pengirim_id', $this->pembeli->id)->whereNull('dibaca_pada')->count()
        );
    }

    public function test_pembeli_tidak_bisa_membaca_percakapan_pembeli_lain(): void
    {
        BarangPesan::create([
            'barang_kos_id' => $this->barang->id,
            'penjual_id' => $this->penjual->id,
            'pembeli_id' => $this->pembeli->id,
            'pengirim_id' => $this->pembeli->id,
            'pesan' => 'Pesan rahasia milik pembeli pertama.',
        ]);

        Volt::actingAs($this->pembeliLain)
            ->test('pages.barang.chat', ['barang' => $this->barang, 'pembeli' => $this->pembeli])
            ->assertForbidden();
    }

    public function test_penjual_tidak_bisa_membuka_percakapan_yang_belum_ada(): void
    {
        $this->actingAs($this->penjual)
            ->get(route('barang.chat.pembeli', ['barang' => $this->barang, 'pembeli' => $this->pembeli]))
            ->assertNotFound();
    }

    public function test_penjual_tidak_bisa_mengobrol_dengan_barang_miliknya_sendiri_sebagai_pembeli(): void
    {
        $this->actingAs($this->penjual)
            ->get(route('barang.chat', $this->barang))
            ->assertOk()
            ->assertSee('Pesan Pembeli');
    }

    public function test_badge_pesan_belum_dibaca_menghitung_chat_barang(): void
    {
        BarangPesan::create([
            'barang_kos_id' => $this->barang->id,
            'penjual_id' => $this->penjual->id,
            'pembeli_id' => $this->pembeli->id,
            'pengirim_id' => $this->pembeli->id,
            'pesan' => 'Pak, bisa titip di pos keamanan?',
        ]);

        // Hindari nilai memo/lencana dari request sebelumnya dalam proses test.
        $this->penjual->forgetPesanBelumDibacaCache();

        $this->assertSame(1, $this->penjual->fresh()->pesanBelumDibaca());

        Volt::actingAs($this->penjual)
            ->test('pages.barang.chat', ['barang' => $this->barang, 'pembeli' => $this->pembeli]);

        $this->assertSame(0, $this->penjual->fresh()->pesanBelumDibaca());
    }

    public function test_inbox_pesan_menampilkan_tab_pasar_kos(): void
    {
        BarangPesan::create([
            'barang_kos_id' => $this->barang->id,
            'penjual_id' => $this->penjual->id,
            'pembeli_id' => $this->pembeli->id,
            'pengirim_id' => $this->pembeli->id,
            'pesan' => 'Meja masih tersedia?',
        ]);

        $this->actingAs($this->pembeli)
            ->get(route('chat.index'))
            ->assertOk()
            ->assertSee('Pasar Kos')
            ->assertSee('Meja Belajar Lipat');

        $this->actingAs($this->penjual)
            ->get(route('chat.index'))
            ->assertOk()
            ->assertSee('Meja masih tersedia?');
    }

    public function test_detail_barang_menampilkan_tombol_chat_di_aplikasi(): void
    {
        $this->actingAs($this->pembeli)
            ->get(route('barang.detail', $this->barang))
            ->assertOk()
            ->assertSee('Chat di Aplikasi');
    }

    public function test_tamu_melihat_ajakan_masuk_untuk_chat_barang(): void
    {
        $this->get(route('barang.detail', $this->barang))
            ->assertOk()
            ->assertSee('Masuk untuk Chat di Aplikasi');
    }
}
