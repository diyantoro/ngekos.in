<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresi proxy bukti pembayaran privat: file hanya disajikan lewat
 * route pembayaran.bukti yang berotorisasi, tidak pernah lewat URL publik.
 */
class BuktiProxyRegresiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    private function pembayaranMenungguPemilik1(): Pembayaran
    {
        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        return Pembayaran::where('status', 'menunggu_verifikasi')
            ->whereHas('tagihan.penyewaan.properti', fn ($q) => $q->where('pemilik_id', $pemilik->id))
            ->firstOrFail();
    }

    public function test_proxy_menyajikan_file_pada_disk_privat(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('bukti-pembayaran/bukti-test.jpg', 'isi-bukti');

        $pembayaran = $this->pembayaranMenungguPemilik1();
        $pembayaran->update(['bukti' => 'bukti-pembayaran/bukti-test.jpg']);

        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        $response = $this->actingAs($pemilik)->get(route('pembayaran.bukti', $pembayaran->id));

        $response->assertOk();
        $this->assertSame('isi-bukti', $response->streamedContent());
    }

    public function test_proxy_menolak_user_lain_dengan_403(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('bukti-pembayaran/bukti-test.jpg', 'isi-bukti');

        $pembayaran = $this->pembayaranMenungguPemilik1();
        $pembayaran->update(['bukti' => 'bukti-pembayaran/bukti-test.jpg']);

        // Pemilik lain (Siti) bukan pemilik properti terkait.
        $this->actingAs(User::where('email', 'pemilik2@ngekos.test')->firstOrFail())
            ->get(route('pembayaran.bukti', $pembayaran->id))
            ->assertForbidden();
    }

    public function test_proxy_404_bila_file_tidak_ada(): void
    {
        Storage::fake('private');

        $pembayaran = $this->pembayaranMenungguPemilik1();
        $pembayaran->update(['bukti' => 'bukti-pembayaran/tidak-ada.jpg']);

        $pemilik = User::where('email', 'pemilik1@ngekos.test')->firstOrFail();

        $this->actingAs($pemilik)
            ->get(route('pembayaran.bukti', $pembayaran->id))
            ->assertNotFound();
    }
}
