<?php

namespace Tests\Feature;

use App\Models\Properti;
use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RekeningTersamarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_tamu_anonim_hanya_dapat_rekening_tersamar(): void
    {
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $this->assertTrue($properti->punyaRekening());

        $res = $this->getJson("/api/kos/{$properti->id}")->assertOk();

        // Nomor utuh tidak boleh bocor ke anonim.
        $res->assertJsonMissing(['no_rekening' => $properti->no_rekening]);
        $this->assertStringStartsWith('****', (string) $res->json('no_rekening'));
        $this->assertStringContainsString(substr(preg_replace('/\D+/', '', $properti->no_rekening), -4), (string) $res->json('no_rekening'));
        // Nama tersamar, bank tetap tampil, label siap tampil tersedia.
        $this->assertSame(mb_substr($properti->rekening_nama, 0, 1).'***', $res->json('rekening_nama'));
        $res->assertJsonPath('bank_nama', $properti->bank_nama);
        $res->assertJsonPath('punya_rekening', true);
        $this->assertNotNull($res->json('rekening_tersamar'));
    }

    public function test_user_login_mendapat_rekening_utuh_untuk_transfer_dp(): void
    {
        $anak = User::where('email', 'anak1@ngekos.test')->firstOrFail();
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();

        Sanctum::actingAs($anak, ['*']);

        $res = $this->getJson("/api/kos/{$properti->id}")->assertOk();

        $res->assertJsonPath('no_rekening', $properti->no_rekening);
        $res->assertJsonPath('rekening_nama', $properti->rekening_nama);
        $res->assertJsonPath('punya_rekening', true);
    }

    public function test_kos_tanpa_rekening_tetap_aman_dan_jelas(): void
    {
        $properti = Properti::where('nama', 'Kos Melati')->firstOrFail();
        $properti->update(['bank_nama' => null, 'no_rekening' => null, 'rekening_nama' => null]);

        $res = $this->getJson("/api/kos/{$properti->id}")->assertOk();

        $res->assertJsonPath('punya_rekening', false);
        $this->assertNull($res->json('no_rekening'));
        $this->assertNull($res->json('rekening_tersamar'));
    }

    public function test_unit_samaran_model(): void
    {
        $p = new Properti(['bank_nama' => 'BCA', 'no_rekening' => '1234567890', 'rekening_nama' => 'Budi Santoso']);

        $this->assertSame('****7890', $p->noRekeningTersamar());
        $this->assertSame('B***', $p->namaRekeningTersamar());
        $this->assertSame('BCA ****7890 a.n. B***', $p->labelRekeningTersamar());

        $kosong = new Properti;
        $this->assertNull($kosong->noRekeningTersamar());
        $this->assertNull($kosong->labelRekeningTersamar());
        $this->assertFalse($kosong->punyaRekening());
    }
}
