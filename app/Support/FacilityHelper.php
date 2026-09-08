<?php

namespace App\Support;

/**
 * Bantu menyeragamkan label fasilitas agar tersimpan konsisten di web
 * maupun mobile. Alias varian penulisan dipetakan ke label kanonik;
 * label yang tidak dikenal dibiarkan apa adanya (tidak merusak data).
 */
class FacilityHelper
{
    /**
     * Label kanonik yang dikenali, terurut.
     */
    public const CANONICAL = [
        'AC',
        'Kipas Angin',
        'Kulkas',
        'Kasur',
        'Lemari',
        'Meja & Kursi',
        'Meja',
        'Kursi',
        'Rak Baju',
        'Jendela',
        'Bantal',
        'Cermin',
        'Ventilasi',
        'Kloset Duduk',
        'Kamar Mandi Dalam',
        'Kamar Mandi Luar',
        'Shower',
        'Ember',
        'Air Panas',
        'WiFi',
        'Dapur',
        'Dapur Bersama',
        'Ruang Tamu',
        'Penjaga Kos',
        'Tempat Jemuran',
        'Laundry',
        'Dilarang Merokok',
        'Parkir',
        'Parkir Mobil',
        'Parkir Motor',
        'Parkir Motor & Sepeda',
        'Lawan Jenis',
        'Akses',
        'Tamu',
        'Termasuk Listrik',
        'Tidak Termasuk Listrik',
    ];

    /**
     * Peta varian (dinormalisasi) -> label kanonik.
     * Normalisasi: huruf kecil + buang semua karakter non-alfanumerik.
     */
    private const ALIAS = [
        'ac' => 'AC',
        'kipas' => 'Kipas Angin',
        'kipasangin' => 'Kipas Angin',
        'wifi' => 'WiFi',
        'kulkas' => 'Kulkas',
        'kasur' => 'Kasur',
        'lemari' => 'Lemari',
        'lemaristorage' => 'Lemari',
        'meja' => 'Meja',
        'kursi' => 'Kursi',
        'mejakursi' => 'Meja & Kursi',
        'mejadankursi' => 'Meja & Kursi',
        'rakbaju' => 'Rak Baju',
        'jendela' => 'Jendela',
        'bantal' => 'Bantal',
        'cermin' => 'Cermin',
        'ventilasi' => 'Ventilasi',
        'klosetduduk' => 'Kloset Duduk',
        'kamarmandidalam' => 'Kamar Mandi Dalam',
        'kmandidalam' => 'Kamar Mandi Dalam',
        'kmdalam' => 'Kamar Mandi Dalam',
        'kamarmandiluar' => 'Kamar Mandi Luar',
        'kmandiluar' => 'Kamar Mandi Luar',
        'kmluar' => 'Kamar Mandi Luar',
        'shower' => 'Shower',
        'ember' => 'Ember',
        'embermandi' => 'Ember',
        'airpanas' => 'Air Panas',
        'dapur' => 'Dapur',
        'dapurbersama' => 'Dapur Bersama',
        'ruangtamu' => 'Ruang Tamu',
        'rtamu' => 'Ruang Tamu',
        'penjagakos' => 'Penjaga Kos',
        'tempatjemuran' => 'Tempat Jemuran',
        'rjemur' => 'Tempat Jemuran',
        'laundry' => 'Laundry',
        'dilarangmerokok' => 'Dilarang Merokok',
        'dilarangmerokokdikamar' => 'Dilarang Merokok',
        'parkir' => 'Parkir',
        'parkirmobil' => 'Parkir Mobil',
        'parkirmotor' => 'Parkir Motor',
        'parkirmotorsepeda' => 'Parkir Motor & Sepeda',
        'parkirmotordansepeda' => 'Parkir Motor & Sepeda',
        'lawanjenis' => 'Lawan Jenis',
        'lawanjenisdilarangkekamar' => 'Lawan Jenis',
        'dilarangtamuwalawanjenis' => 'Lawan Jenis',
        'akses' => 'Akses',
        'tamu' => 'Tamu',
        'tamubolehmenginap' => 'Tamu',
        'tamumenginapdikenakanbiaya' => 'Tamu',
        'termasuklistrik' => 'Termasuk Listrik',
        'tidaktermasuklistrik' => 'Tidak Termasuk Listrik',
        'tidaklistrik' => 'Tidak Termasuk Listrik',
        'listrik' => 'Tidak Termasuk Listrik',
    ];

    /**
     * Normalisasi satu label.
     */
    public static function normalize(string $label): string
    {
        $label = trim($label);
        if ($label === '') {
            return $label;
        }

        $key = self::normKey($label);

        // Cari di alias berdasarkan key ternormalisasi.
        if (isset(self::ALIAS[$key])) {
            return self::ALIAS[$key];
        }

        // Fallback: cocokkan key terhadap label kanonik yang sudah dinormalisasi.
        foreach (self::CANONICAL as $canon) {
            if (self::normKey($canon) === $key) {
                return $canon;
            }
        }

        return $label;
    }

    /**
     * Normalisasi daftar label (import CSV, hasil form, dsb.).
     */
    public static function normalizeList(array $labels): array
    {
        $seen = [];
        $out = [];
        foreach ($labels as $label) {
            $canon = self::normalize(trim($label));
            if ($canon === '' || isset($seen[self::normKey($canon)])) {
                continue;
            }
            $seen[self::normKey($canon)] = true;
            $out[] = $canon;
        }

        return $out;
    }

    /**
     * Parsing string fasilitas (dipisah koma) menjadi array.
     */
    public static function fromString(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    /**
     * Normalisasi string fasilitas (dua arah normalisasi + urut).
     */
    public static function normalizeString(?string $value): ?string
    {
        $labels = self::normalizeList(self::fromString($value));

        return $labels ? implode(', ', $labels) : null;
    }

    /**
     * Kunci normalisasi: huruf kecil + buang non-alfanumerik.
     */
    private static function normKey(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', $value) ?? '');
    }
}
