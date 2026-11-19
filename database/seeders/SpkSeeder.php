<?php

namespace Database\Seeders;

use App\Models\DataLatih;
use App\Models\Kriteria;
use App\Models\ModelVersi;
use App\Models\TitikAcuan;
use Illuminate\Database\Seeder;

class SpkSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kriteria TOPSIS
        $kriterias = [
            [
                'kode' => 'C1',
                'nama' => 'Harga Sewa per Bulan',
                'tipe' => 'cost',
                'bobot_default' => 0.2500,
            ],
            [
                'kode' => 'C2',
                'nama' => 'Kewajaran Harga',
                'tipe' => 'benefit',
                'bobot_default' => 0.2000,
            ],
            [
                'kode' => 'C3',
                'nama' => 'Jarak ke Titik Acuan',
                'tipe' => 'cost',
                'bobot_default' => 0.2500,
            ],
            [
                'kode' => 'C4',
                'nama' => 'Kelengkapan Fasilitas',
                'tipe' => 'benefit',
                'bobot_default' => 0.1500,
            ],
            [
                'kode' => 'C5',
                'nama' => 'Luas Kamar',
                'tipe' => 'benefit',
                'bobot_default' => 0.1500,
            ],
        ];

        foreach ($kriterias as $k) {
            Kriteria::updateOrCreate(['kode' => $k['kode']], $k);
        }

        // 2. Titik Acuan (Kampus Terkenal)
        $titikAcuans = [
            [
                'nama' => 'Institut Teknologi Bandung (ITB Ganesha)',
                'latitude' => -6.8915000,
                'longitude' => 107.6107000,
                'kategori' => 'kampus',
            ],
            [
                'nama' => 'Universitas Padjadjaran (Unpad Dipatiukur)',
                'latitude' => -6.8930000,
                'longitude' => 107.6178000,
                'kategori' => 'kampus',
            ],
            [
                'nama' => 'Telkom University Bandung',
                'latitude' => -6.9740000,
                'longitude' => 107.6305000,
                'kategori' => 'kampus',
            ],
            [
                'nama' => 'Universitas Indonesia (UI Depok)',
                'latitude' => -6.3650000,
                'longitude' => 106.8286000,
                'kategori' => 'kampus',
            ],
            [
                'nama' => 'Universitas Gadjah Mada (UGM Yogyakarta)',
                'latitude' => -7.7709000,
                'longitude' => 110.3776000,
                'kategori' => 'kampus',
            ],
            [
                'nama' => 'Universitas Brawijaya (UB Malang)',
                'latitude' => -7.9525000,
                'longitude' => 112.6144000,
                'kategori' => 'kampus',
            ],
            [
                'nama' => 'Institut Teknologi Sepuluh Nopember (ITS Surabaya)',
                'latitude' => -7.2824000,
                'longitude' => 112.7949000,
                'kategori' => 'kampus',
            ],
            [
                'nama' => 'Universitas Sriwijaya (UNSRI Bukit Besar)',
                'latitude' => -2.9850000,
                'longitude' => 104.7320000,
                'kategori' => 'kampus',
            ],
        ];

        foreach ($titikAcuans as $ta) {
            TitikAcuan::updateOrCreate(['nama' => $ta['nama']], $ta);
        }

        // 3. Model Versi Awal
        ModelVersi::updateOrCreate(
            ['versi' => 'v1.0-mlp-baseline'],
            [
                'algoritma' => 'Multilayer Perceptron (MLP)',
                'hyperparameter' => [
                    'layers' => [64, 32, 16],
                    'activation' => 'relu',
                    'dropout' => 0.2,
                    'optimizer' => 'adam',
                    'target_transform' => 'log1p',
                ],
                'mae' => 85000.00,
                'rmse' => 115000.00,
                'r2' => 0.8920,
                'mape' => 7.80,
                'artifact_path' => 'models/v1.0-mlp-baseline.joblib',
                'status' => 'aktif',
                'dilatih_pada' => now(),
            ]
        );

        // 4. Data Latih Awal (Survei)
        if (DataLatih::count() === 0) {
            $sampel = [
                ['luas_m2' => 12, 'jarak_km' => 0.8, 'kawasan' => 'Coblong', 'tipe_penghuni' => 'Campur', 'fasilitas' => ['WiFi', 'Kamar Mandi Dalam', 'Kasur', 'Lemari'], 'harga_aktual' => 1200000],
                ['luas_m2' => 16, 'jarak_km' => 1.2, 'kawasan' => 'Coblong', 'tipe_penghuni' => 'Putri', 'fasilitas' => ['WiFi', 'AC', 'Kamar Mandi Dalam', 'Kasur', 'Lemari', 'Meja Belajar'], 'harga_aktual' => 1750000],
                ['luas_m2' => 9, 'jarak_km' => 2.5, 'kawasan' => 'Sukajadi', 'tipe_penghuni' => 'Putra', 'fasilitas' => ['WiFi', 'Kasur', 'Lemari'], 'harga_aktual' => 750000],
                ['luas_m2' => 20, 'jarak_km' => 0.5, 'kawasan' => 'Coblong', 'tipe_penghuni' => 'Campur', 'fasilitas' => ['WiFi', 'AC', 'Kamar Mandi Dalam', 'Water Heater', 'Kasur', 'Lemari', 'Meja Belajar', 'Dapur Bersama'], 'harga_aktual' => 2500000],
                ['luas_m2' => 12, 'jarak_km' => 1.5, 'kawasan' => 'Cibeunying Kaler', 'tipe_penghuni' => 'Putri', 'fasilitas' => ['WiFi', 'Kamar Mandi Dalam', 'Kasur', 'Lemari'], 'harga_aktual' => 1100000],
                ['luas_m2' => 15, 'jarak_km' => 0.9, 'kawasan' => 'Coblong', 'tipe_penghuni' => 'Campur', 'fasilitas' => ['WiFi', 'AC', 'Kamar Mandi Dalam', 'Kasur'], 'harga_aktual' => 1600000],
                ['luas_m2' => 10, 'jarak_km' => 3.0, 'kawasan' => 'Dago', 'tipe_penghuni' => 'Putra', 'fasilitas' => ['WiFi', 'Kasur', 'Parkir Motor'], 'harga_aktual' => 850000],
            ];

            foreach ($sampel as $row) {
                DataLatih::create(array_merge($row, [
                    'sumber' => 'survei',
                    'ditandai_outlier' => false,
                ]));
            }
        }
    }
}
