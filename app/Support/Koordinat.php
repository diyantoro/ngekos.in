<?php

namespace App\Support;

class Koordinat
{
    /**
     * Pusat kota untuk fallback ketika properti belum punya koordinat,
     * sekaligus acuan peta mengikuti ketikan kota di form kos.
     * Kunci harus sama ejaannya dengan \App\Support\DaftarKota::DAFTAR.
     */
    public static array $kota = [
        // Jabodetabek
        'Jakarta' => [-6.2088, 106.8456],
        'Jakarta Pusat' => [-6.1865, 106.8289],
        'Jakarta Utara' => [-6.1382, 106.8649],
        'Jakarta Barat' => [-6.1683, 106.7608],
        'Jakarta Selatan' => [-6.2615, 106.8106],
        'Jakarta Timur' => [-6.2251, 106.9004],
        'Bogor' => [-6.5971, 106.806],
        'Depok' => [-6.4025, 106.7942],
        'Bekasi' => [-6.2383, 106.9756],
        'Tangerang' => [-6.1751, 106.6302],
        'Tangerang Selatan' => [-6.2887, 106.7179],
        'Cilegon' => [-6.0170, 106.0540],
        'Serang' => [-6.1160, 106.1530],
        // Jawa Barat
        'Bandung' => [-6.9175, 107.6191],
        'Bandung Barat' => [-6.8446, 107.5110],
        'Cimahi' => [-6.8848, 107.5413],
        'Tasikmalaya' => [-7.3506, 108.2176],
        'Banjar' => [-7.3743, 108.5339],
        'Cirebon' => [-6.7320, 108.5520],
        'Sukabumi' => [-6.9272, 106.9280],
        'Cianjur' => [-6.8210, 107.1420],
        'Garut' => [-7.2146, 107.8990],
        'Kuningan' => [-6.9750, 108.4830],
        'Majalengka' => [-6.8360, 108.2270],
        'Sumedang' => [-6.8570, 107.9230],
        'Indramayu' => [-6.3270, 108.3250],
        'Subang' => [-6.5710, 107.7630],
        'Purwakarta' => [-6.5560, 107.4430],
        'Karawang' => [-6.3220, 107.2940],
        'Ciamis' => [-7.3290, 108.3530],
        'Pangandaran' => [-7.6840, 108.6540],
        'Lebak' => [-6.3560, 106.2490],
        'Pandeglang' => [-6.3090, 106.1060],
        'Jatinangor' => [-6.9330, 107.7730],
        // Jawa Tengah
        'Semarang' => [-6.9667, 110.4167],
        'Salatiga' => [-7.3300, 110.5080],
        'Pekalongan' => [-6.8880, 109.6750],
        'Tegal' => [-6.8690, 109.1400],
        'Surakarta' => [-7.5755, 110.8263],
        'Solo' => [-7.5755, 110.8263],
        'Magelang' => [-7.4700, 110.2170],
        'Purwokerto' => [-7.4240, 109.2340],
        'Banyumas' => [-7.5160, 109.2920],
        'Cilacap' => [-7.7200, 109.0150],
        'Kebumen' => [-7.6680, 109.6600],
        'Purworejo' => [-7.7120, 110.0140],
        'Wonosobo' => [-7.3630, 109.9030],
        'Boyolali' => [-7.5310, 110.6010],
        'Klaten' => [-7.7010, 110.6060],
        'Sukoharjo' => [-7.6810, 110.8300],
        'Wonogiri' => [-7.8140, 110.9260],
        'Karanganyar' => [-7.6170, 110.9480],
        'Sragen' => [-7.4270, 111.0220],
        'Grobogan' => [-7.0860, 110.9170],
        'Blora' => [-6.9710, 111.4190],
        'Rembang' => [-6.7190, 111.3420],
        'Pati' => [-6.7550, 111.0390],
        'Kudus' => [-6.8040, 110.8420],
        'Jepara' => [-6.5900, 110.6690],
        'Demak' => [-6.8940, 110.6390],
        'Kendal' => [-6.9200, 110.2020],
        'Batang' => [-6.9050, 109.7310],
        'Temanggung' => [-7.3140, 110.1700],
        // DIY
        'Yogyakarta' => [-7.7956, 110.3695],
        'Sleman' => [-7.6933, 110.359],
        'Bantul' => [-7.8881, 110.3284],
        'Kulon Progo' => [-7.8600, 110.1590],
        'Gunungkidul' => [-7.9610, 110.6020],
        'Janti' => [-7.7956, 110.3695],
        'Palagan' => [-7.741, 110.368],
        // Jawa Timur
        'Surabaya' => [-7.2575, 112.7521],
        'Malang' => [-7.9797, 112.6304],
        'Batu' => [-7.8710, 112.5260],
        'Madiun' => [-7.6290, 111.5230],
        'Mojokerto' => [-7.4720, 112.4330],
        'Pasuruan' => [-7.6450, 112.9030],
        'Probolinggo' => [-7.7760, 113.2130],
        'Blitar' => [-8.0950, 112.1610],
        'Kediri' => [-7.8160, 112.0110],
        'Jember' => [-8.1680, 113.7020],
        'Banyuwangi' => [-8.2190, 114.3690],
        'Bondowoso' => [-7.9130, 113.8210],
        'Situbondo' => [-7.7060, 114.0100],
        'Lumajang' => [-8.1330, 113.2240],
        'Sidoarjo' => [-7.4470, 112.7180],
        'Gresik' => [-7.1550, 112.6490],
        'Lamongan' => [-7.1160, 112.4170],
        'Tuban' => [-6.8970, 112.0640],
        'Bojonegoro' => [-7.1510, 111.8810],
        'Ngawi' => [-7.4039, 111.4483],
        'Magetan' => [-7.6590, 111.3390],
        'Ponorogo' => [-7.8680, 111.4630],
        'Pacitan' => [-8.2060, 111.1020],
        'Trenggalek' => [-8.0530, 111.7080],
        'Tulungagung' => [-8.0650, 111.9020],
        'Nganjuk' => [-7.6020, 111.8960],
        'Jombang' => [-7.5460, 112.2260],
        'Bangkalan' => [-7.0290, 112.9330],
        'Sampang' => [-7.1830, 113.2490],
        'Pamekasan' => [-7.1580, 113.4740],
        'Sumenep' => [-7.0150, 113.8600],
        // Bali
        'Denpasar' => [-8.6705, 115.2126],
        'Badung' => [-8.6390, 115.2180],
        'Gianyar' => [-8.5410, 115.3230],
        'Buleleng' => [-8.1150, 115.0880],
        'Tabanan' => [-8.5410, 115.1210],
        'Klungkung' => [-8.5350, 115.3970],
        'Bangli' => [-8.4540, 115.3540],
        'Karangasem' => [-8.3440, 115.6110],
        'Jembrana' => [-8.3570, 114.6390],
        // NTB
        'Mataram' => [-8.5830, 116.1170],
        'Bima' => [-8.4660, 118.7270],
        'Lombok Barat' => [-8.7230, 115.9440],
        'Lombok Tengah' => [-8.7080, 116.2860],
        'Lombok Timur' => [-8.6510, 116.5310],
        'Lombok Utara' => [-8.3490, 116.1480],
        'Sumbawa' => [-8.5050, 117.4270],
        'Dompu' => [-8.5430, 118.4610],
        // NTT
        'Kupang' => [-10.1770, 123.6070],
        'Ende' => [-8.8450, 121.6620],
        'Maumere' => [-8.6220, 122.2110],
        'Labuan Bajo' => [-8.4960, 119.8870],
        'Manggarai Barat' => [-8.4960, 119.8870],
        // Sumatera
        'Banda Aceh' => [5.5480, 95.3230],
        'Sabang' => [5.8930, 95.3210],
        'Lhokseumawe' => [5.1810, 97.1410],
        'Langsa' => [4.4680, 97.9680],
        'Subulussalam' => [2.6420, 97.9320],
        'Medan' => [3.5952, 98.6722],
        'Binjai' => [3.6000, 98.4850],
        'Tebing Tinggi' => [3.3290, 99.1620],
        'Pematangsiantar' => [2.9590, 99.0680],
        'Tanjungbalai' => [2.9670, 99.7970],
        'Sibolga' => [1.7420, 98.7840],
        'Padang Sidempuan' => [1.3820, 99.2710],
        'Gunungsitoli' => [1.2890, 97.6190],
        'Deli Serdang' => [3.5630, 98.8760],
        'Padang' => [0.9470, 100.4170],
        'Bukittinggi' => [0.3030, 100.3670],
        'Padang Panjang' => [0.4690, 100.3780],
        'Pariaman' => [0.6250, 100.1180],
        'Sawahlunto' => [0.6820, 100.7760],
        'Payakumbuh' => [0.2210, 100.6320],
        'Solok' => [0.8010, 100.6600],
        'Pekanbaru' => [0.5070, 101.4470],
        'Dumai' => [1.6670, 101.4470],
        'Jambi' => [1.6100, 103.6130],
        'Sungai Penuh' => [2.0650, 101.3950],
        'Palembang' => [-2.9909, 104.7566],
        'Prabumulih' => [3.4290, 104.2370],
        'Pagar Alam' => [4.0210, 103.2430],
        'Lubuklinggau' => [3.2930, 102.8610],
        'Ogan Ilir' => [-3.2117, 104.65],
        'Indralaya' => [-3.2117, 104.65],
        'Bengkulu' => [3.7920, 102.2600],
        'Bandar Lampung' => [5.3970, 105.2660],
        'Metro' => [5.1130, 105.3060],
        'Pangkal Pinang' => [2.1310, 106.1170],
        'Tanjung Pinang' => [0.9180, 104.4480],
        'Batam' => [1.1300, 104.0530],
        // Kalimantan
        'Pontianak' => [0.0260, 109.3420],
        'Singkawang' => [0.9060, 108.9800],
        'Palangka Raya' => [2.2120, 113.9160],
        'Banjarmasin' => [3.3190, 114.5900],
        'Banjarbaru' => [3.4420, 114.8310],
        'Samarinda' => [0.4940, 117.1430],
        'Balikpapan' => [1.2680, 116.8310],
        'Bontang' => [0.1330, 117.4970],
        'Tarakan' => [3.2980, 117.5910],
        'Tanjung Selor' => [2.8440, 117.3660],
        // Sulawesi
        'Manado' => [1.4750, 124.8420],
        'Bitung' => [1.4400, 125.1900],
        'Tomohon' => [1.3270, 124.8370],
        'Kotamobagu' => [0.7380, 124.3140],
        'Palu' => [0.9000, 119.8700],
        'Makassar' => [-5.1477, 119.4327],
        'Parepare' => [4.0130, 119.6250],
        'Palopo' => [2.9970, 120.2050],
        'Kendari' => [3.9770, 122.5150],
        'Bau-Bau' => [5.4770, 122.6020],
        'Gorontalo' => [0.5410, 123.0670],
        'Mamuju' => [2.6780, 118.8930],
        'Maros' => [5.0020, 119.5760],
        'Gowa' => [5.2030, 119.4490],
        // Maluku & Papua
        'Ambon' => [3.6950, 128.1810],
        'Tual' => [5.6450, 132.7500],
        'Ternate' => [0.7890, 127.3840],
        'Tidore Kepulauan' => [0.6670, 127.4240],
        'Jayapura' => [2.5330, 140.7180],
        'Sorong' => [0.8810, 131.2450],
        'Manokwari' => [0.8660, 134.0830],
        'Merauke' => [-8.5000, 140.3330],
        'Biak' => [1.1770, 136.0830],
        'Timika' => [4.5460, 136.8830],
        'Wamena' => [4.0980, 138.9510],
        'Fakfak' => [2.9170, 132.2960],
        'Sorong Selatan' => [1.1160, 131.9180],
    ];

