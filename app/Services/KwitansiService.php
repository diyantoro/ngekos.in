<?php

namespace App\Services;

use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Generate kwitansi PDF per pembayaran terverifikasi.
 * Idempoten: pembayaran yang sudah punya file_kwitansi tidak dibuat ulang.
 */
class KwitansiService
{
    /**
     * @param bool $paksa Regenerasi ulang walau file sudah ada (mis. template berubah).
     */
    public static function untuk(Pembayaran $pembayaran, bool $paksa = false): Pembayaran
    {
        $pembayaran->loadMissing([
            'tagihan.penyewaan.kamar',
            'tagihan.penyewaan.properti',
            'anakKos',
            'verifikator',
        ]);

        if ($pembayaran->status !== 'diverifikasi') {
            return $pembayaran;
        }

        if (! $paksa && $pembayaran->file_kwitansi && Storage::disk('public')->exists($pembayaran->file_kwitansi)) {
            return $pembayaran;
        }

        if (! $pembayaran->nomor_kwitansi) {
            $pembayaran->nomor_kwitansi = self::nomorBaru();
        }

        $tagihan = $pembayaran->tagihan;
        $penyewaan = $tagihan?->penyewaan;

        // Ikon rumah Ngekos.in (PNG transparan) di-embed base64 agar
        // DomPDF bisa merendernya sebagai watermark tanpa akses remote.
        // PNG dipakai (bukan SVG) karena render SVG DomPDF memecah
        // garis ikon menjadi bercak.
        $logoWatermark = null;
        try {
            $logoPath = public_path('images/watermark.png');

            if (! is_file($logoPath)) {
                $logoPath = public_path('images/watermark.svg');
            }

            if (! is_file($logoPath)) {
                $logoPath = public_path('favicon.svg');
            }

            if (is_file($logoPath)) {
                $mime = str_ends_with($logoPath, '.png') ? 'image/png' : 'image/svg+xml';
                $logoWatermark = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($logoPath));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $pdf = Pdf::loadView('exports.kwitansi', [
            'pembayaran' => $pembayaran,
            'tagihan' => $tagihan,
            'penyewaan' => $penyewaan,
            'anakKos' => $pembayaran->anakKos,
            'properti' => $penyewaan?->properti,
            'kamar' => $penyewaan?->kamar,
            'verifikator' => $pembayaran->verifikator,
            'logoWatermark' => $logoWatermark,
        ])->setPaper('a5', 'landscape');

        $path = 'kwitansi/'.str_replace('/', '-', $pembayaran->nomor_kwitansi).'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $pembayaran->update([
            'nomor_kwitansi' => $pembayaran->nomor_kwitansi,
            'file_kwitansi' => $path,
        ]);

        return $pembayaran->refresh();
    }

    public static function url(Pembayaran $pembayaran): ?string
    {
        return $pembayaran->file_kwitansi ? '/storage/'.$pembayaran->file_kwitansi : null;
    }

    private static function nomorBaru(): string
    {
        $prefix = 'KWT-'.now()->format('Ym').'-';

        $terakhir = Pembayaran::where('nomor_kwitansi', 'like', $prefix.'%')
            ->orderByDesc('nomor_kwitansi')
            ->value('nomor_kwitansi');

        $nomor = $terakhir ? ((int) substr($terakhir, strlen($prefix)) + 1) : 1;

        do {
            $kandidat = $prefix.str_pad((string) $nomor, 4, '0', STR_PAD_LEFT);
            $nomor++;
        } while (Pembayaran::where('nomor_kwitansi', $kandidat)->exists());

        return $kandidat;
    }
}
