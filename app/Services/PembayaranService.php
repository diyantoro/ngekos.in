<?php

namespace App\Services;

use App\Enums\PembayaranStatus;
use App\Models\ChatPesan;
use App\Models\Pembayaran;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu verifikasi pembayaran (dipakai API + 3 dashboard Volt).
 * Menjamin: update status, pelunasan tagihan, generate kwitansi, chat notifikasi.
 *
 * Hook booking: bila yang diverifikasi/ditolak adalah bukti DP dari booking
 * yang masih menunggu_verifikasi, sewanya otomatis diaktifkan / dibatalkan
 * (kamar dilepas kembali) dalam transaksi yang sama.
 */
class PembayaranService
{
    /**
     * @return array{pembayaran: Pembayaran, tagihan_lunas: bool, kwitansi_url: ?string, booking_disetujui: bool, booking_dibatalkan: bool}
     */
    public static function verifikasi(Pembayaran $pembayaran, int $verifikatorId, string $status = 'diverifikasi'): array
    {
        if (! in_array($status, [PembayaranStatus::Diverifikasi->value, PembayaranStatus::Ditolak->value], true)) {
            throw new DomainException('Status verifikasi tidak valid.');
        }

        if ($pembayaran->status !== 'menunggu_verifikasi') {
            throw new DomainException('Pembayaran tidak ditemukan atau sudah diproses.');
        }

        $tagihanLunas = false;
        $kwitansiUrl = null;
        $bookingDisetujui = false;
        $bookingDibatalkan = false;

        DB::transaction(function () use ($pembayaran, $verifikatorId, $status, &$tagihanLunas, &$kwitansiUrl, &$bookingDisetujui, &$bookingDibatalkan) {
            $pembayaran->update([
                'status' => $status,
                'diverifikasi_oleh' => $verifikatorId,
                'verified_at' => now(),
            ]);

            if ($status === 'diverifikasi') {
                $tagihan = $pembayaran->tagihan()->with(['pembayarans', 'penyewaan.kamar', 'penyewaan.properti'])->first()
                    ?? $pembayaran->tagihan;

                if ($tagihan && $tagihan->status === 'batal') {
                    throw new DomainException('Tagihan sudah dibatalkan karena check-out.');
                }

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

                // Booking disetujui: bukti DP lolos verifikasi => sewa aktif.
                $sewa = $tagihan?->penyewaan;
                if ($sewa && $sewa->status === 'menunggu_verifikasi' && PenyewaanService::isDp($tagihan)) {
                    $sewa->update(['status' => 'aktif']);
                    $bookingDisetujui = true;

                    ChatPesan::notifikasiTagihan(
                        (int) $sewa->properti_id,
                        (int) $sewa->anak_kos_id,
                        $verifikatorId,
                        'Kabar baik! Booking kamar '.$sewa->kamar?->nama
                            .' sudah saya setujui — sewamu resmi AKTIF sampai '
                            .($sewa->tanggal_berakhir?->locale('id')->translatedFormat('d F Y') ?? '-')
                            .'. Sisa bulan pertama Rp'.number_format(max(0, (float) $sewa->kamar?->harga_sewa_bulanan - PenyewaanService::DP_NOMINAL), 0, ',', '.')
                            .' — segera lunasi di menu Kos Saya. Terima kasih.',
                    );
                }
            }

            if ($status === 'ditolak') {
                $tagihan = $pembayaran->tagihan()->with(['penyewaan.kamar', 'penyewaan.properti', 'penyewaan.tagihans'])->first()
                    ?? $pembayaran->tagihan;

                // Booking ditolak: bukti DP tidak sah => booking dibatalkan,
                // tagihan dibatalkan, kamar dilepas kembali tersedia.
                $sewa = $tagihan?->penyewaan;
                if ($sewa && $sewa->status === 'menunggu_verifikasi' && $tagihan && PenyewaanService::isDp($tagihan)) {
                    $sewa->update([
                        'status' => 'dibatalkan',
                        'tanggal_keluar' => now()->toDateString(),
                    ]);
                    $bookingDibatalkan = true;

                    $sewa->tagihans()
                        ->whereNotIn('status', ['lunas', 'batal'])
                        ->update(['status' => 'batal']);

                    $masihDipesan = \App\Models\Penyewaan::where('kamar_id', $sewa->kamar_id)
                        ->whereIn('status', ['aktif', 'menunggu_verifikasi'])
                        ->where('id', '!=', $sewa->id)
                        ->exists();

                    if (! $masihDipesan) {
                        optional($sewa->kamar)->update(['status' => 'tersedia']);
                    }

                    ChatPesan::notifikasiTagihan(
                        (int) $sewa->properti_id,
                        (int) $sewa->anak_kos_id,
                        $verifikatorId,
                        'Maaf, bukti DP untuk booking kamar '.$sewa->kamar?->nama
                            .' tidak dapat saya verifikasi (ditolak), jadi booking dibatalkan dan kamar dilepas kembali. '
                            .'Pastikan transfer ke rekening resmi lalu booking ulang dengan bukti yang jelas. Terima kasih.',
                    );
                }
            }
        });

        return [
            'pembayaran' => $pembayaran->refresh(),
            'tagihan_lunas' => $tagihanLunas,
            'kwitansi_url' => $kwitansiUrl,
            'booking_disetujui' => $bookingDisetujui,
            'booking_dibatalkan' => $bookingDibatalkan,
        ];
    }
}
