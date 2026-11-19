<?php

namespace App\Services;

use App\Models\Kamar;
use App\Models\ModelVersi;
use App\Models\PrediksiHarga;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MlModelService
{
    protected string $baseUrl;

    protected string $token;

    protected float $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ml_service.url', 'http://127.0.0.1:8001'), '/');
        $this->token = (string) config('services.ml_service.token', 'ngekos-secret-api-token');
        $this->timeout = (float) config('services.ml_service.timeout', 3.0);
    }

    /**
     * Memeriksa status kesehatan layanan microservice ML.
     */
    public function checkHealth(): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/health");

            return $response->successful() && ($response->json('status') === 'healthy');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mendapatkan informasi versi model aktif beserta metrik evaluasinya.
     */
    public function getModelInfo(): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->token)
                ->get("{$this->baseUrl}/model/info");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal mengambil info model dari ML Service: {$e->getMessage()}");
        }

        // Fallback dari database
        $modelDb = ModelVersi::modelAktif();
        if ($modelDb) {
            return [
                'versi' => $modelDb->versi,
                'algoritma' => $modelDb->algoritma,
                'status' => $modelDb->status,
                'mae' => (float) $modelDb->mae,
                'rmse' => (float) $modelDb->rmse,
                'r2' => (float) $modelDb->r2,
                'mape' => (float) $modelDb->mape,
                'hyperparameter' => $modelDb->hyperparameter ?? [],
            ];
        }

        return null;
    }

    /**
     * Prediksi harga wajar untuk satu kamar.
     * Mengembalikan array hasil atau fallback jika service offline (FR-11).
     *
     * @param array{
     *     kamar_id?: int,
     *     luas_m2: float,
     *     jarak_km: float,
     *     kawasan?: string,
     *     tipe_penghuni?: string,
     *     fasilitas?: array,
     *     harga_aktual?: float
     * } $fitur
     */
    public function predictSingle(array $fitur, bool $simpanKeDatabase = true): ?array
    {
        $payload = [
            'kamar_id' => $fitur['kamar_id'] ?? null,
            'luas_m2' => (float) ($fitur['luas_m2'] ?? 12.0),
            'jarak_km' => (float) ($fitur['jarak_km'] ?? 1.0),
            'kawasan' => (string) ($fitur['kawasan'] ?? 'Coblong'),
            'tipe_penghuni' => (string) ($fitur['tipe_penghuni'] ?? 'Campur'),
            'fasilitas' => (array) ($fitur['fasilitas'] ?? []),
            'harga_aktual' => isset($fitur['harga_aktual']) ? (float) $fitur['harga_aktual'] : null,
        ];

        $hasil = null;

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->token)
                ->post("{$this->baseUrl}/predict", $payload);

            if ($response->successful()) {
                $hasil = $response->json();
            }
        } catch (\Throwable $e) {
            Log::info("ML Service FastAPI tidak tersedia ({$e->getMessage()}), menggunakan estimasi terkalibrasi internal.");
        }

        // Jika ML service offline, gunakan fallback estimator parametrik terkalibrasi
        if ($hasil === null) {
            $hasil = $this->fallbackEstimator($payload);
        }

        if ($hasil && $simpanKeDatabase && ! empty($fitur['kamar_id'])) {
            $this->simpanPrediksi((int) $fitur['kamar_id'], $hasil);
        }

        return $hasil;
    }

    /**
     * Prediksi harga wajar dalam batch untuk pemeringkatan SPK TOPSIS.
     *
     * @param  array<int, array>  $items
     * @return array<int, array>
     */
    public function predictBatch(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        try {
            $response = Http::timeout($this->timeout + 2.0)
                ->withToken($this->token)
                ->post("{$this->baseUrl}/predict/batch", ['items' => $items]);

            if ($response->successful()) {
                $body = $response->json();

                return $body['predictions'] ?? [];
            }
        } catch (\Throwable $e) {
            Log::info("Batch ML Service FastAPI offline, menggunakan fallback internal: {$e->getMessage()}");
        }

        // Fallback per item
        $hasil = [];
        foreach ($items as $item) {
            $pred = $this->fallbackEstimator($item);
            $pred['kamar_id'] = $item['kamar_id'] ?? null;
            $hasil[] = $pred;
        }

        return $hasil;
    }

    /**
     * Pelatihan ulang model dari data latih (FR-09).
     */
    public function triggerTrain(array $dataRecords): ?array
    {
        try {
            $response = Http::timeout(30.0)
                ->withToken($this->token)
                ->post("{$this->baseUrl}/train", ['data' => $dataRecords]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::error("Gagal melatih model via API: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Menyimpan hasil prediksi ke tabel prediksi_harga.
     */
    public function simpanPrediksi(int $kamarId, array $hasil): PrediksiHarga
    {
        $versiDb = ModelVersi::where('versi', (string) ($hasil['model_versi'] ?? ''))->first()
            ?? ModelVersi::modelAktif();

        return PrediksiHarga::create([
            'kamar_id' => $kamarId,
            'model_versi_id' => $versiDb?->id,
            'harga_prediksi' => $hasil['harga_prediksi'] ?? 0,
            'selisih_persen' => $hasil['selisih_persen'] ?? 0,
            'label_kewajaran' => $hasil['label_kewajaran'] ?? 'wajar',
        ]);
    }

    /**
     * Fallback regresi parametrik pasar jika service FastAPI tidak merespons (FR-11).
     */
    public function fallbackEstimator(array $payload): array
    {
        $luas = (float) ($payload['luas_m2'] ?? 12.0);
        $jarak = max(0.1, (float) ($payload['jarak_km'] ?? 1.0));
        $kawasan = strtolower((string) ($payload['kawasan'] ?? ''));
        $fasilitas = (array) ($payload['fasilitas'] ?? []);
        $hargaAktual = isset($payload['harga_aktual']) ? (float) $payload['harga_aktual'] : null;

        // Base + luas
        $harga = 500000.0 + ($luas * 55000.0);

        // Pengaruh jarak (eksponensial meluruh)
        $harga += 450000.0 * exp(-0.18 * $jarak);

        // Tambahan fasilitas
        $facStr = strtolower(implode(' ', $fasilitas));
        if (str_contains($facStr, 'ac')) {
            $harga += 400000.0;
        }
        if (str_contains($facStr, 'dalam') || str_contains($facStr, 'kamar mandi dalam')) {
            $harga += 300000.0;
        }
        if (str_contains($facStr, 'water heater') || str_contains($facStr, 'panas')) {
            $harga += 150000.0;
        }
        if (str_contains($facStr, 'wifi') || str_contains($facStr, 'internet')) {
            $harga += 75000.0;
        }
        if (str_contains($facStr, 'kasur') || str_contains($facStr, 'springbed')) {
            $harga += 80000.0;
        }
        if (str_contains($facStr, 'lemari')) {
            $harga += 50000.0;
        }
        if (str_contains($facStr, 'meja')) {
            $harga += 40000.0;
        }
        if (str_contains($facStr, 'mobil')) {
            $harga += 150000.0;
        }
        if (str_contains($facStr, 'dapur')) {
            $harga += 50000.0;
        }

        if (str_contains($kawasan, 'dago') || str_contains($kawasan, 'coblong')) {
            $harga *= 1.12;
        } elseif (str_contains($kawasan, 'depok') || str_contains($kawasan, 'yogyakarta')) {
            $harga *= 1.05;
        }

        $prediksi = (int) (round($harga / 25000.0) * 25000.0);

        $selisihPersen = null;
        $label = 'wajar';

        if ($hargaAktual !== null && $hargaAktual > 0) {
            $selisihPersen = round((($hargaAktual - $prediksi) / $prediksi) * 100.0, 2);
            if ($selisihPersen < -10.0) {
                $label = 'di_bawah_pasar';
            } elseif ($selisihPersen > 10.0) {
                $label = 'di_atas_pasar';
            } else {
                $label = 'wajar';
            }
        }

        return [
            'harga_prediksi' => $prediksi,
            'selisih_persen' => $selisihPersen,
            'label_kewajaran' => $label,
            'model_versi' => 'v1.0-mlp-baseline',
            'detail' => [
                'fallback' => true,
                'luas_m2' => $luas,
                'jarak_km' => $jarak,
            ],
        ];
    }
}
