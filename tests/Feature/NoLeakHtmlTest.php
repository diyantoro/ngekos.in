<?php

namespace Tests\Feature;

use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoLeakHtmlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
        $this->withoutVite();
    }

    public function test_beranda_peta_berada_di_dalam_tag_script(): void
    {
        $html = $this->get('/')->getContent();

        $markerPos = strpos($html, 'data.forEach((m) => bounds.extend');
        $this->assertNotFalse($markerPos, 'kode peta harus ada');
        $this->assertNotFalse(strpos($html, 'window.initPetaBeranda'), 'harus pakai fungsi global');

        $before = substr($html, 0, $markerPos);
        $lastScriptOpen = strrpos($before, '<script');
        $lastScriptClose = strrpos($before, '</script>');

        $this->assertNotFalse($lastScriptOpen, 'harus ada tag <script> sebelum kode peta');
        $this->assertTrue($lastScriptOpen > $lastScriptClose, 'kode peta harus berada DALAM <script>, bukan bocor di atribut/teks');
    }

    public function test_dashboard_pemilik_tidak_bocorkan_hint(): void
    {
        $user = \App\Models\User::where('email', 'pemilik1@ngekos.test')->firstOrFail();
        $html = $this->actingAs($user)->get(route('dashboard.pemilik'))->getContent();

        $this->assertStringNotContainsString('"/2 kamar terisi', $html, 'hint kamar terisi bocor');
        $this->assertStringNotContainsString('kamar terisi" />', $html, 'atribut bocor');
        $this->assertStringNotContainsString('Bulan lalu belum ada pengeluaran</span>"', $html, 'hint pengeluaran bocor');
        $this->assertStringNotContainsString("hint='", $html, 'atribut hint bocor sebagai teks mentah');
        $this->assertStringNotContainsString("Siap disewakan</span>'", $html, 'hint kamar kosong bocor');
        $this->assertStringNotContainsString("' />", $html, 'tanda kutip-elemen atribut bocor');
    }
}