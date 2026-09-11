<?php

namespace App\Services;

use App\Models\ChatPesan;
use App\Models\Pembayaran;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu verifikasi pembayaran (dipakai API + 3 dashboard Volt).
 * Menjamin: update status, pelunasan tagihan, generate kwitansi, chat notifikasi.
 */
class PembayaranService
{
    /**
     * @return array{pembayaran: Pembayaran, tagihan_lunas: bool, kwitansi_url: ?string}
     */
    public static function verifikasi(Pembayaran $pembayaran, int $verifikatorId, string $status = 'diverifikasi'): array
    {
        if (! in_array($status, ['diverifikasi', 'ditolak'], true)) {
            throw new DomainException('Status verifikasi tidak valid.');
        }

        if ($pembayaran->status !== 'menunggu_verifikasi') {
            throw new DomainException('Pembayaran tidak ditemukan atau sudah diproses.');
        }

        $tagihanLunas = false;
        $kwitansiUrl = null;

        DB::transaction(function () use ($pembayaran, $verifikatorId, $status, &$tagihanLunas, &$kwitansiUrl) {
            $pembayaran->update([
                'status' => $status,
                'diverifikasi_oleh' => $verifikatorId,
                'verified_at' => now(),
            ]);

            if ($status === 'diverifikasi') {
                $tagihan = $pembayaran->tagihan()->with('pembayarans')->first()
                    ?? $pembayaran->tagihan;

                TagihanService::sinkronDenda($tagihan);
                $total = $tagihan->pembayarans()->where('status', 'diverifikasi')->sum('jumlah');

                if ($total >= ((float) $tagihan->jumlah + (float) $tagihan->denda)) {
                    $tagihan->update(['status' => 'lunas']);
                    $tagihanLunas = true;
                }

                $pembayaran->refresh();
                KwitansiService::untuk($pembayaran);
                $kwitansiUrl = KwitansiService::url($pembayaran->refresh());

                ChatPesan::notifikasiPembayaranDiverifikasi($pembayaran, $verifikatorId);
            }
        });

        return [
            'pembayaran' => $pembayaran->refresh(),
            'tagihan_lunas' => $tagihanLunas,
            'kwitansi_url' => $kwitansiUrl,
        ];
    }
}
