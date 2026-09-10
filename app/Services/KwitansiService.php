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
    public static function untuk(Pembayaran $pembayaran): Pembayaran
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

        if ($pembayaran->file_kwitansi && Storage::disk('public')->exists($pembayaran->file_kwitansi)) {
            return $pembayaran;
        }

        if (! $pembayaran->nomor_kwitansi) {
            $pembayaran->nomor_kwitansi = self::nomorBaru();
        }

        $tagihan = $pembayaran->tagihan;
        $penyewaan = $tagihan?->penyewaan;

        $pdf = Pdf::loadView('exports.kwitansi', [
            'pembayaran' => $pembayaran,
            'tagihan' => $tagihan,
            'penyewaan' => $penyewaan,
            'anakKos' => $pembayaran->anakKos,
            'properti' => $penyewaan?->properti,
            'kamar' => $penyewaan?->kamar,
            'verifikator' => $pembayaran->verifikator,
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
