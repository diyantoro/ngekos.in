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
        'Kloset Jongkok',
        'Kamar Mandi Dalam',
        'Kamar Mandi Luar',
        'Shower',
        'Ember',
        'Air',
        'WiFi',
        'Dapur Bersama',
        'Ruang Tamu',
        'Penjaga Kos',
        'Tempat Jemuran',
        'Laundry',
        'Dilarang Merokok',
        'Dilarang Bawa Tamu lawan jenis',
        'Jam Malam 22.00',
        'Dilarang Bawa Hewan',
        'Dilarang Miras & Narkoba',
        'Parkir Motor',
        'Parkir Mobil',
        'Lawan Jenis',
        'Akses 24 Jam',
        'Tamu',
        'Termasuk Listrik',
        'Tidak Termasuk Listrik',
    ];

    /**
     * Fasilitas level KOS (umum/bersama, dipakai di form kos).
     * Prinsip: hanya yang dipakai bersama seluruh penghuni.
     */
    public const FASILITAS_KOS = [
        'WiFi',
        'Dapur Bersama',
        'Ruang Tamu',
        'Penjaga Kos',
        'Tempat Jemuran',
        'Laundry',
        'Parkir Motor',
        'Parkir Mobil',
    ];

    /**
     * Fasilitas level KAMAR (privat, dipakai di form kamar).
     * Prinsip: hanya yang ada di dalam / milik kamar tersebut,
     * termasuk kamar mandi dan status listrik kamar.
     */
    public const FASILITAS_KAMAR = [
        'AC',
        'Kipas Angin',
        'Kulkas',
        'Kasur',
        'Lemari',
        'Meja',
        'Kursi',
        'Meja & Kursi',
        'Rak Baju',
        'Jendela',
        'Bantal',
        'Cermin',
        'Ventilasi',
        'Kamar Mandi Dalam',
        'Kamar Mandi Luar',
        'Kloset Duduk',
        'Kloset Jongkok',
        'Shower',
        'Ember',
        'Air',
        'Termasuk Listrik',
        'Tidak Termasuk Listrik',
    ];

    /**
     * Peraturan level KOS (berlaku untuk seluruh kos, dipakai di form kos).
     * Disimpan di kolom fasilitas seperti sebelumnya agar data lama tetap jalan,
     * berdampingan dengan kolom Aturan Kos bebas (untuk aturan kustom per baris).
     */
    public const PERATURAN_KOS = [
        'Dilarang Merokok',
        'Dilarang Bawa Tamu lawan jenis',
        'Akses 24 Jam',
        'Jam Malam 22.00',
        'Dilarang Bawa Hewan',
        'Dilarang Miras & Narkoba',
    ];

    /**
     * Pasangan fasilitas kamar yang saling bertentangan (tidak boleh
     * dipilih bersamaan). Dipakai di form kamar (web + API).
     */
    public const EKSKLUSIF = [
        ['AC', 'Kipas Angin'],
        ['Termasuk Listrik', 'Tidak Termasuk Listrik'],
        ['Kamar Mandi Dalam', 'Kamar Mandi Luar'],
        ['Kloset Duduk', 'Kloset Jongkok'],
    ];

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
        'klosetjongkok' => 'Kloset Jongkok',
        'closetduduk' => 'Kloset Duduk',
        'closetjongkok' => 'Kloset Jongkok',
        'klosetdudukjongkok' => 'Kloset Duduk',
        'kamarmandidalam' => 'Kamar Mandi Dalam',
        'kmandidalam' => 'Kamar Mandi Dalam',
        'kmdalam' => 'Kamar Mandi Dalam',
        'kamarmandiluar' => 'Kamar Mandi Luar',
        'kmandiluar' => 'Kamar Mandi Luar',
        'kmluar' => 'Kamar Mandi Luar',
        'shower' => 'Shower',
        'showerairdingin' => 'Shower',
        'showerdingin' => 'Shower',
        'pancuran' => 'Shower',
        'pancuranairdingin' => 'Shower',
        'pancurandingin' => 'Shower',
        'ember' => 'Ember',
        'embermandi' => 'Ember',
        'air' => 'Air',
        'airbersih' => 'Air',
        'airpanas' => 'Air',
        'showerairpanas' => 'Air',
        'showerpanas' => 'Air',
        'waterheater' => 'Air',
        'pemanasair' => 'Air',
        // Dapur di kos selalu berarti dapur bersama (dipakai bareng),
        // jadi varian lama 'Dapur' / 'Dapur Umum' digabung ke satu label.
        'dapur' => 'Dapur Bersama',
        'dapurbersama' => 'Dapur Bersama',
        'dapurumum' => 'Dapur Bersama',
        'ruangtamu' => 'Ruang Tamu',
        'rtamu' => 'Ruang Tamu',
        'penjagakos' => 'Penjaga Kos',
        'tempatjemuran' => 'Tempat Jemuran',
        'rjemur' => 'Tempat Jemuran',
        'laundry' => 'Laundry',
        'dilarangmerokok' => 'Dilarang Merokok',
        'dilarangmerokokdikamar' => 'Dilarang Merokok',
        'jammalam2200' => 'Jam Malam 22.00',
        'jammalam' => 'Jam Malam 22.00',
        'jammalam10malam' => 'Jam Malam 22.00',
        'dilarangbawahewan' => 'Dilarang Bawa Hewan',
        'dilaranghewan' => 'Dilarang Bawa Hewan',
        'dilarangmemeliharahewan' => 'Dilarang Bawa Hewan',
        'dilarangmirasnarkoba' => 'Dilarang Miras & Narkoba',
        'dilarangbawamirasnarkoba' => 'Dilarang Miras & Narkoba',
        'dilarangmiras' => 'Dilarang Miras & Narkoba',
        'dilarangnarkoba' => 'Dilarang Miras & Narkoba',
        // Parkir generik dipecah agar logis: motor (sepeda ikut area motor)
        // dan mobil dipilih terpisah. Data lama 'Parkir' dianggap parkir
        // motor (paling umum di kos); pemilik bisa centang Mobil bila ada.
        // Label lama 'Parkir Motor & Sepeda' disingkat jadi 'Parkir Motor'.
        'parkir' => 'Parkir Motor',
        'parkiran' => 'Parkir Motor',
        'parkirumum' => 'Parkir Motor',
        'parkirmobil' => 'Parkir Mobil',
        'parkirmotor' => 'Parkir Motor',
        'parkirmotorsepeda' => 'Parkir Motor',
        'parkirmotordansepeda' => 'Parkir Motor',
        'parkirsepeda' => 'Parkir Motor',
        'lawanjenis' => 'Lawan Jenis',
        'lawanjenisdilarangkekamar' => 'Lawan Jenis',
        'dilarangtamuwalawanjenis' => 'Lawan Jenis',
        'akses' => 'Akses 24 Jam',
        'akses24jam' => 'Akses 24 Jam',
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

    /**
     * Cari pasangan bertentangan yang terpilih bersamaan.
     * Return null bila aman, atau pesan galat bila ada konflik.
     */
    public static function konflikEksklusif(array $labels): ?string
    {
        $dipilih = self::normalizeList($labels);

        foreach (self::EKSKLUSIF as $grup) {
            $kena = array_values(array_intersect($grup, $dipilih));
            if (count($kena) > 1) {
                return 'Pilihan bertentangan: "'.implode('" dan "', $kena).'" tidak boleh dipilih bersamaan. Pilih salah satu saja.';
            }
        }

        return null;
    }

    /**
     * Bersihkan konflik dengan mempertahankan pilihan terakhir.
     * Dipakai di Livewire updated hook agar ceklis lawan otomatis lepas.
     */
    public static function tanpaKonflik(array $labels, ?string $terakhir = null): array
    {
        $dipilih = self::normalizeList($labels);

        if ($terakhir === null || $terakhir === '') {
            // Tanpa info terakhir: lepas item kedua dst. dari tiap grup.
            foreach (self::EKSKLUSIF as $grup) {
                $pertama = null;
                foreach ($dipilih as $i => $label) {
                    if (in_array($label, $grup, true)) {
                        if ($pertama === null) {
                            $pertama = $label;
                        } else {
                            unset($dipilih[$i]);
                        }
                    }
                }
            }

            return array_values($dipilih);
        }

        $terakhir = self::normalize($terakhir);

        foreach (self::EKSKLUSIF as $grup) {
            if (! in_array($terakhir, $grup, true)) {
                continue;
            }
            // Pertahankan pilihan terakhir, lepas lawannya.
            $dipilih = array_values(array_filter(
                $dipilih,
                fn ($label) => $label === $terakhir || ! in_array($label, $grup, true)
            ));
        }

        return $dipilih;
    }

    /**
     * Lawan eksklusif dari satu label (untuk frontend).
     *
     * @return array<int,string>
     */
    public static function lawanEksklusif(string $label): array
    {
        $label = self::normalize($label);

        foreach (self::EKSKLUSIF as $grup) {
            if (in_array($label, $grup, true)) {
                return array_values(array_filter($grup, fn ($item) => $item !== $label));
            }
        }

        return [];
    }

    /**
     * Daftar fasilitas untuk satu scope ('kos'|'kamar'|'semua').
     * Scope kos sudah termasuk peraturan kos (berlaku untuk seluruh kos).
     *
     * @return array<int,string>
     */
    public static function untukScope(string $scope): array
    {
        return match ($scope) {
            'kos' => array_values(array_unique(array_merge(self::FASILITAS_KOS, self::PERATURAN_KOS))),
            'kamar' => self::FASILITAS_KAMAR,
            default => array_values(array_unique(array_merge(self::FASILITAS_KOS, self::PERATURAN_KOS, self::FASILITAS_KAMAR))),
        };
    }

    /**
     * Pisahkan string fasilitas campuran menjadi [kos, kamar].
     * Item tak dikenal ikut kedua sisi agar tidak hilang (kompatibel data lama).
     *
     * @return array{kos: array<int,string>, kamar: array<int,string>}
     */
    public static function pecahKosKamar(?string $value): array
    {
        $labels = self::normalizeList(self::fromString($value));
        $kos = [];
        $kamar = [];

        foreach ($labels as $label) {
            $diKos = in_array($label, self::FASILITAS_KOS, true) || in_array($label, self::PERATURAN_KOS, true);
            $diKamar = in_array($label, self::FASILITAS_KAMAR, true);

            if ($diKos && ! $diKamar) {
                $kos[] = $label;
            } elseif ($diKamar && ! $diKos) {
                $kamar[] = $label;
            } else {
                // Tak dikenal: tampilkan di kedua sisi agar tidak hilang.
                $kos[] = $label;
                $kamar[] = $label;
            }
        }

        return ['kos' => $kos, 'kamar' => $kamar];
    }
}
