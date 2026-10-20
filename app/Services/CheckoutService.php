<?php

namespace App\Services;

use App\Models\ChatPesan;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Tagihan;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu check-out / keluar sewa.
 * Keluar sewa selalu berupa PENGAJUAN check-out; sewa baru benar-benar selesai
 * setelah pemilik memverifikasi (setujui). Kamar kembali tersedia saat itu.
 *
 * Aturan tagihan (Opsi A):
 * - Semua tagihan yang sudah jatuh tempo sampai akhir bulan berjalan
 *   (tunggakan + bulan ini) wajib lunas dulu sebelum checkout (ketat).
 *   ATURAN INI HANYA untuk sewa yang sudah produktif (status aktif, penyewa
 *   sudah masuk). Untuk sewa berstatus 'dibooking' (DP terverifikasi tapi
 *   BELUM masuk) berlaku jalur pembatalan sewa di bawah.
 * - Tagihan bulan-bulan berikutnya yang belum lunas dibatalkan (status=batal).
 *
 * Pembatalan sewa (status 'dibooking', belum masuk kos):
 * - Ajukan → Pemilik setujui. DP HANGUS untuk pemilik (tagihan DP tetap
 *   lunas), semua tagihan lain langsung 'batal', sewa ditutup, dan kamar
 *   langsung tersedia lagi — tidak perlu menunggu sebulan.
 * - Setelah lewat TOLERANSI_BATAL_HARI dari tanggal_masuk dan penyewa
 *   belum masuk, pemilik boleh batal langsung (batalOtomatis).
 */
class CheckoutService
{
    public const STATUS_BATAL = 'batal';

    /**
     * Toleransi hari setelah tanggal_mulai sebelum pemilik boleh
     * melakukan batal otomatis (penyewa belum masuk).
     */
    public const TOLERANSI_BATAL_HARI = 5;

    /**
     * @var array<int,string> Status penyewaan yang MENAHAN kamar (tidak
     * boleh ada dua penyewaan berstatus ini pada kamar yang sama).
     */
    public const STATUS_MEMEGANG_KAMAR = ['aktif', 'dibooking', 'menunggu_verifikasi'];

    /**
     * Tagihan bulan berjalan (jatuh_tempo sebulan dengan tglAcuan)
     * yang belum lunas/batal.
     *
     * @return Collection<int, Tagihan>
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
     * @return Collection<int, Tagihan>
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
     * @return Collection<int, Tagihan>
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

        $menunggu = Pembayaran::whereIn('tagihan_id', $ids)
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
     * Apakah kamar masih dihuni/ditahan penyewa lain (data lama hasil
     * pemecahan patungan bisa punya lebih dari satu penyewaan aktif di
     * kamar yang sama).
     */
    public static function kamarMasihDihuniLain(Penyewaan $penyewaan): bool
    {
        return Penyewaan::query()
            ->where('kamar_id', $penyewaan->kamar_id)
            ->where('id', '!=', $penyewaan->id)
            ->whereIn('status', self::STATUS_MEMEGANG_KAMAR)
            ->exists();
    }

