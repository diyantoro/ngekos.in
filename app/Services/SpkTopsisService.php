<?php

namespace App\Services;

use App\Models\Kamar;
use App\Models\Kriteria;
use App\Models\TitikAcuan;
use App\Support\FacilityHelper;
use Illuminate\Support\Collection;

class SpkTopsisService
{
    protected MlModelService $mlService;

    public function __construct(?MlModelService $mlService = null)
    {
        $this->mlService = $mlService ?? app(MlModelService::class);
    }

    /**
     * Hitung jarak Haversine antara dua titik koordinat (dalam Kilometer).
     */
    public static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 3);
    }

    /**
     * Jalankan proses pemeringkatan TOPSIS untuk sekumpulan kamar.
     *
     * @param  Collection<int, Kamar>|array<int, Kamar>  $kamars
     * @param  array<string, float>  $bobotUser  Associative array [ 'C1' => 0.25, ... ]
     * @return array{
     *     hasil: array<int, array{
     *         kamar_id: int,
     *         kamar_nama: string,
     *         properti_id: int|null,
     *         properti_nama: string|null,
     *         kota: string|null,
     *         harga_bulanan: float,
     *         foto: string|null,
     *         skor: float,
     *         peringkat: int,
     *         nilai_kriteria: array<string, float>,
     *         detail_skor: array<string, float>,
     *         alasan: string
     *     }>,
     *     kriteria: array,
     *     bobot: array<string, float>,
     *     sensitivitas: array
     * }
     */
    public function rank(
        mixed $kamars,
        TitikAcuan $titikAcuan,
        array $bobotUser = []
    ): array {
        $kamarsList = collect($kamars)->values();
        if ($kamarsList->isEmpty()) {
            return [
                'hasil' => [],
                'kriteria' => [],
                'bobot' => [],
                'sensitivitas' => [],
            ];
        }

        // 1. Ambil kriteria default & normalkan bobot
        $kriterias = Kriteria::orderBy('kode')->get()->keyBy('kode');
        $bobotFinal = $this->normalisasiBobot($kriterias, $bobotUser);

        // 2. Siapkan data kamar untuk batch prediksi harga JST (C2)
        $batchPayload = [];
        foreach ($kamarsList as $idx => $kamar) {
            $properti = $kamar->properti;
            $latKamar = (float) ($properti?->latitude ?? $titikAcuan->latitude);
            $lonKamar = (float) ($properti?->longitude ?? $titikAcuan->longitude);
            $jarak = self::haversine($latKamar, $lonKamar, (float) $titikAcuan->latitude, (float) $titikAcuan->longitude);

            $batchPayload[] = [
                'kamar_id' => $kamar->id,
                'luas_m2' => $kamar->hitungLuasM2(),
                'jarak_km' => $jarak,
                'kawasan' => $properti?->kota ?? 'Coblong',
                'tipe_penghuni' => $properti?->tipe_hunian?->value ?? 'Campur',
                'fasilitas' => $this->gabungFasilitas($kamar),
                'harga_aktual' => (float) $kamar->harga_sewa_bulanan,
            ];
        }

        $prediksiBatch = collect($this->mlService->predictBatch($batchPayload))->keyBy('kamar_id');

        // 3. Susun Matriks Keputusan X (m kamar x 5 kriteria)
        // C1: Harga bulanan (cost)
        // C2: Kewajaran harga (benefit: semakin murah dari prediksi, semakin besar benefit)
        // C3: Jarak ke titik acuan (cost)
        // C4: Kelengkapan fasilitas (benefit)
        // C5: Luas kamar (benefit)
        $matrix = [];
        $kamarMeta = [];

        foreach ($kamarsList as $i => $kamar) {
            $properti = $kamar->properti;
            $latKamar = (float) ($properti?->latitude ?? $titikAcuan->latitude);
            $lonKamar = (float) ($properti?->longitude ?? $titikAcuan->longitude);
            $jarak = max(0.05, self::haversine($latKamar, $lonKamar, (float) $titikAcuan->latitude, (float) $titikAcuan->longitude));

            $hargaBulanan = max(100000.0, (float) $kamar->harga_sewa_bulanan);
            $pred = $prediksiBatch->get($kamar->id);
            $hargaPrediksi = (float) ($pred['harga_prediksi'] ?? $hargaBulanan);

            // Nilai kewajaran (benefit): rasio (harga_prediksi / harga_bulanan). Nilai > 1 berarti lebih murah dari harga pasar wajar!
            $rasioKewajaran = round($hargaPrediksi / $hargaBulanan, 4);

            $jumlahFasilitas = max(1, count($this->gabungFasilitas($kamar)));
            $luas = max(4.0, $kamar->hitungLuasM2());

            $matrix[$i] = [
                'C1' => $hargaBulanan,
                'C2' => $rasioKewajaran,
                'C3' => $jarak,
                'C4' => (float) $jumlahFasilitas,
                'C5' => $luas,
            ];

            $kamarMeta[$i] = [
                'kamar' => $kamar,
                'jarak_km' => $jarak,
                'harga_prediksi' => $hargaPrediksi,
                'selisih_persen' => $pred['selisih_persen'] ?? 0,
                'label_kewajaran' => $pred['label_kewajaran'] ?? 'wajar',
            ];
        }

        // 4. Hitung TOPSIS untuk bobot aktif
        $hasilRanking = $this->hitungTopsis($matrix, $kamarMeta, $kriterias, $bobotFinal);

        // 5. Uji Sensitivitas (variasi bobot +- 25% pada tiap kriteria)
        $sensitivitas = $this->hitungSensitivitas($matrix, $kamarMeta, $kriterias, $bobotFinal, $hasilRanking);

        return [
            'hasil' => $hasilRanking,
            'kriteria' => $kriterias->toArray(),
            'bobot' => $bobotFinal,
            'sensitivitas' => $sensitivitas,
        ];
    }

    /**
     * Gabungkan fasilitas kamar + fasilitas kos (properti), unik & terstandar.
     *
     * @return array<int, string>
     */
    protected function gabungFasilitas(Kamar $kamar): array
    {
        $dariKamar = $kamar->daftarFasilitas();
        $dariKos = FacilityHelper::normalizeList(
            FacilityHelper::fromString((string) ($kamar->properti?->fasilitas ?? ''))
        );

        return array_values(array_unique(array_merge($dariKamar, $dariKos)));
    }

    /**
     * Inti Algoritma TOPSIS.
     */
    public function hitungTopsis(
        array $matrix,
        array $kamarMeta,
        Collection $kriterias,
        array $bobot
    ): array {
        $m = count($matrix);
        if ($m === 0) {
            return [];
        }

        $kodeList = ['C1', 'C2', 'C3', 'C4', 'C5'];

        // Step 1: Normalisasi Vektor
        $pembagi = [];
        foreach ($kodeList as $kode) {
            $sumSq = 0.0;
            for ($i = 0; $i < $m; $i++) {
                $val = $matrix[$i][$kode] ?? 0.0;
                $sumSq += ($val * $val);
            }
            $pembagi[$kode] = sqrt($sumSq) ?: 1.0;
        }

        // Step 2: Matriks Normalisasi Terbobot (Y)
        $y = [];
        for ($i = 0; $i < $m; $i++) {
            foreach ($kodeList as $kode) {
                $r_ij = $matrix[$i][$kode] / $pembagi[$kode];
                $w_j = $bobot[$kode] ?? 0.2;
                $y[$i][$kode] = $r_ij * $w_j;
            }
        }

        // Step 3: Solusi Ideal Positif (A+) dan Solusi Ideal Negatif (A-)
        $aPos = [];
        $aNeg = [];

        foreach ($kodeList as $kode) {
            $kolom = array_column($y, $kode);
            $tipe = $kriterias->get($kode)?->tipe ?? 'benefit';

            if ($tipe === 'benefit') {
                $aPos[$kode] = max($kolom);
                $aNeg[$kode] = min($kolom);
            } else {
                // cost
                $aPos[$kode] = min($kolom);
                $aNeg[$kode] = max($kolom);
            }
        }

        // Step 4: Jarak Euclidean ke A+ (D+) dan A- (D-)
        // Step 5: Nilai Preferensi V_i
        $hasil = [];
        for ($i = 0; $i < $m; $i++) {
            $dPosSq = 0.0;
            $dNegSq = 0.0;

            foreach ($kodeList as $kode) {
                $diffPos = $y[$i][$kode] - $aPos[$kode];
                $diffNeg = $y[$i][$kode] - $aNeg[$kode];
                $dPosSq += ($diffPos * $diffPos);
                $dNegSq += ($diffNeg * $diffNeg);
            }

            $dPos = sqrt($dPosSq);
            $dNeg = sqrt($dNegSq);

            $penyebut = $dPos + $dNeg;
            $skor = $penyebut > 0 ? ($dNeg / $penyebut) : 0.0;

            // Skor radar (skala 0 - 100 untuk visualisasi chart)
            $radarScore = [];
            foreach ($kodeList as $kode) {
                $tipe = $kriterias->get($kode)?->tipe ?? 'benefit';
                $val = $matrix[$i][$kode];
                $kolomSemua = array_column($matrix, $kode);
                $minK = min($kolomSemua);
                $maxK = max($kolomSemua);
                $rentang = ($maxK - $minK) ?: 1.0;

                if ($tipe === 'benefit') {
                    $radarScore[$kode] = round((($val - $minK) / $rentang) * 100.0, 1);
                } else {
                    // cost: semakin rendah nilai riil, semakin tinggi skor visualnya
                    $radarScore[$kode] = round((($maxK - $val) / $rentang) * 100.0, 1);
                }
            }

            $meta = $kamarMeta[$i];
            $alasan = $this->buatAlasanPeringkat($matrix[$i], $radarScore, $meta);

            $hasil[] = [
                'kamar_id' => $meta['kamar']->id,
                'kamar_nama' => $meta['kamar']->nama,
                'properti_id' => $meta['kamar']->properti?->id,
                'properti_nama' => $meta['kamar']->properti?->nama,
                'kota' => $meta['kamar']->properti?->kota,
                'harga_bulanan' => (float) $matrix[$i]['C1'],
                'foto' => $meta['kamar']->fotoCover(),
                'skor' => round($skor, 5),
                'jarak_km' => $meta['jarak_km'],
                'harga_prediksi' => $meta['harga_prediksi'],
                'selisih_persen' => $meta['selisih_persen'],
                'label_kewajaran' => $meta['label_kewajaran'],
                'nilai_kriteria' => $matrix[$i],
                'detail_skor' => $radarScore,
                'alasan' => $alasan,
            ];
        }

        // Urutkan menurun berdasarkan skor preferensi
        usort($hasil, fn ($a, $b) => $b['skor'] <=> $a['skor']);

        // Tetapkan nomor peringkat
        foreach ($hasil as $idx => &$item) {
            $item['peringkat'] = $idx + 1;
        }
        unset($item);

        return $hasil;
    }

    /**
     * Hitung Uji Sensitivitas Bobot (+- 25% pada tiap kriteria).
     */
    protected function hitungSensitivitas(
        array $matrix,
        array $kamarMeta,
        Collection $kriterias,
        array $bobotAwal,
        array $rankingAwal
    ): array {
        $kodeList = ['C1', 'C2', 'C3', 'C4', 'C5'];
        $hasilSensitivitas = [];
        $top3Awal = collect($rankingAwal)->take(3)->pluck('kamar_id')->all();

        foreach ($kodeList as $kode) {
            $bobotVariasi = $bobotAwal;
            // Tingkatkan 20% kriteria ini lalu renormalisasi
            $bobotVariasi[$kode] = $bobotVariasi[$kode] * 1.25;
            $sum = array_sum($bobotVariasi);
            foreach ($bobotVariasi as $k => $v) {
                $bobotVariasi[$k] = $v / $sum;
            }

            $rankingBaru = $this->hitungTopsis($matrix, $kamarMeta, $kriterias, $bobotVariasi);
            $top3Baru = collect($rankingBaru)->take(3)->pluck('kamar_id')->all();

            $perubahanTop3 = ($top3Awal !== $top3Baru);
            $namaKriteria = $kriterias->get($kode)?->nama ?? $kode;

            $hasilSensitivitas[$kode] = [
                'kriteria' => $namaKriteria,
                'stabil' => ! $perubahanTop3,
                'keterangan' => ! $perubahanTop3
                    ? "Peringkat 3 teratas tetap stabil jika bobot {$namaKriteria} dinaikkan."
                    : "Peringkat 3 teratas bergeser ketika bobot {$namaKriteria} dinaikkan.",
            ];
        }

        return $hasilSensitivitas;
    }

    /**
     * Normalisasi bobot kriteria sehingga total tepat 1.0.
     */
    public function normalisasiBobot(Collection $kriterias, array $bobotUser): array
    {
        $bobot = [];
        $total = 0.0;

        foreach ($kriterias as $k) {
            $val = isset($bobotUser[$k->kode]) ? (float) $bobotUser[$k->kode] : (float) $k->bobot_default;
            $val = max(0.01, $val);
            $bobot[$k->kode] = $val;
            $total += $val;
        }

        if ($total > 0) {
            foreach ($bobot as $k => $v) {
                $bobot[$k] = round($v / $total, 4);
            }
        }

        return $bobot;
    }

    /**
     * Narasi alasan peringkat yang transparan dan dapat dipertanggungjawabkan (FR-05).
     */
    protected function buatAlasanPeringkat(array $nilai, array $radar, array $meta): string
    {
        $keunggulan = [];

        if ($radar['C1'] >= 75) {
            $keunggulan[] = 'harga sewa yang sangat ekonomis';
        }
        if ($radar['C2'] >= 75 || $meta['label_kewajaran'] === 'di_bawah_pasar') {
            $keunggulan[] = 'harga berada di bawah estimasi wajar pasar (sangat menguntungkan)';
        }
        if ($radar['C3'] >= 75) {
            $keunggulan[] = 'lokasi sangat dekat ke kampus ('.round($meta['jarak_km'], 2).' km)';
        }
        if ($radar['C4'] >= 75) {
            $keunggulan[] = 'fasilitas kamar sangat lengkap';
        }
        if ($radar['C5'] >= 75) {
            $keunggulan[] = 'ukuran kamar relatif luas ('.$nilai['C5'].' m²)';
        }

        if (empty($keunggulan)) {
            return 'Memiliki keseimbangan nilai yang baik antara harga, jarak, dan fasilitas.';
        }

        return 'Unggul pada '.implode(', ', $keunggulan).'.';
    }
}
