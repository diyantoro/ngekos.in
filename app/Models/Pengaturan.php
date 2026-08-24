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
    public static function semua(): array
    {
        return Cache::rememberForever('pengaturan.semua', function () {
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
}