    protected static ?array $petaKota = null;

    public static function kota(string $kota): ?array
    {
        if (self::$petaKota === null) {
            self::$petaKota = [];
            foreach (self::$kota as $nama => $titik) {
                self::$petaKota[NormalisasiKota::normalize($nama)] = $titik;
            }
        }

        $n = NormalisasiKota::normalize($kota);

        if (isset(self::$petaKota[$n])) {
            return self::$petaKota[$n];
        }

        $cocok = null;
        foreach (self::$petaKota as $nama => $titik) {
            if (str_starts_with($n, $nama.' ')) {
                if ($cocok === null || strlen($nama) > strlen($cocok)) {
                    $cocok = $nama;
                }
            }
        }

        return $cocok !== null ? self::$petaKota[$cocok] : null;
    }

    /**
     * Koordinat efektif sebuah properti: pakai latitude/longitude asli jika ada,
     * jika tidak, fallback ke pusat kota yang dikenali.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function titik(?string $kota, mixed $latitude, mixed $longitude): ?array
    {
        if (is_numeric($latitude) && is_numeric($longitude)) {
            $lat = (float) $latitude;
            $lng = (float) $longitude;

            if ($lat != 0.0 || $lng != 0.0) {
                return [$lat, $lng];
            }
        }

        return $kota !== null && $kota !== '' ? self::kota($kota) : null;
    }
}