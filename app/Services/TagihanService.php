<?php

namespace App\Services;

use App\Models\Pengaturan;
use App\Models\Tagihan;
use Illuminate\Support\Carbon;

/**
 * Satu-satunya pintu hitung denda + total tagihan.
 * Dipakai scheduler, API, Livewire, dan verifikasi pembayaran
 * agar nominal selalu sesuai hari bayar (bukan hasil cache 00:05).
 */
class TagihanService
{
    public static function dendaPerHari(Tagihan $tagihan): float
    {
        $tagihan->loadMissing('penyewaan.properti');

        return (float) ($tagihan->penyewaan?->properti?->denda_per_hari ?? Pengaturan::dendaPerHari());
    }

    public static function hariTelat(Tagihan $tagihan, ?Carbon $pada = null): int
    {
        if (! $tagihan->jatuh_tempo) {
            return 0;
        }

        $pada ??= Carbon::today();
        $selisih = $pada->copy()->startOfDay()->diffInDays($tagihan->jatuh_tempo->copy()->startOfDay(), false);

        return (int) max(0, -$selisih);
    }

    public static function dendaBerjalan(Tagihan $tagihan, ?Carbon $pada = null): float
    {
        $perHari = self::dendaPerHari($tagihan);

        if ($perHari <= 0) {
            return 0.0;
        }

        return round(self::hariTelat($tagihan, $pada) * $perHari, 2);
    }

    public static function totalBerjalan(Tagihan $tagihan, ?Carbon $pada = null): float
    {
        return round((float) $tagihan->jumlah + self::dendaBerjalan($tagihan, $pada), 2);
    }

    public static function selisihHari(Tagihan $tagihan, ?Carbon $pada = null): int
    {
        if (! $tagihan->jatuh_tempo) {
            return 0;
        }

        $pada ??= Carbon::today();

        return (int) $pada->copy()->startOfDay()->diffInDays($tagihan->jatuh_tempo->copy()->startOfDay(), false);
    }

    /**
     * Sinkron kolom denda ke nilai berjalan. Return denda terbaru.
     */
    public static function sinkronDenda(Tagihan $tagihan, ?Carbon $pada = null): float
    {
        if ($tagihan->status === 'lunas') {
            return (float) $tagihan->denda;
        }

        $denda = self::dendaBerjalan($tagihan, $pada);

        if ((float) $tagihan->denda !== $denda) {
            $tagihan->update(['denda' => $denda]);
            $tagihan->refresh();
        }

        return $denda;
    }

    /**
     * Rincian siap tampil / validasi bayar (termasuk patungan).
     *
     * @return array{sewa: float, denda: float, hari_telat: int, denda_per_hari: float, total: float, porsi: float|null}
     */
    public static function rincian(Tagihan $tagihan, ?Carbon $pada = null): array
    {
        $tagihan->loadMissing(['penyewaan.anggotas']);
        self::sinkronDenda($tagihan, $pada);

        $porsi = null;

        if ($tagihan->penyewaan) {
            $porsi = PatunganService::porsiTagihan($tagihan->penyewaan, $tagihan);
        }

        return [
            'sewa' => (float) $tagihan->jumlah,
            'denda' => (float) $tagihan->denda,
            'hari_telat' => self::hariTelat($tagihan, $pada),
            'denda_per_hari' => self::dendaPerHari($tagihan),
            'total' => (float) $tagihan->jumlah + (float) $tagihan->denda,
            'porsi' => $porsi,
        ];
    }

    /**
     * Nominal yang wajib dibayar satu user: total penuh untuk tunggal,
     * sisa porsi (porsi - sudah diverifikasi uid) untuk patungan.
     */
    public static function wajibBayar(Tagihan $tagihan, int $userId, ?Carbon $pada = null): float
    {
        $rincian = self::rincian($tagihan, $pada);

        if ($tagihan->penyewaan?->isPatungan() && $rincian['porsi'] !== null) {
            $sudah = (float) $tagihan->pembayarans()->where('anak_kos_id', $userId)->where('status', 'diverifikasi')->sum('jumlah');

            return round(max(0, $rincian['porsi'] - $sudah), 2);
        }

        return $rincian['total'];
    }

    public static function rupiah(float $nilai): string
    {
        return 'Rp'.number_format($nilai, 0, ',', '.');
    }
}
