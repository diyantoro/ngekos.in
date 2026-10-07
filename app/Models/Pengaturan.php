<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Pengaturan aplikasi berbasis key-value (tabel pengaturans).
 * Nilai dibaca lewat helper statis agar bisa dipakai dari mana saja,
 * hasil pembacaan di-cache sampai ada perubahan.
 */
class Pengaturan extends Model
{
    protected $fillable = ['kunci', 'nilai'];

    /**
     * Semua pengaturan sebagai pasangan kunci => nilai (tercache).
     *
     * @return array<string, string|null>
     */
    private static ?array $memoSemua = null;

    public static function semua(): array
    {
        if (self::$memoSemua !== null) {
            return self::$memoSemua;
        }

        return self::$memoSemua = Cache::rememberForever('pengaturan.semua', function () {
            return static::query()->pluck('nilai', 'kunci')->all();
        });
    }

    public static function ambil(string $kunci, mixed $default = null): mixed
    {
        return static::semua()[$kunci] ?? $default;
    }

    /**
     * Simpan beberapa pengaturan sekaligus lalu segarkan cache.
     *
     * @param  array<string, string|null>  $pasangan
     */
    public static function simpanBanyak(array $pasangan): void
    {
        foreach ($pasangan as $kunci => $nilai) {
            static::query()->updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
        }

        self::$memoSemua = null;
        Cache::forget('pengaturan.semua');
    }

    /**
     * Nama situs yang tampil di judul, logo, dan footer.
     */
    public static function namaSitus(): string
    {
        return (string) (static::ambil('situs.nama') ?: config('app.name', 'Ngekos.in'));
    }

    /**
     * Deskripsi singkat situs untuk footer/meta.
     */
    public static function deskripsiSitus(): string
    {
        return (string) (static::ambil('situs.deskripsi') ?: 'Platform pencari kos & pemilik kos.');
    }

    /**
     * Jatuh tempo tagihan untuk periode bulan tertentu sesuai pengaturan:
     * "akhir" = akhir bulan, atau angka 1-28 = tanggal tetap tiap bulan.
     */
    public static function jatuhTempoUntuk(Carbon $bulan): Carbon
    {
        $aturan = (string) static::ambil('kos.jatuh_tempo', 'akhir');

        if ($aturan !== 'akhir' && ctype_digit($aturan)) {
            return $bulan->copy()->day(min(28, max(1, (int) $aturan)));
        }

        return $bulan->copy()->endOfMonth();
    }

    /**
     * Denda keterlambatan default global per hari (Rp).
     * Bisa ditimpa per properti lewat properti.denda_per_hari.
     */
    public static function dendaPerHari(): float
    {
        return (float) static::ambil('kos.denda_per_hari', 0);
    }

    public static function landingHeroId(): ?int
    {
        $id = (int) static::ambil('landing.hero_properti_id', 0);

        return $id > 0 ? $id : null;
    }

    public static function simpanLandingHero(?int $id): void
    {
        static::simpanBanyak(['landing.hero_properti_id' => $id && $id > 0 ? (string) $id : null]);
    }

    /**
     * @return array<int>
     */
    public static function landingPromoIds(): array
    {
        $mentah = static::ambil('landing.promo_ids', null);

        if ($mentah === null || $mentah === '') {
            return [];
        }

        $data = json_decode((string) $mentah, true);

        if (! is_array($data)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($v) => (int) $v > 0 ? (int) $v : null,
            $data
        )));
    }

    public static function simpanLandingPromo(array $ids): void
    {
        $bersih = array_values(array_unique(array_filter(array_map(
            fn ($v) => (int) $v > 0 ? (int) $v : null,
            $ids
        ))));

        static::simpanBanyak(['landing.promo_ids' => json_encode(array_slice($bersih, 0, 8))]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function landingBanners(bool $hanyaAktif = false): array
    {
        $mentah = static::ambil('landing.banners', null);

        if ($mentah === null || $mentah === '') {
            return [];
        }

        $data = json_decode((string) $mentah, true);

        if (! is_array($data)) {
            return [];
        }

        $daftar = array_values(array_filter(array_map(function ($b) {
            if (! is_array($b)) {
                return null;
            }

            return [
                'brand' => (string) ($b['brand'] ?? ''),
                'tagline' => (string) ($b['tagline'] ?? ''),
                'desc' => (string) ($b['desc'] ?? ''),
                'gradient' => (string) ($b['gradient'] ?? 'from-blue-700 via-sky-600 to-sky-500'),
                'accent' => (string) ($b['accent'] ?? 'text-sky-200'),
                'icon' => (string) ($b['icon'] ?? 'wifi'),
                'image' => isset($b['image']) && $b['image'] !== '' ? (string) $b['image'] : null,
                'aktif' => ! array_key_exists('aktif', $b) || (bool) $b['aktif'],
            ];
        }, $data)));

        $daftar = array_values(array_filter($daftar, fn ($b) => $b['brand'] !== ''));

        if ($hanyaAktif) {
            $daftar = array_values(array_filter($daftar, fn ($b) => $b['aktif']));
        }

        return $daftar;
    }

    public static function adaKustomLandingBanners(): bool
    {
        return static::ambil('landing.banners', null) !== null;
    }

    public static function simpanLandingBanners(array $banners): void
    {
        static::simpanBanyak(['landing.banners' => json_encode(array_values($banners))]);
    }

    /**
     * Jeda auto-slide carousel promo (milidetik). Bisa diatur super-admin.
     * Dijepit 1000–10000 agar tidak terlalu cepat/lambat; default 3000.
     */
    public static function promoIntervalMs(): int
    {
        $ms = (int) static::ambil('landing.promo_interval_ms', 3000);

        return min(10000, max(1000, $ms > 0 ? $ms : 3000));
    }

    public static function simpanPromoIntervalMs(int $ms): void
    {
        $ms = $ms > 0 ? $ms : 3000;

        static::simpanBanyak(['landing.promo_interval_ms' => (string) min(10000, max(1000, $ms))]);
    }

    /**
     * Batas akhir hitung mundur Promo Ngebut yang diatur super-admin.
     * Null = belum diatur / tidak valid.
     */
    public static function promoBerakhirPada(): ?Carbon
    {
        $mentah = trim((string) (static::ambil('landing.promo_berakhir_pada') ?? ''));

        if ($mentah === '') {
            return null;
        }

        try {
            return Carbon::parse($mentah);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function simpanPromoBerakhirPada(?Carbon $waktu): void
    {
        static::simpanBanyak(['landing.promo_berakhir_pada' => $waktu?->toIso8601String()]);
    }

    /**
     * True jika super-admin mengatur batas akhir sendiri yang masih di masa depan.
     */
    public static function promoBerakhirKustom(): bool
    {
        $waktu = static::promoBerakhirPada();

        return $waktu !== null && $waktu->isFuture();
    }

    /**
     * Timestamp detik untuk hitung mundur Promo Ngebut:
     * pengaturan super-admin jika masih di masa depan,
     * sonst fallback akhir bulan berjalan (perilaku lama).
     */
    public static function akhirPromoTimestamp(): int
    {
        $waktu = static::promoBerakhirPada();

        if ($waktu !== null && $waktu->isFuture()) {
            return $waktu->getTimestamp();
        }

        return mktime(23, 59, 59, (int) date('n'), (int) date('t'), (int) date('Y'));
    }
}
