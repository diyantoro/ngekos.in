<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Halaman beranda dapat diakses publik tanpa login.
     */
    public function test_beranda_dapat_diakses_publik(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
