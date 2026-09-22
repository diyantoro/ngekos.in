<?php

namespace Tests\Feature;

use Tests\TestCase;

class TempPilihPeranTest extends TestCase
{
    public function test_pilih_peran_ada_tombol_kembali_ke_beranda(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Kembali ke Beranda', false)
            ->assertSee(route('home'), false)
            ->assertSee('Masuk sebagai', false)
            ->assertSee('Pencari Kos', false)
            ->assertSee('Pemilik Kos', false);
    }
}
