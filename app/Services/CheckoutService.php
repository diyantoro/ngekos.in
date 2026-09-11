<?php

namespace App\Services;

use App\Models\Penyewaan;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu check-out / keluar sewa.
 * Aturan: patungan + ada yang stay => keluar partial via PatunganService
 * (wajib lunas porsi). Sewa tunggal / tidak ada yang stay => full checkout.
 * Kamar kembali tersedia hanya bila tidak ada penghuni aktif tersisa.
 */
class CheckoutService
{
    /**
     * @return array{jenis: 'partial'|'penuh', kamar_tersedia: bool, tagihan_belum_lunas: int}
     */
    public static function keluarPenghuni(Penyewaan $penyewaan, int $userId): array
    {
        $penyewaan->loadMissing(['kamar', 'anggotas', 'tagihans']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if (! in_array($userId, $penyewaan->idPenghuniAktif(), true)) {
            throw new DomainException('Kamu bukan penghuni aktif sewa ini.');
        }

        if ($penyewaan->anak_kos_id === $userId && $penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Pengajuan check-out sudah pernah dikirim.');
        }

        $adaYangStay = $penyewaan->anggotas->where('status', 'aktif')->where('user_id', '!=', $userId)->isNotEmpty()
            || ($penyewaan->anak_kos_id !== $userId);

        if ($adaYangStay) {
            PatunganService::keluarkan($penyewaan, $userId);

            return ['jenis' => 'partial', 'kamar_tersedia' => false, 'tagihan_belum_lunas' => 0];
        }

        $belumLunas = $penyewaan->tagihans->where('status', '!=', 'lunas')->count();

        DB::transaction(function () use ($penyewaan) {
            $penyewaan->update([
                'permintaan_keluar_pada' => now(),
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'selesai',
            ]);
            optional($penyewaan->kamar)->update(['status' => 'tersedia']);
        });

        return ['jenis' => 'penuh', 'kamar_tersedia' => true, 'tagihan_belum_lunas' => $belumLunas];
    }

    /**
     * Force check-out oleh pemilik: menutup sewa walau ada tunggakan.
     * Kamar tersedia hanya bila tak ada anggota aktif tersisa.
     *
     * @return array{jenis: 'penuh', kamar_tersedia: bool, tagihan_belum_lunas: int}
     */
    public static function checkoutPemilik(Penyewaan $penyewaan, int $pemilikId): array
    {
        $penyewaan->loadMissing(['kamar', 'anggotas', 'tagihans', 'properti', 'anakKos']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if ((int) $penyewaan->properti?->pemilik_id !== $pemilikId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        $belumLunas = $penyewaan->tagihans->where('status', '!=', 'lunas')->count();
        $adaPenghuniLain = $penyewaan->anggotas->where('status', 'aktif')->isNotEmpty();
        $kamarTersedia = ! $adaPenghuniLain;

        DB::transaction(function () use ($penyewaan, $kamarTersedia) {
            $penyewaan->update([
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'selesai',
            ]);

            if ($kamarTersedia) {
                optional($penyewaan->kamar)->update(['status' => 'tersedia']);
            }
        });

        return ['jenis' => 'penuh', 'kamar_tersedia' => $kamarTersedia, 'tagihan_belum_lunas' => $belumLunas];
    }
}
