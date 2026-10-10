<?php

namespace App\Services;

use App\Models\Penyewaan;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu check-out / keluar sewa.
 * Aturan: patungan + ada yang stay => keluar partial via PatunganService
 * (wajib lunas porsi, langsung tanpa verifikasi karena kamar tetap terisi).
 * Sewa tunggal / tidak ada yang stay => hanya berupa PENGAJUAN check-out;
 * sewa baru benar-benar selesai setelah pemilik memverifikasi (setujui).
 * Kamar kembali tersedia hanya bila tidak ada penghuni aktif tersisa.
 *
 * Aturan tagihan (Opsi A):
 * - Semua tagihan yang sudah jatuh tempo sampai akhir bulan berjalan
 *   (tunggakan + bulan ini) wajib lunas dulu sebelum checkout (ketat).
 * - Tagihan bulan-bulan berikutnya yang belum lunas dibatalkan (status=batal).
 */
class CheckoutService
{
    public const STATUS_BATAL = 'batal';

    /**
     * Tagihan bulan berjalan (jatuh_tempo sebulan dengan tglAcuan)
     * yang belum lunas/batal.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Tagihan>
     */
    public static function tagihanBulanBerjalanBelumLunas(Penyewaan $penyewaan, ?Carbon $tglAcuan = null)
    {
        $tglAcuan ??= Carbon::today();
        $penyewaan->loadMissing(['tagihans']);

        return $penyewaan->tagihans->filter(
            fn ($t) => ! in_array($t->status, ['lunas', self::STATUS_BATAL], true)
                && $t->jatuh_tempo
                && $t->jatuh_tempo->copy()->startOfMonth()->isSameMonth($tglAcuan->copy()->startOfMonth())
        )->values();
    }

    /**
     * Semua tagihan WAJIB (tunggakan + bulan berjalan, yaitu jatuh_tempo
     * <= akhir bulan tglAcuan) yang belum lunas/batal.
     * Tagihan masa depan murni tidak ikut: ia dibatalkan, bukan dibayar.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Tagihan>
     */
    public static function tagihanWajibBelumLunas(Penyewaan $penyewaan, ?Carbon $tglAcuan = null)
    {
        $tglAcuan ??= Carbon::today();
        $batas = $tglAcuan->copy()->endOfMonth()->startOfDay();
        $penyewaan->loadMissing(['tagihans']);

        return $penyewaan->tagihans->filter(
            fn ($t) => ! in_array($t->status, ['lunas', self::STATUS_BATAL], true)
                && (! $t->jatuh_tempo || $t->jatuh_tempo->copy()->startOfDay()->lessThanOrEqualTo($batas))
        )->values();
    }

    /**
     * Tagihan masa depan (jatuh_tempo di bulan setelah tglKeluar)
     * yang belum lunas/batal.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Tagihan>
     */
    public static function tagihanMasaDepan(Penyewaan $penyewaan, ?Carbon $tglKeluar = null)
    {
        $tglKeluar ??= Carbon::today();
        $batas = $tglKeluar->copy()->endOfMonth();
        $penyewaan->loadMissing(['tagihans']);

        return $penyewaan->tagihans->filter(
            fn ($t) => ! in_array($t->status, ['lunas', self::STATUS_BATAL], true)
                && $t->jatuh_tempo
                && $t->jatuh_tempo->copy()->startOfDay()->greaterThan($batas->copy()->startOfDay())
        )->values();
    }

    /**
     * Blokir checkout bila masih ada tagihan wajib (tunggakan + bulan
     * berjalan) yang belum lunas.
     *
     * @throws DomainException
     */
    public static function pastikanBulanBerjalanLunas(Penyewaan $penyewaan, ?Carbon $tglAcuan = null): void
    {
        $kurang = self::tagihanWajibBelumLunas($penyewaan, $tglAcuan);

        if ($kurang->isNotEmpty()) {
            $total = $kurang->sum(fn ($t) => (float) $t->jumlah + (float) $t->denda);
            $tunggakan = $kurang->filter(
                fn ($t) => $t->jatuh_tempo && $t->jatuh_tempo->copy()->startOfMonth()->lessThan($tglAcuan?->copy()->startOfMonth() ?? Carbon::today()->startOfMonth())
            )->count();
            $rincian = $tunggakan > 0
                ? "termasuk {$tunggakan} tunggakan bulan lalu"
                : 'tagihan bulan ini ('.$kurang->first()->periode.')';
            throw new DomainException(
                'Lunasi dulu '.$kurang->count().' tagihan sampai bulan ini '.$rincian
                .' (total Rp'.number_format($total, 0, ',', '.')
                .') sebelum check-out.'
            );
        }
    }

