<?php

namespace App\Services;

use App\Models\ChatPesan;
use App\Models\Pengaturan;
use App\Models\Penyewaan;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Perpanjangan sewa bulanan 1 bulan.
 * - H-3 sebelum tanggal_berakhir: status -> 'ditawarkan' + chat ke anak kos.
 * - Anak kos jawab 'lanjut': tanggal_berakhir +1 bulan, tagihan bulan baru,
 *   status direset ke null (siklus berikutnya ditawarkan lagi H-3).
 * - Anak kos jawab 'tidak' / diam sampai lewat berakhir: scheduler menutup
 *   sewa (status selesai) + kamar otomatis tersedia lagi.
 */
class PerpanjanganService
{
    public const STATUS_DITAWARKAN = 'ditawarkan';

    public const STATUS_TIDAK = 'tidak';

    public static function sisaHari(Penyewaan $penyewaan, ?Carbon $pada = null): ?int
    {
        if (! $penyewaan->tanggal_berakhir) {
            return null;
        }

        $pada ??= Carbon::today();

        return (int) $pada->copy()->startOfDay()
            ->diffInDays($penyewaan->tanggal_berakhir->copy()->startOfDay(), false);
    }

    public static function perluDitawarkan(Penyewaan $penyewaan, ?Carbon $pada = null): bool
    {
        if ($penyewaan->status !== 'aktif' || ! $penyewaan->tanggal_berakhir) {
            return false;
        }

        if ($penyewaan->perpanjangan_status !== null) {
            return false;
        }

        $sisa = self::sisaHari($penyewaan, $pada);

        return $sisa !== null && $sisa <= 3 && $sisa >= 0;
    }

    public static function tawarkan(Penyewaan $penyewaan, ?Carbon $pada = null): bool
    {
        $pada ??= Carbon::today();

        if (! self::perluDitawarkan($penyewaan, $pada)) {
            return false;
        }

        $penyewaan->loadMissing(['kamar', 'properti']);

        $sisa = self::sisaHari($penyewaan, $pada);
        $berakhir = $penyewaan->tanggal_berakhir->locale('id')->translatedFormat('d F Y');
        $harga = (float) ($penyewaan->kamar?->harga_sewa_bulanan ?? 0);

        DB::transaction(function () use ($penyewaan, $pada, $sisa, $berakhir, $harga) {
            $penyewaan->update([
                'perpanjangan_status' => self::STATUS_DITAWARKAN,
                'perpanjangan_ditawarkan_pada' => $pada->toDateString(),
            ]);

            ChatPesan::notifikasiTagihan(
                (int) $penyewaan->properti_id,
                (int) $penyewaan->anak_kos_id,
                (int) ($penyewaan->properti?->pemilik_id ?? $penyewaan->anak_kos_id),
                'Masa sewamu di kamar '.$penyewaan->kamar?->nama
                    .' akan berakhir pada '.$berakhir
                    .' (sisa '.$sisa.' hari). Mau lanjut 1 bulan lagi'
                    .($harga > 0 ? ' (Rp'.number_format($harga, 0, ',', '.').'/bulan)' : '')
                    .'? Pilih LANJUT di menu Kos Saya. Kalau pilih TIDAK (atau tidak menjawab sampai tanggal berakhir), sewamu otomatis selesai dan kamar kembali tersedia.',
            );
        });

        return true;
    }

