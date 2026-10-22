<?php

namespace App\Support;

/**
 * Pemetaan kampus populer -> kota & area sekitar.
 *
 * Chatbot memakai daftar ini untuk memahami pertanyaan seperti
 * "ada kos dekat kampus UNSRI?" lalu mencocokkannya dengan alamat kos
 * yang tersimpan di database (mis. "Bukit Besar, Ilir Barat I").
 *
 * Catatan: ini bukan data jarak/koordinat kampus. Pemetaan hanya
 * menghubungkan nama kampus dengan kota + nama area yang umum dipakai
 * di alamat kos. Bila tidak ada kos yang menyebut area tersebut,
 * chatbot akan jujur menampilkan kos di kota yang sama.
 */
class DaftarKampus
{
    /**
     * @var array<int, array{
     *     nama: string,
     *     alias: array<int, string>,
     *     lokasi: array<int, array{kota: string, area: array<int, string>}>
     * }>
     */
    public const DAFTAR = [
        [
            'nama' => 'Universitas Sriwijaya (UNSRI)',
            'alias' => ['unsri', 'universitas sriwijaya', 'univ sriwijaya'],
            'lokasi' => [
                ['kota' => 'Palembang', 'area' => ['bukit besar', 'demang lebar daun']],
                ['kota' => 'Ogan Ilir', 'area' => ['indralaya']],
            ],
        ],
        [
            'nama' => 'Politeknik Negeri Sriwijaya (POLSRI)',
            'alias' => ['polsri', 'politeknik negeri sriwijaya'],
            'lokasi' => [
                ['kota' => 'Palembang', 'area' => ['srijaya negara', 'bukit besar']],
            ],
        ],
        [
            'nama' => 'Universitas Muhammadiyah Palembang (UMP)',
            'alias' => ['ump', 'universitas muhammadiyah palembang'],
            'lokasi' => [
                ['kota' => 'Palembang', 'area' => ['13 ulu', 'ahmad dahlan', 'seberang ulu']],
            ],
        ],
        [
            'nama' => 'Universitas Bina Darma',
            'alias' => ['binadarma', 'bina darma', 'universitas bina darma'],
            'lokasi' => [
                ['kota' => 'Palembang', 'area' => ['ahmad yani', 'plaju']],
            ],
        ],
        [
            'nama' => 'Institut Teknologi Bandung (ITB)',
            'alias' => ['itb', 'institut teknologi bandung'],
            'lokasi' => [
                ['kota' => 'Bandung', 'area' => ['ganesha', 'dago', 'cisitu', 'lebak siliwangi', 'tamansari']],
            ],
        ],
        [
            'nama' => 'Universitas Padjadjaran (UNPAD)',
            'alias' => ['unpad', 'universitas padjadjaran', 'padjadjaran'],
            'lokasi' => [
                ['kota' => 'Sumedang', 'area' => ['jatinangor']],
                ['kota' => 'Bandung', 'area' => ['dipatiukur', 'dago']],
            ],
        ],
        [
            'nama' => 'Universitas Pendidikan Indonesia (UPI)',
            'alias' => ['upi', 'universitas pendidikan indonesia'],
            'lokasi' => [
                ['kota' => 'Bandung', 'area' => ['setiabudi', 'isola']],
            ],
        ],
        [
            'nama' => 'Telkom University',
            'alias' => ['telkom university', 'universitas telkom', 'telkom'],
            'lokasi' => [
                ['kota' => 'Bandung', 'area' => ['buahbatu', 'dayeuhkolot', 'sukapura']],
            ],
        ],
        [
            'nama' => 'Universitas Katolik Parahyangan (UNPAR)',
            'alias' => ['unpar', 'parahyangan'],
            'lokasi' => [
                ['kota' => 'Bandung', 'area' => ['ciumbuleuit', 'setiabudi']],
            ],
        ],
        [
            'nama' => 'Universitas Kristen Maranatha',
            'alias' => ['maranatha'],
            'lokasi' => [
                ['kota' => 'Bandung', 'area' => ['surya sumantri', 'ciumbuleuit']],
            ],
        ],
        [
            'nama' => 'Universitas Gadjah Mada (UGM)',
            'alias' => ['ugm', 'universitas gadjah mada', 'gadjah mada'],
            'lokasi' => [
                ['kota' => 'Sleman', 'area' => ['pogung', 'mlati', 'kaliurang', 'bulaksumur']],
                ['kota' => 'Yogyakarta', 'area' => ['bulaksumur']],
            ],
        ],
        [
            'nama' => 'Universitas Islam Indonesia (UII)',
            'alias' => ['uii', 'universitas islam indonesia'],
            'lokasi' => [
                ['kota' => 'Sleman', 'area' => ['kaliurang', 'ngaglik']],
                ['kota' => 'Yogyakarta', 'area' => ['caturtunggal']],
            ],
        ],
        [
            'nama' => 'UIN Sunan Kalijaga',
            'alias' => ['uin sunan kalijaga', 'sunan kalijaga', 'uin suka', 'uin jogja', 'uin yogyakarta'],
            'lokasi' => [
                ['kota' => 'Yogyakarta', 'area' => ['caturtunggal', 'maguwoharjo', 'depok']],
                ['kota' => 'Sleman', 'area' => ['depok', 'maguwoharjo']],
            ],
        ],
        [
            'nama' => 'Universitas Indonesia (UI)',
            'alias' => ['universitas indonesia', 'ui depok', 'ui'],
            'lokasi' => [
                ['kota' => 'Depok', 'area' => ['kukusan', 'beji', 'margonda', 'pancoran mas']],
            ],
        ],
        [
            'nama' => 'Institut Pertanian Bogor (IPB)',
            'alias' => ['ipb', 'institut pertanian bogor'],
            'lokasi' => [
                ['kota' => 'Bogor', 'area' => ['dramaga', 'babakan']],
            ],
        ],
        [
            'nama' => 'Universitas Diponegoro (UNDIP)',
            'alias' => ['undip', 'universitas diponegoro', 'diponegoro'],
            'lokasi' => [
                ['kota' => 'Semarang', 'area' => ['tembalang', 'banyumanik', 'pleburan']],
            ],
        ],
        [
            'nama' => 'Universitas Negeri Semarang (UNNES)',
            'alias' => ['unnes', 'universitas negeri semarang'],
            'lokasi' => [
                ['kota' => 'Semarang', 'area' => ['sekaran', 'gunungpati']],
            ],
        ],
        [
            'nama' => 'Universitas Brawijaya (UB)',
            'alias' => ['ub', 'universitas brawijaya', 'brawijaya'],
            'lokasi' => [
                ['kota' => 'Malang', 'area' => ['lowokwaru', 'veteran', 'sumbersari']],
            ],
        ],
        [
            'nama' => 'Universitas Negeri Malang (UM)',
            'alias' => ['um', 'universitas negeri malang'],
            'lokasi' => [
                ['kota' => 'Malang', 'area' => ['sumbersari', 'lowokwaru']],
            ],
        ],
        [
            'nama' => 'Universitas Muhammadiyah Malang (UMM)',
            'alias' => ['umm', 'universitas muhammadiyah malang'],
            'lokasi' => [
                ['kota' => 'Malang', 'area' => ['dinoyo', 'lowokwaru', 'tegalgondo']],
            ],
        ],
        [
            'nama' => 'Institut Teknologi Sepuluh Nopember (ITS)',
            'alias' => ['its', 'institut teknologi sepuluh nopember', 'sepuluh nopember'],
            'lokasi' => [
                ['kota' => 'Surabaya', 'area' => ['sukolilo', 'keputih', 'mulyorejo']],
            ],
        ],
        [
            'nama' => 'Universitas Airlangga (UNAIR)',
            'alias' => ['unair', 'universitas airlangga', 'airlangga'],
            'lokasi' => [
                ['kota' => 'Surabaya', 'area' => ['airlangga', 'gubeng', 'dharmawangsa']],
            ],
        ],
        [
            'nama' => 'Universitas Negeri Surabaya (UNESA)',
            'alias' => ['unesa', 'universitas negeri surabaya'],
            'lokasi' => [
                ['kota' => 'Surabaya', 'area' => ['ketintang', 'lidah wetan']],
            ],
        ],
        [
            'nama' => 'Universitas Trisakti',
            'alias' => ['trisakti', 'universitas trisakti'],
            'lokasi' => [
                ['kota' => 'Jakarta Barat', 'area' => ['grogol', 'grogol petamburan']],
            ],
        ],
        [
            'nama' => 'Universitas Bina Nusantara (BINUS)',
            'alias' => ['binus', 'bina nusantara', 'universitas bina nusantara'],
            'lokasi' => [
                ['kota' => 'Jakarta Barat', 'area' => ['kemanggisan', 'kebon jeruk']],
                ['kota' => 'Tangerang', 'area' => ['alam sutera']],
            ],
        ],
        [
            'nama' => 'Universitas Multimedia Nusantara (UMN)',
            'alias' => ['umn', 'multimedia nusantara'],
            'lokasi' => [
                ['kota' => 'Tangerang Selatan', 'area' => ['pagedangan', 'bsd', 'scientia']],
            ],
        ],
        [
            'nama' => 'UIN Syarif Hidayatullah Jakarta',
            'alias' => ['uin jakarta', 'uin syarif hidayatullah', 'syarif hidayatullah'],
            'lokasi' => [
                ['kota' => 'Tangerang Selatan', 'area' => ['ciputat', 'sawah baru', 'tegal rotan']],
            ],
        ],
        [
            'nama' => 'Universitas Udayana (UNUD)',
            'alias' => ['unud', 'udayana', 'universitas udayana'],
            'lokasi' => [
                ['kota' => 'Badung', 'area' => ['jimbaran', 'bukit']],
                ['kota' => 'Denpasar', 'area' => ['sudirman', 'sanglah']],
            ],
        ],
        [
            'nama' => 'Universitas Sumatera Utara (USU)',
            'alias' => ['usu', 'universitas sumatera utara'],
            'lokasi' => [
                ['kota' => 'Medan', 'area' => ['padang bulan', 'setia budi', 'polonia']],
            ],
        ],
    ];