    /**
     * Batalkan tagihan masa depan. Wajib dipanggil di dalam transaksi.
     *
     * @throws DomainException bila ada pembayaran menunggu verifikasi
     */
    public static function batalkanTagihanMasaDepan(Penyewaan $penyewaan, ?Carbon $tglKeluar = null): int
    {
        $tglKeluar ??= Carbon::today();
        $batas = $tglKeluar->copy()->endOfMonth()->toDateString();

        $query = $penyewaan->tagihans()
            ->whereNotIn('status', ['lunas', self::STATUS_BATAL])
            ->whereDate('jatuh_tempo', '>', $batas);

        $ids = $query->pluck('id')->all();

        if ($ids === []) {
            return 0;
        }

        $menunggu = \App\Models\Pembayaran::whereIn('tagihan_id', $ids)
            ->where('status', 'menunggu_verifikasi')
            ->exists();

        if ($menunggu) {
            throw new DomainException('Ada pembayaran tagihan bulan depan yang masih menunggu verifikasi. Tolak/verifikasi dulu sebelum check-out.');
        }

        return $query->update(['status' => self::STATUS_BATAL]);
    }

    public static function hitungBelumLunasAktif(Penyewaan $penyewaan): int
    {
        $penyewaan->loadMissing(['tagihans']);

        return $penyewaan->tagihans->whereNotIn('status', ['lunas', self::STATUS_BATAL])->count();
    }
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

        // Opsi A (ketat): bulan berjalan wajib lunas dulu sebelum boleh mengajukan.
        self::pastikanBulanBerjalanLunas($penyewaan);

        $belumLunas = self::hitungBelumLunasAktif($penyewaan);

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
     * @return array{jenis: 'penuh', kamar_tersedia: bool, tagihan_belum_lunas: int, dibatalkan: int}
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

        // Cek ulang: bulan berjalan wajib sudah lunas (anti-bypass jeda pengajuan->setujui).
        self::pastikanBulanBerjalanLunas($penyewaan);

        $adaPenghuniLain = $penyewaan->anggotas->where('status', 'aktif')->isNotEmpty();
        $kamarTersedia = ! $adaPenghuniLain;
        $dibatalkan = 0;

        DB::transaction(function () use ($penyewaan, $kamarTersedia, &$dibatalkan) {
            $penyewaan->update([
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'selesai',
            ]);

            $dibatalkan = self::batalkanTagihanMasaDepan($penyewaan);

            if ($kamarTersedia) {
                optional($penyewaan->kamar)->update(['status' => 'tersedia']);
            }
        });

        $belumLunas = self::hitungBelumLunasAktif($penyewaan->refresh());

        return ['jenis' => 'penuh', 'kamar_tersedia' => $kamarTersedia, 'tagihan_belum_lunas' => $belumLunas, 'dibatalkan' => $dibatalkan];
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
     * @return array{jenis: 'penuh', kamar_tersedia: bool, tagihan_belum_lunas: int, dibatalkan: int}
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

        // Force checkout tetap wajib lunas bulan berjalan; tunggakan lama
        // boleh tersisa sebagai info, tapi masa depan dibatalkan.
        self::pastikanBulanBerjalanLunas($penyewaan);

        $adaPenghuniLain = $penyewaan->anggotas->where('status', 'aktif')->isNotEmpty();
        $kamarTersedia = ! $adaPenghuniLain;
        $dibatalkan = 0;

        DB::transaction(function () use ($penyewaan, $kamarTersedia, &$dibatalkan) {
            $penyewaan->update([
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'selesai',
            ]);

            $dibatalkan = self::batalkanTagihanMasaDepan($penyewaan);

            if ($kamarTersedia) {
                optional($penyewaan->kamar)->update(['status' => 'tersedia']);
            }
        });

        $belumLunas = self::hitungBelumLunasAktif($penyewaan->refresh());

        return ['jenis' => 'penuh', 'kamar_tersedia' => $kamarTersedia, 'tagihan_belum_lunas' => $belumLunas, 'dibatalkan' => $dibatalkan];
    }
}