    /**
     * Anak kos memilih LANJUT: perpanjang 1 bulan + buat tagihan bulan baru.
     */
    public static function lanjutkan(Penyewaan $penyewaan, int $userId): Penyewaan
    {
        $penyewaan->loadMissing(['kamar', 'tagihans', 'properti']);

        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if ($penyewaan->anak_kos_id !== $userId) {
            throw new DomainException('Kamu bukan penghuni aktif sewa ini.');
        }

        if (! $penyewaan->tanggal_berakhir) {
            throw new DomainException('Sewa ini tidak memakai sistem perpanjangan bulanan.');
        }

        $harga = (float) ($penyewaan->kamar?->harga_sewa_bulanan ?? 0);

        if ($harga <= 0) {
            throw new DomainException('Harga sewa bulanan kamar belum ditetapkan.');
        }

        DB::transaction(function () use ($penyewaan, $harga) {
            // Bulan baru = 1 bulan kalender setelah tagihan terakhir (anti-dobel,
            // selaras dengan ProsesTagihan yang menagih per bulan kalender).
            $terakhir = $penyewaan->tagihans
                ->whereNotIn('status', ['batal'])
                ->filter(fn ($t) => $t->jatuh_tempo !== null)
                ->sortBy(fn ($t) => $t->jatuh_tempo->toDateString())
                ->last()?->jatuh_tempo;

            $dasar = $terakhir
                ? $terakhir->copy()->addMonthNoOverflow()->startOfMonth()
                : $penyewaan->tanggal_berakhir->copy()->addDay()->startOfMonth();

            $berakhirBaru = $penyewaan->tanggal_berakhir->copy()->addMonthNoOverflow();

            $penyewaan->update([
                'tanggal_berakhir' => $berakhirBaru->toDateString(),
                'perpanjangan_status' => null,
                'perpanjangan_ditawarkan_pada' => null,
            ]);

            $sudahAda = $penyewaan->tagihans()
                ->where('status', '!=', 'batal')
                ->whereMonth('jatuh_tempo', $dasar->month)
                ->whereYear('jatuh_tempo', $dasar->year)
                ->exists();

            if (! $sudahAda) {
                $penyewaan->tagihans()->create([
                    'periode' => $dasar->locale('id')->translatedFormat('F Y'),
                    'jumlah' => $harga,
                    'jenis' => 'sewa',
                    'denda' => 0,
                    'jatuh_tempo' => Pengaturan::jatuhTempoUntuk($dasar)->toDateString(),
                    'status' => 'belum_bayar',
                ]);
            }

            ChatPesan::notifikasiTagihan(
                (int) $penyewaan->properti_id,
                (int) $penyewaan->anak_kos_id,
                (int) $penyewaan->anak_kos_id,
                'Kamu memilih LANJUT 1 bulan sampai '
                    .$berakhirBaru->locale('id')->translatedFormat('d F Y')
                    .'. Tagihan bulan '.$dasar->locale('id')->translatedFormat('F Y')
                    .' (Rp'.number_format($harga, 0, ',', '.').') sudah dibuat — segera bayar. Terima kasih.',
            );
        });

        return $penyewaan->refresh();
    }

    /**
     * Anak kos memilih TIDAK: sewa tetap aktif sampai tanggal_berakhir,
     * lalu scheduler otomatis menutup + mengosongkan kamar.
     */
    public static function tolak(Penyewaan $penyewaan, int $userId): void
    {
        if ($penyewaan->status !== 'aktif') {
            throw new DomainException('Penyewaan tidak aktif.');
        }

        if ($penyewaan->anak_kos_id !== $userId) {
            throw new DomainException('Kamu bukan penghuni aktif sewa ini.');
        }

        if (! $penyewaan->tanggal_berakhir) {
            throw new DomainException('Sewa ini tidak memakai sistem perpanjangan bulanan.');
        }

        $penyewaan->update(['perpanjangan_status' => self::STATUS_TIDAK]);
    }

    /**
     * Scheduler harian: tawarkan H-3 + tutup sewa yang sudah lewat berakhir.
     *
     * @return array{ditawarkan: int, ditutup: int}
     */
    public static function prosesHarian(?Carbon $pada = null): array
    {
        $pada ??= Carbon::today();
        $hasil = ['ditawarkan' => 0, 'ditutup' => 0];

        Penyewaan::query()
            ->where('status', 'aktif')
            ->whereNotNull('tanggal_berakhir')
            ->with(['kamar', 'properti', 'tagihans'])
            ->chunkById(100, function ($sewaans) use ($pada, &$hasil) {
                foreach ($sewaans as $sewaan) {
                    if (self::tawarkan($sewaan, $pada)) {
                        $hasil['ditawarkan']++;
                    }

                    if (self::perluDitutup($sewaan, $pada)) {
                        self::tutupBerakhir($sewaan);
                        $hasil['ditutup']++;
                    }
                }
            });

        return $hasil;
    }

    public static function perluDitutup(Penyewaan $penyewaan, ?Carbon $pada = null): bool
    {
        if ($penyewaan->status !== 'aktif' || ! $penyewaan->tanggal_berakhir) {
            return false;
        }

        $pada ??= Carbon::today();

        return $pada->copy()->startOfDay()->greaterThan($penyewaan->tanggal_berakhir->copy()->startOfDay());
    }

    /**
     * Tutup sewa yang lewat tanggal_berakhir: status selesai,
     * kamar otomatis tersedia, tagihan masa depan dibatalkan.
     */
    public static function tutupBerakhir(Penyewaan $penyewaan): void
    {
        $penyewaan->loadMissing(['kamar', 'tagihans', 'properti', 'anakKos']);

        if ($penyewaan->status !== 'aktif' || ! self::perluDitutup($penyewaan)) {
            return;
        }

        DB::transaction(function () use ($penyewaan) {
            $penyewaan->update([
                'tanggal_keluar' => $penyewaan->tanggal_berakhir->toDateString(),
                'status' => 'selesai',
            ]);

            CheckoutService::batalkanTagihanMasaDepan($penyewaan, $penyewaan->tanggal_berakhir);

            if (! CheckoutService::kamarMasihDihuniLain($penyewaan)) {
                optional($penyewaan->kamar)->update(['status' => 'tersedia']);
            }
        });
    }
}
