<?php

namespace App\Services;

use App\Models\Penyewaan;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu check-out / keluar sewa.
 * Aturan: patungan + ada yang stay => keluar partial via PatunganService
 * (wajib lunas porsi, langsung tanpa verifikasi karena kamar tetap terisi).
 * Sewa tunggal / tidak ada yang stay => hanya berupa PENGAJUAN check-out;
 * sewa baru benar-benar selesai setelah pemilik memverifikasi (setujui).
 * Kamar kembali tersedia hanya bila tidak ada penghuni aktif tersisa.
 */
class CheckoutService
{
    /**
     * Penghuni mengajukan check-out. Sewa tetap aktif & kamar tetap terisi
     * sampai pemilik menyetujui lewat menu Penyewa.
     *
     * @return array{jenis: 'partial'|'pengajuan', kamar_tersedia: bool, tagihan_belum_lunas: int}
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

        if ($penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Pengajuan check-out sudah pernah dikirim dan menunggu verifikasi pemilik.');
        }

        $adaYangStay = $penyewaan->anggotas->where('status', 'aktif')->where('user_id', '!=', $userId)->isNotEmpty()
            || ($penyewaan->anak_kos_id !== $userId);

        if ($adaYangStay) {
            PatunganService::keluarkan($penyewaan, $userId);

            return ['jenis' => 'partial', 'kamar_tersedia' => false, 'tagihan_belum_lunas' => 0];
        }

        $belumLunas = $penyewaan->tagihans->where('status', '!=', 'lunas')->count();

        DB::transaction(function () use ($penyewaan) {
            $penyewaan->update(['permintaan_keluar_pada' => now()]);
        });

        return ['jenis' => 'pengajuan', 'kamar_tersedia' => false, 'tagihan_belum_lunas' => $belumLunas];
    }

    /**
     * Penghuni membatalkan pengajuannya sendiri sebelum diverifikasi pemilik.
     */
    public static function batalkanPengajuan(Penyewaan $penyewaan, int $userId): void
    {
        $penyewaan->loadMissing(['anggotas']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if (! in_array($userId, $penyewaan->idPenghuniAktif(), true)) {
            throw new DomainException('Kamu bukan penghuni aktif sewa ini.');
        }

        if (! $penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Tidak ada pengajuan check-out yang perlu dibatalkan.');
        }

        $penyewaan->update(['permintaan_keluar_pada' => null]);
    }

    /**
     * Pemilik menyetujui pengajuan check-out: sewa ditutup dan kamar
     * kembali tersedia (bila tak ada anggota patungan aktif tersisa).
     *
     * @return array{jenis: 'penuh', kamar_tersedia: bool, tagihan_belum_lunas: int}
     */
    public static function setujuiCheckout(Penyewaan $penyewaan, int $verifikatorId, bool $verifikatorAdalahPengelola = false): array
    {
        $penyewaan->loadMissing(['kamar', 'anggotas', 'tagihans', 'properti', 'anakKos']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if (! $penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Tidak ada pengajuan check-out untuk sewa ini.');
        }

        if (! $verifikatorAdalahPengelola && (int) $penyewaan->properti?->pemilik_id !== $verifikatorId) {
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

    /**
     * Pemilik menolak pengajuan check-out: sewa tetap berjalan aktif.
     */
    public static function tolakCheckout(Penyewaan $penyewaan, int $verifikatorId, bool $verifikatorAdalahPengelola = false): void
    {
        $penyewaan->loadMissing(['properti']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if (! $penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Tidak ada pengajuan check-out untuk sewa ini.');
        }

        if (! $verifikatorAdalahPengelola && (int) $penyewaan->properti?->pemilik_id !== $verifikatorId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        $penyewaan->update(['permintaan_keluar_pada' => null]);
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