    /**
     * Pemilik/admin menandai penyewa SUDAH MASUK: sewa 'dibooking'
     * menjadi 'aktif'. Sejak titik ini berlaku aturan checkout ketat.
     */
    public static function tandaiMasuk(Penyewaan $penyewaan, int $verifikatorId, bool $verifikatorAdalahPengelola = false): void
    {
        $penyewaan->loadMissing(['kamar', 'properti', 'anakKos']);

        if ($penyewaan->status !== 'dibooking') {
            throw new DomainException('Hanya sewa berstatus Dibooking yang perlu ditandai masuk.');
        }

        if (! $verifikatorAdalahPengelola && (int) $penyewaan->properti?->pemilik_id !== $verifikatorId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        $penyewaan->update([
            'status' => 'aktif',
            'permintaan_keluar_pada' => null,
        ]);

        ChatPesan::notifikasiTagihan(
            (int) $penyewaan->properti_id,
            (int) $penyewaan->anak_kos_id,
            (int) ($penyewaan->properti?->pemilik_id ?? $penyewaan->anak_kos_id),
            'Selamat datang di '.$penyewaan->properti?->nama.'! Sewamu di kamar '.$penyewaan->kamar?->nama
                .' sudah ditandai MASUK oleh pemilik — sewa resmi AKTIF sampai '
                .($penyewaan->tanggal_berakhir?->locale('id')->translatedFormat('d F Y') ?? '-')
                .'. Jangan lupa lunasi sisa tagihan bulan pertama di menu Kos Saya. Terima kasih.',
        );
    }

    /* -------------------------------------------------------------------------
     |  PEMBATALAN SEWA (status 'dibooking' — penyewa sudah DP, belum masuk)
     | ------------------------------------------------------------------------- */

    /**
     * Jumlah hari penyewa telat masuk sejak tanggal_mulai (0 = belum telat).
     */
    public static function hariTelatMasuk(Penyewaan $penyewaan, ?Carbon $tglAcuan = null): int
    {
        if (! $penyewaan->tanggal_masuk) {
            return 0;
        }

        $acuan = ($tglAcuan ?? Carbon::today())->copy()->startOfDay();
        $mulai = $penyewaan->tanggal_masuk->copy()->startOfDay();

        return max(0, (int) $mulai->diffInDays($acuan, false));
    }

    /**
     * True bila toleransi batal otomatis sudah lewat (penyewa belum masuk
     * sampai tanggal_masuk + TOLERANSI_BATAL_HARI hari).
     */
    public static function lewatBatasBatalOtomatis(Penyewaan $penyewaan, ?Carbon $tglAcuan = null): bool
    {
        if ($penyewaan->status !== 'dibooking') {
            return false;
        }

        return self::hariTelatMasuk($penyewaan, $tglAcuan) > self::TOLERANSI_BATAL_HARI;
    }

    /**
     * Anak kos mengajukan PEMBATALAN sewa (masih dibooking, belum masuk).
     * Sewa & kamar tetap tertahan sampai pemilik menyetujui / menolak.
     *
     * @return array{jenis: 'pengajuan_batal', dp_hangus: float}
     */
    public static function ajukanBatalSewa(Penyewaan $penyewaan, int $userId): array
    {
        $penyewaan->loadMissing(['kamar', 'tagihans']);

        if ($penyewaan->status !== 'dibooking') {
            throw new DomainException('Pembatalan sewa hanya untuk booking yang belum masuk (status Dibooking).');
        }

        if ($penyewaan->anak_kos_id !== $userId) {
            throw new DomainException('Kamu bukan penghuni sewa ini.');
        }

        if ($penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Pengajuan batal sewa sudah pernah dikirim dan menunggu verifikasi pemilik.');
        }

        $dp = $penyewaan->tagihans->first(fn ($t) => PenyewaanService::isDp($t));

        DB::transaction(function () use ($penyewaan) {
            $penyewaan->update(['permintaan_keluar_pada' => now()]);
        });

        return ['jenis' => 'pengajuan_batal', 'dp_hangus' => (float) ($dp?->jumlah ?? 0)];
    }

    /**
     * Anak kos membatalkan pengajuan batal sewa sebelum diproses pemilik.
     */
    public static function batalkanAjukanBatal(Penyewaan $penyewaan, int $userId): void
    {
        if ($penyewaan->status !== 'dibooking') {
            throw new DomainException('Pembatalan sewa hanya untuk sewa yang belum masuk.');
        }

        if ($penyewaan->anak_kos_id !== $userId) {
            throw new DomainException('Kamu bukan penghuni sewa ini.');
        }

        if (! $penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Tidak ada pengajuan batal sewa yang perlu dibatalkan.');
        }

        $penyewaan->update(['permintaan_keluar_pada' => null]);
    }

    /**
     * Pemilik menyetujui ajukan batal sewa: DP hangus, tagihan lain batal,
     * sewa ditutup, kamar langsung tersedia.
     *
     * @return array{jenis: 'batal_sewa', kamar_tersedia: bool, dp_hangus: float}
     */
    public static function setujuiBatalSewa(Penyewaan $penyewaan, int $verifikatorId, bool $verifikatorAdalahPengelola = false): array
    {
        $penyewaan->loadMissing(['kamar', 'tagihans', 'properti', 'anakKos']);

        if ($penyewaan->status !== 'dibooking') {
            throw new DomainException('Pembatalan sewa hanya untuk sewa yang belum masuk.');
        }

        if (! $penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Tidak ada pengajuan batal sewa untuk sewa ini.');
        }

        if (! $verifikatorAdalahPengelola && (int) $penyewaan->properti?->pemilik_id !== $verifikatorId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        return self::selesaikanBatalSewa($penyewaan, 'disetujui_pemilik');
    }

    /**
     * Pemilik menolak ajukan batal sewa (menunggu sewa masuk).
     */
    public static function tolakBatalSewa(Penyewaan $penyewaan, int $verifikatorId, bool $verifikatorAdalahPengelola = false): void
    {
        $penyewaan->loadMissing(['properti']);

        if ($penyewaan->status !== 'dibooking') {
            throw new DomainException('Pembatalan sewa hanya untuk sewa yang belum masuk.');
        }

        if (! $penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Tidak ada pengajuan batal sewa untuk sewa ini.');
        }

        if (! $verifikatorAdalahPengelola && (int) $penyewaan->properti?->pemilik_id !== $verifikatorId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        $penyewaan->update(['permintaan_keluar_pada' => null]);
    }

    /**
     * Batal otomatis oleh pemilik: toleransi telat masuk sudah lewat,
     * sewa masih dibooking. DP hangus, kamar langsung tersedia.
     *
     * @return array{jenis: 'batal_sewa', kamar_tersedia: bool, dp_hangus: float}
     */
    public static function batalOtomatis(Penyewaan $penyewaan, int $pemilikId): array
    {
        $penyewaan->loadMissing(['kamar', 'tagihans', 'properti', 'anakKos']);

        if ($penyewaan->status !== 'dibooking') {
            throw new DomainException('Batal otomatis hanya untuk sewa yang belum masuk.');
        }

        if ((int) $penyewaan->properti?->pemilik_id !== $pemilikId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        if (! self::lewatBatasBatalOtomatis($penyewaan)) {
            $telat = self::hariTelatMasuk($penyewaan);

            throw new DomainException(
                'Belum boleh dibatalkan otomatis. Batas ditunggul hari ke-'
                .self::TOLERANSI_BATAL_HARI.' setelah tanggal masuk'
                .($telat > 0 ? ' (baru telat '.$telat.' hari)' : '').'.'
            );
        }

        return self::selesaikanBatalSewa($penyewaan, 'otomatis_pemilik');
    }

    /**
     * Eksekusi pembatalan sewa (satu pintu): DP hangus, tagihan lain batal,
     * sewa ditutup 'dibatalkan', kamar kembali tersedia, pesan chat terkirim.
     *
     * @return array{jenis: 'batal_sewa', kamar_tersedia: bool, dp_hangus: float}
     */
    private static function selesaikanBatalSewa(Penyewaan $penyewaan, string $penyebab): array
    {
        $dp = $penyewaan->tagihans->first(fn ($t) => PenyewaanService::isDp($t));
        $dpHangus = (float) ($dp?->jumlah ?? 0);
        $kamarNama = $penyewaan->kamar?->nama ?? '-';
        $propertiNama = $penyewaan->properti?->nama ?? 'kos';
        $namaAnak = $penyewaan->anakKos?->nama ?? 'penyewa';

        DB::transaction(function () use ($penyewaan) {
            $penyewaan->update([
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'dibatalkan',
                'permintaan_keluar_pada' => null,
                'peringatan_batal_pada' => now(),
            ]);

            // DP tetap LUNAS (uang hangus untuk pemilik); selain DP dibatalkan.
            $penyewaan->tagihans()
                ->whereNotIn('status', ['lunas', self::STATUS_BATAL])
                ->whereNot(fn ($q) => $q->where('jenis', 'dp')->orWhere('periode', 'like', 'DP - %'))
                ->update(['status' => self::STATUS_BATAL]);

            if (! self::kamarMasihDihuniLain($penyewaan)) {
                optional($penyewaan->kamar)->update(['status' => 'tersedia']);
            }
        });

        // Pesan terkirim (satu thread chat dibaca kedua pihak).
        $alasan = $penyebab === 'otomatis_pemilik'
            ? 'Sudah telat '.self::hariTelatMasuk($penyewaan).' hari tanpa konfirmasi masuk'
            : 'Pengajuan pembatalan disetujui';

        ChatPesan::notifikasiTagihan(
            (int) $penyewaan->properti_id,
            (int) $penyewaan->anak_kos_id,
            (int) ($penyewaan->properti?->pemilik_id ?? $penyewaan->anak_kos_id),
            $alasan.' — booking kamar '.$kamarNama.' di '.$propertiNama
                .' (a.n. '.$namaAnak.') telah dibatalkan. '
                .'DP Rp'.number_format($dpHangus, 0, ',', '.').' hangus dan tidak dikembalikan. '
                .'Kamar sudah kembali tersedia. Terima kasih.',
        );

        return ['jenis' => 'batal_sewa', 'kamar_tersedia' => true, 'dp_hangus' => $dpHangus];
    }

    /**
     * Penghuni mengajukan check-out. Sewa tetap aktif & kamar tetap terisi
     * sampai pemilik menyetujui lewat menu Penyewa.
     *
     * @return array{jenis: 'pengajuan', kamar_tersedia: bool, tagihan_belum_lunas: int}
     */
    public static function keluarPenghuni(Penyewaan $penyewaan, int $userId): array
    {
        $penyewaan->loadMissing(['kamar', 'tagihans']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if ($penyewaan->anak_kos_id !== $userId) {
            throw new DomainException('Kamu bukan penghuni aktif sewa ini.');
        }

        if ($penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Pengajuan check-out sudah pernah dikirim dan menunggu verifikasi pemilik.');
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
        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if ($penyewaan->anak_kos_id !== $userId) {
            throw new DomainException('Kamu bukan penghuni aktif sewa ini.');
        }

        if (! $penyewaan->permintaan_keluar_pada) {
            throw new DomainException('Tidak ada pengajuan check-out yang perlu dibatalkan.');
        }

        $penyewaan->update(['permintaan_keluar_pada' => null]);
    }

    /**
     * Pemilik menyetujui pengajuan check-out: sewa ditutup dan kamar
     * kembali tersedia.
     *
     * @return array{jenis: 'penuh', kamar_tersedia: bool, tagihan_belum_lunas: int, dibatalkan: int}
     */
    public static function setujuiCheckout(Penyewaan $penyewaan, int $verifikatorId, bool $verifikatorAdalahPengelola = false): array
    {
        $penyewaan->loadMissing(['kamar', 'tagihans', 'properti', 'anakKos']);

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

        $kamarTersedia = ! self::kamarMasihDihuniLain($penyewaan);
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
     * Kamar langsung dikembalikan ke tersedia.
     *
     * @return array{jenis: 'penuh', kamar_tersedia: bool, tagihan_belum_lunas: int, dibatalkan: int}
     */
    public static function checkoutPemilik(Penyewaan $penyewaan, int $pemilikId): array
    {
        $penyewaan->loadMissing(['kamar', 'tagihans', 'properti', 'anakKos']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if ((int) $penyewaan->properti?->pemilik_id !== $pemilikId) {
            throw new DomainException('Bukan properti kelolaanmu.');
        }

        // Force checkout tetap wajib lunas bulan berjalan; tunggakan lama
        // boleh tersisa sebagai info, tapi masa depan dibatalkan.
        self::pastikanBulanBerjalanLunas($penyewaan);

        $kamarTersedia = ! self::kamarMasihDihuniLain($penyewaan);
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
