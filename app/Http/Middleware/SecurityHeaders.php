<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menambahkan HTTP security headers dasar untuk memperkuat proteksi
 * terhadap serangan clickjacking, MIME sniffing, dan informasi referrer.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', "camera=(), microphone=(), geolocation=(self)");
        // Catatan: 'unsafe-eval' wajib ada karena Alpine.js (dipakai Livewire
        // untuk x-data/@click/x-show/dll) mengevaluasi ekspresi via new Function.
        // Tanpa itu seluruh komponen interaktif mati: chatbot, mode gelap,
        // grafik persebaran, tombol peta, dan menu mobile.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; img-src 'self' data: https: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; font-src 'self' data: https:; connect-src 'self' https: wss:; frame-src 'self' https: data: blob:; child-src 'self' https: data: blob:; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
        );

        if ($request->secure() || app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
