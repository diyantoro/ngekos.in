<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertiFormScriptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class]);
    }

    public function test_script_blok_peta_utuh_dan_valid(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('pemilik');

        $html = $this->actingAs($user->refresh())->get(route('pemilik.properti.buat'))->getContent();

        if (! preg_match('/wire:effects="(\{.*?\})"\s*wire:id/s', $html, $m)) {
            $this->fail('Atribut wire:effects tidak ditemukan');
        }

        $effects = json_decode(html_entity_decode($m[1]), true);
        $scripts = $effects['scripts'] ?? [];
        $this->assertNotEmpty($scripts, 'Tidak ada script Livewire');

        foreach ($scripts as $key => $isi) {
            // Isi script harus utuh diawali kode JS awal blok, bukan
            // potongan komentar tengah (pernah terjadi karena token
            // @script tertulis di dalam komentar JS sehingga parser
            // Livewire memotong isi script dan JS halaman mati total).
            $this->assertStringStartsWith('<script>', ltrim($isi));
            $this->assertStringContainsString('let petaForm', substr($isi, 0, 500));
            $this->assertDoesNotMatchRegularExpression('/^\s*(dieksekusi|tetap hidup)/', $isi);
        }

        $this->assertTrue(true);
    }
}
