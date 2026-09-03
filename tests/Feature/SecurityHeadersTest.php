<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_respon_web_mengirim_headers_keamanan(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_respon_api_mengirim_headers_keamanan(): void
    {
        // Kirim request login tanpa kredensial valid -> response 422 dari validasi,
        // tapi header keamanan tetap harus terpasang.
        $response = $this->postJson('/api/login', ['email' => '', 'password' => '']);

        $response->assertStatus(422);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