    /**
     * Cari kampus yang disebut dalam teks.
     *
     * @return array{nama: string, alias: array<int, string>, lokasi: array<int, array{kota: string, area: array<int, string>}>}|null
     */
    public static function cocok(string $teks): ?array
    {
        $teks = self::normalisasi($teks);

        if ($teks === '') {
            return null;
        }

        // Alias terpanjang lebih dulu supaya "universitas brawijaya"
        // tidak kalah oleh alias pendek "ub".
        $kandidat = [];

        foreach (self::DAFTAR as $kampus) {
            foreach ($kampus['alias'] as $alias) {
                $kandidat[] = ['kampus' => $kampus, 'alias' => self::normalisasi($alias)];
            }
        }

        usort($kandidat, fn ($a, $b) => mb_strlen($b['alias']) <=> mb_strlen($a['alias']));

        foreach ($kandidat as $item) {
            $alias = $item['alias'];

            if ($alias === '') {
                continue;
            }

            if (preg_match('/(?<![\p{L}\p{N}])'.preg_quote($alias, '/').'(?![\p{L}\p{N}])/u', $teks) === 1) {
                return $item['kampus'];
            }
        }

        return null;
    }

    /**
     * Ambil data kampus berdasarkan nama lengkap yang tersimpan di konteks.
     *
     * @return array{nama: string, alias: array<int, string>, lokasi: array<int, array{kota: string, area: array<int, string>}>}|null
     */
    public static function cariNama(?string $nama): ?array
    {
        if ($nama === null || trim($nama) === '') {
            return null;
        }

        foreach (self::DAFTAR as $kampus) {
            if (mb_strtolower($kampus['nama']) === mb_strtolower(trim($nama))) {
                return $kampus;
            }
        }

        return null;
    }

    /**
     * Contoh nama kampus yang dikenali chatbot.
     *
     * @return array<int, string>
     */
    public static function contoh(int $batas = 6): array
    {
        return collect(self::DAFTAR)->pluck('nama')->take($batas)->values()->all();
    }

    private static function normalisasi(string $teks): string
    {
        $teks = mb_strtolower($teks, 'UTF-8');
        $teks = (string) preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $teks);
        $teks = (string) preg_replace('/\s+/', ' ', $teks);

        return trim($teks);
    }
}
