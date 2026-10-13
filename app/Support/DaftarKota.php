<?php

namespace App\Support;

/**
 * Daftar baku kota/kabupaten Indonesia untuk autocomplete + validasi ejaan.
 *
 * Prinsip:
 * - Nama disimpan TANPA awalan "Kota/Kabupaten" (mis. "Bandung", "Sleman", "Bogor").
 * - Pencocokan case-insensitive + awalan "Kota/Kab." diabaikan ("Kota Bandung" -> "Bandung").
 * - Daftar ini kurasi kota/kabupaten populer + ibu kota provinsi. Kota lain tetap
 *   bisa disimpan (tidak diblokir), tapi disarankan memilih dari saran agar ejaan baku.
 */
class DaftarKota
{
    /** @var array<int,string> */
    public const DAFTAR = [
        // DKI Jakarta & sekitar (Jabodetabek)
        'Jakarta',
        'Jakarta Pusat',
        'Jakarta Utara',
        'Jakarta Barat',
        'Jakarta Selatan',
        'Jakarta Timur',
        'Bogor',
        'Depok',
        'Bekasi',
        'Tangerang',
        'Tangerang Selatan',
        'Cilegon',
        'Serang',
        // Jawa Barat
        'Bandung',
        'Bandung Barat',
        'Cimahi',
        'Tasikmalaya',
        'Banjar',
        'Cirebon',
        'Sukabumi',
        'Cianjur',
        'Garut',
        'Kuningan',
        'Majalengka',
        'Sumedang',
        'Indramayu',
        'Subang',
        'Purwakarta',
        'Karawang',
        'Ciamis',
        'Pangandaran',
        // Jawa Tengah
        'Semarang',
        'Salatiga',
        'Pekalongan',
        'Tegal',
        'Surakarta',
        'Solo',
        'Magelang',
        'Purwokerto',
        'Banyumas',
        'Cilacap',
        'Kebumen',
        'Purworejo',
        'Wonosobo',
        'Boyolali',
        'Klaten',
        'Sukoharjo',
        'Wonogiri',
        'Karanganyar',
        'Sragen',
        'Grobogan',
        'Blora',
        'Rembang',
        'Pati',
        'Kudus',
        'Jepara',
        'Demak',
        'Kendal',
        'Batang',
        'Temanggung',
        // DIY
        'Yogyakarta',
        'Sleman',
        'Bantul',
        'Kulon Progo',
        'Gunungkidul',
        // Jawa Timur
        'Surabaya',
        'Malang',
        'Batu',
        'Madiun',
        'Mojokerto',
        'Pasuruan',
        'Probolinggo',
        'Blitar',
        'Kediri',
        'Jember',
        'Banyuwangi',
        'Bondowoso',
        'Situbondo',
        'Lumajang',
        'Sidoarjo',
        'Gresik',
        'Lamongan',
        'Tuban',
        'Bojonegoro',
        'Ngawi',
        'Magetan',
        'Ponorogo',
        'Pacitan',
        'Trenggalek',
        'Tulungagung',
        'Nganjuk',
        'Jombang',
        'Bangkalan',
        'Sampang',
        'Pamekasan',
        'Sumenep',
        // Banten
        'Lebak',
        'Pandeglang',
        // Bali
        'Denpasar',
        'Badung',
        'Gianyar',
        'Buleleng',
        'Tabanan',
        'Klungkung',
        'Bangli',
        'Karangasem',
        'Jembrana',
        // NTB
        'Mataram',
        'Bima',
        'Lombok Barat',
        'Lombok Tengah',
        'Lombok Timur',
        'Lombok Utara',
        'Sumbawa',
        'Dompu',
        // NTT
        'Kupang',
        'Ende',
        'Maumere',
        'Labuan Bajo',
        'Manggarai Barat',
        // Sumatera
        'Banda Aceh',
        'Sabang',
        'Lhokseumawe',
        'Langsa',
        'Subulussalam',
        'Medan',
        'Binjai',
        'Tebing Tinggi',
        'Pematangsiantar',
        'Tanjungbalai',
        'Sibolga',
        'Padang Sidempuan',
        'Gunungsitoli',
        'Padang',
        'Bukittinggi',
        'Padang Panjang',
        'Pariaman',
        'Sawahlunto',
        'Payakumbuh',
        'Solok',
        'Pekanbaru',
        'Dumai',
        'Jambi',
        'Sungai Penuh',
        'Palembang',
        'Prabumulih',
        'Pagar Alam',
        'Lubuklinggau',
        'Bengkulu',
        'Bandar Lampung',
        'Metro',
        'Pangkal Pinang',
        'Tanjung Pinang',
        'Batam',
        'Deli Serdang',
        'Ogan Ilir',
        'Indralaya',
        'Jatinangor',
        // Kalimantan
        'Pontianak',
        'Singkawang',
        'Palangka Raya',
        'Banjarmasin',
        'Banjarbaru',
        'Samarinda',
        'Balikpapan',
        'Bontang',
        'Tarakan',
        'Tanjung Selor',
        // Sulawesi
        'Manado',
        'Bitung',
        'Tomohon',
        'Kotamobagu',
        'Palu',
        'Makassar',
        'Parepare',
        'Palopo',
        'Kendari',
        'Bau-Bau',
        'Gorontalo',
        'Mamuju',
        'Maros',
        'Gowa',
        // Maluku & Papua
        'Ambon',
        'Tual',
        'Ternate',
        'Tidore Kepulauan',
        'Jayapura',
        'Sorong',
        'Manokwari',
        'Merauke',
        'Biak',
        'Timika',
        'Wamena',
        'Fakfak',
        'Sorong Selatan',
    ];

    /**
     * Semua nama kota terurut + unik.
     *
     * @return array<int,string>
     */
    public static function semua(): array
    {
        return collect(self::DAFTAR)
            ->map(fn ($k) => trim((string) $k))
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * Saran kota untuk query ketikan (filter contains, maks $batas).
     *
     * @return array<int,string>
     */
    public static function saran(string $query, int $batas = 12): array
    {
        $query = mb_strtolower(trim($query));

        if ($query === '') {
            return array_slice(self::semua(), 0, $batas);
        }

        return collect(self::semua())
            ->filter(fn ($k) => str_contains(mb_strtolower($k), $query))
            ->take($batas)
            ->values()
            ->all();
    }

    /**
     * Samakan ejaan input dengan daftar baku (case-insensitive,
     * awalan Kota/Kabupaten diabaikan). Kembalikan null bila kosong.
     * Bila tidak cocok, kembalikan rapihan spasi + tanpa awalan
     * (tetap disimpan, tidak diblokir).
     */
    public static function kanonik(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $rapi = trim((string) preg_replace('/\s+/', ' ', $input));

        if ($rapi === '') {
            return null;
        }

        foreach (self::semua() as $nama) {
            if (mb_strtolower($nama) === mb_strtolower($rapi)) {
                return $nama;
            }
        }

        $tanpaAwalan = trim((string) preg_replace('/^(kota|kotamadya|kabupaten|kab\.?)\s+/i', '', $rapi));

        if ($tanpaAwalan !== '' && $tanpaAwalan !== $rapi) {
            foreach (self::semua() as $nama) {
                if (mb_strtolower($nama) === mb_strtolower($tanpaAwalan)) {
                    return $nama;
                }
            }

            return $tanpaAwalan;
        }

        return $rapi;
    }
}
