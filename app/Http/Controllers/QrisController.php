<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class QrisController extends Controller
{
    /**
     * Unduh QRIS paket langganan sebagai file (attachment).
     * Selalu mengembalikan Content-Disposition: attachment agar browser
     * mengunduh, bukan hanya membuka gambar.
     */
    public function unduh(Request $request, string $plan)
    {
        if (! in_array($plan, ['pro', 'business'], true)) {
            abort(404);
        }

        $qris = (string) Pengaturan::ambil('pay.qris', '');
        $qrisImage = (string) Pengaturan::ambil('pay.qris_image', '');

        if ($qris === '' && $qrisImage === '') {
            abort(404, 'QRIS belum diatur admin.');
        }

        // 1) Admin mengunggah gambar QRIS: kirim file aslinya sebagai unduhan.
        if ($qrisImage !== '' && Storage::disk('public')->exists($qrisImage)) {
            $ext = strtolower((string) pathinfo($qrisImage, PATHINFO_EXTENSION));
            if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $ext = 'png';
            }

            return response()->download(
                Storage::disk('public')->path($qrisImage),
                "QRIS-{$plan}.{$ext}"
            );
        }

        if ($qris === '') {
            abort(404);
        }

        // 2) QRIS berupa string: ambil PNG dari penyedia QR server-side
        // (tanpa CORS) lalu kembalikan sebagai attachment.
        try {
            $url = 'https://api.qrserver.com/v1/create-qr-code/?size=480x480&margin=12&data='.urlencode($qris);
            $res = Http::timeout(10)->get($url);

            if ($res->successful()) {
                $contentType = (string) ($res->header('Content-Type') ?: 'image/png');
                $body = $res->body();

                if (str_starts_with($contentType, 'image') && strlen($body) > 100) {
                    return response($body, 200, [
                        'Content-Type' => $contentType,
                        'Content-Disposition' => 'attachment; filename="QRIS-'.$plan.'.png"',
                        'Content-Length' => (string) strlen($body),
                        'Cache-Control' => 'private, max-age=3600',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Lanjut ke fallback di bawah.
        }

        // 3) Fallback: unduh string QRIS sebagai txt agar tombol tetap berguna.
        return response($qris, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="QRIS-'.$plan.'.txt"',
        ]);
    }
}
