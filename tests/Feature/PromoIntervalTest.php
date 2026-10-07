<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PromoIntervalTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_interval_3000_tanpa_barisan_pengaturan(): void
    {
        $this->assertSame(3000, Pengaturan::promoIntervalMs());
    }

    public function test_simpan_interval_dijepit_1_sampai_10_detik(): void
    {
        Pengaturan::simpanPromoIntervalMs(500);
        $this->assertSame(1000, Pengaturan::promoIntervalMs());

        Pengaturan::simpanPromoIntervalMs(60000);
        $this->assertSame(10000, Pengaturan::promoIntervalMs());

        Pengaturan::simpanPromoIntervalMs(0);
        $this->assertSame(3000, Pengaturan::promoIntervalMs());

        Pengaturan::simpanPromoIntervalMs(7000);
        $this->assertSame(7000, Pengaturan::promoIntervalMs());
    }

    public function test_komponen_promo_merender_atribut_interval(): void
    {
        Pengaturan::simpanPromoIntervalMs(7000);

        $html = Blade::render('<x-promo-ads />');

        $this->assertStringContainsString('data-promo-interval="7000"', $html);
    }

    public function test_komponen_promo_menolak_interval_tidak_valid(): void
    {
        $html = Blade::render('<x-promo-ads :interval-ms="5" />');

        $this->assertStringContainsString('data-promo-interval="3000"', $html);
    }
}
