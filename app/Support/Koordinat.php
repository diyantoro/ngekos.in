<?php

namespace App\Support;

class Koordinat
{
    /** Pusat kota untuk fallback ketika properti belum punya koordinat. */
    public static array $kota = [
        'Jakarta' => [-6.2088, 106.8456],
        'Bandung' => [-6.9175, 107.6191],
        'Bogor' => [-6.5971, 106.806],
        'Depok' => [-6.4025, 106.7942],
        'Bekasi' => [-6.2383, 106.9756],
        'Tangerang' => [-6.1751, 106.6302],
        'Yogyakarta' => [-7.7956, 110.3695],
        'Sleman' => [-7.6933, 110.359],
        'Bantul' => [-7.8881, 110.3284],
        'Janti' => [-7.7956, 110.3695],
        'Palagan' => [-7.741, 110.368],
        'Semarang' => [-6.9667, 110.4167],
        'Surabaya' => [-7.2575, 112.7521],
        'Malang' => [-7.9797, 112.6304],
        'Ngawi' => [-7.4039, 111.4483],
        'Solo' => [-7.5755, 110.8263],
        'Surakarta' => [-7.5755, 110.8263],
        'Medan' => [3.5952, 98.6722],
        'Makassar' => [-5.1477, 119.4327],
        'Denpasar' => [-8.6705, 115.2126],
    ];

    public static function kota(string $kota): ?array
    {
        $kota = trim($kota);

        return self::$kota[$kota] ?? null;
    }

    /**
     * Koordinat efektif sebuah properti: pakai latitude/longitude asli jika ada,
     * jika tidak, fallback ke pusat kota yang dikenali.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function titik(?string $kota, ?string $latitude, ?string $longitude): ?array
    {
        if ($latitude && $longitude) {
            return [(float) $latitude, (float) $longitude];
        }

        return $kota !== null && $kota !== '' ? self::kota($kota) : null;
    }
}