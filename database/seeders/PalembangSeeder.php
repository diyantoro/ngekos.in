<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\Properti;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Database\Seeder;

class PalembangSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::all()->keyBy('email');

        $budi = $u['pemilik1@ngekos.test'] ?? null;
        $siti = $u['pemilik2@ngekos.test'] ?? null;
        $admin = $u['admin.ngekos@gmail.com'] ?? null;
        $anak1 = $u['anak1@ngekos.test'] ?? null;
        $anak2 = $u['anak2@ngekos.test'] ?? null;
        $anak3 = $u['anak3@ngekos.test'] ?? null;

        if (! $budi || ! $siti) {
            return;
        }

        $data = [
            [
                'nama' => 'Kost Bukit Siguntang', 'tipe' => 'campur', 'kota' => 'Palembang',
                'alamat' => 'Bukit Besar, Ilir Barat I', 'lat' => -2.9989, 'lng' => 104.7321,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => '5 menit ke UNSRI Bukit Besar, gang tenang di daerah Bukit Siguntang. Ibu kosnya ramah.',
                'harga' => 1200000, 'asli' => 1350000,
                'kamar' => [['A1', 1, 1200000, 'tersedia'], ['A2', 1, 1200000, 'terisi'], ['A3', 2, 1400000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Demang Putri', 'tipe' => 'putri', 'kota' => 'Palembang',
                'alamat' => 'Demang Lebar Daun, Ilir Barat I', 'lat' => -2.9812, 'lng' => 104.7445,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur',
                'deskripsi' => 'Khusus putri di kawasan Demang, deket ke PIM dan kampus UMP. Gerbang dikunci jam 10 malam.',
                'harga' => 950000, 'asli' => null,
                'kamar' => [['1', 1, 950000, 'tersedia'], ['2', 1, 950000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Kenten Permai', 'tipe' => 'putra', 'kota' => 'Palembang',
                'alamat' => 'Kenten, Sako', 'lat' => -2.9556, 'lng' => 104.7689,
                'fasilitas' => 'WiFi·Kasur·Parkir Motor·Dapur Bersama',
                'deskripsi' => 'Khusus putra, lingkungannya rame anak rantau. Deket ke KM 5 dan akses ke bandara SMB II.',
                'harga' => 650000, 'asli' => null,
                'kamar' => [['B1', 1, 650000, 'tersedia'], ['B2', 1, 650000, 'terisi']],
            ],
            [
                'nama' => 'Kost Jakabaring Sport City', 'tipe' => 'campur', 'kota' => 'Palembang',
                'alamat' => 'Jakabaring, Seberang Ulu I', 'lat' => -3.0201, 'lng' => 104.7892,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Seberang Jembatan Ampera, 10 menit ke UIN Raden Fatah dan Jakabaring Sport City. Bangunan baru.',
                'harga' => 1100000, 'asli' => 1250000,
                'kamar' => [['C1', 1, 1100000, 'tersedia'], ['C2', 1, 1100000, 'tersedia'], ['C3', 2, 1300000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Plaju Bagus', 'tipe' => 'putra', 'kota' => 'Palembang',
                'alamat' => 'Plaju, Seberang Ulu II', 'lat' => -3.0056, 'lng' => 104.8214,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kasur·Akses 24 Jam',
                'deskripsi' => 'Deket komplek Pusri dan KI Plaju, cocok buat pekerja. Warteg sama laundry pada deket.',
                'harga' => 750000, 'asli' => null,
                'kamar' => [['1', 1, 750000, 'tersedia'], ['2', 1, 750000, 'terisi']],
            ],
            [
                'nama' => 'Kost Indralaya Gerbang UNSRI', 'tipe' => 'campur', 'kota' => 'Ogan Ilir',
                'alamat' => 'Indralaya, Ogan Ilir', 'lat' => -3.2212, 'lng' => 104.6501,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Jalan kaki ke gerbang UNSRI Indralaya, isinya anak kampus semua. Fotokopian sama kantin di depan gang.',
                'harga' => 850000, 'asli' => 975000,
                'kamar' => [['D1', 1, 850000, 'tersedia'], ['D2', 1, 850000, 'tersedia'], ['D3', 2, 1000000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Timbangan Indralaya', 'tipe' => 'putri', 'kota' => 'Ogan Ilir',
                'alamat' => 'Timbangan, Indralaya Utara', 'lat' => -3.2089, 'lng' => 104.6587,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kasur',
                'deskripsi' => 'Khusus putri di kawasan Timbangan, 5 menit ke kampus naik ojek. Keamanan terjaga, CCTV 24 jam.',
                'harga' => 600000, 'asli' => null,
                'kamar' => [['E1', 1, 600000, 'tersedia'], ['E2', 1, 600000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Kertapati Muara', 'tipe' => 'campur', 'kota' => 'Palembang',
                'alamat' => 'Kertapati, Seberang Ulu I', 'lat' => -3.0156, 'lng' => 104.7534,
                'fasilitas' => 'WiFi·Kasur·Parkir Motor',
                'deskripsi' => 'Deket Stasiun Kertapati, gampang kalo mau pulang kampung naik kereta. Harganya bersahabat.',
                'harga' => 550000, 'asli' => null,
                'kamar' => [['1', 1, 550000, 'tersedia'], ['2', 1, 550000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Basuki Rahmat Executive', 'tipe' => 'putri', 'kota' => 'Palembang',
                'alamat' => 'Basuki Rahmat, Kemuning', 'lat' => -2.9712, 'lng' => 104.7589,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Laundry',
                'deskripsi' => 'Tipe executive di pusat kota, deket Palembang Square dan kantor-kantor. Buat karyawati yang mau nyaman.',
                'harga' => 1500000, 'asli' => 1650000,
                'kamar' => [['F1', 1, 1500000, 'tersedia'], ['F2', 1, 1500000, 'terisi']],
            ],
            [
                'nama' => 'Kost Sako Kenten Baru', 'tipe' => 'campur', 'kota' => 'Palembang',
                'alamat' => 'Sako, Kenten', 'lat' => -2.9478, 'lng' => 104.7612,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur·Akses 24 Jam',
                'deskripsi' => 'Sering penuh karena deket ke POLSRI dan IAIN. Booking dulu aja sebelum survei ke lokasi.',
                'harga' => 900000, 'asli' => 1025000,
                'kamar' => [['G1', 1, 900000, 'tersedia'], ['G2', 1, 900000, 'tersedia'], ['G3', 2, 1050000, 'tersedia']],
            ],
        ];

        $komentars = [
            [5, 'Kamarnya sesuai foto, air sama wifi lancar. Ibu kosnya baik.'],
            [5, 'Udah setahun di sini, betah. Deket kampus banget.'],
            [4, 'Lumayan lah buat harga segini. Parkiran agak sempit aja.'],
            [5, 'Bersih, aman, tetangga kosnya asik-asik.'],
            [4, 'Pempek langganan deket gang, bahaya buat dompet. Recommended.'],
        ];

        $pengulas = array_filter([$anak1, $anak2, $anak3]);
        $adminIds = array_filter([$admin?->id]);

        foreach ($data as $i => $row) {
            $pemilik = $i % 2 === 0 ? $budi : $siti;

            $properti = Properti::firstOrCreate(['nama' => $row['nama']], [
                'pemilik_id' => $pemilik->id,
                'kota' => $row['kota'],
                'alamat' => $row['alamat'],
                'latitude' => $row['lat'],
                'longitude' => $row['lng'],
                'deskripsi' => $row['deskripsi'],
                'fasilitas' => $row['fasilitas'],
                'aturan' => 'Tamu dilarang menginap tanpa izin pemilik.',
                'denda_per_hari' => 5000,
                'harga' => $row['harga'],
                'harga_asli' => $row['asli'] && $row['asli'] > $row['harga'] ? $row['asli'] : null,
                'tipe_hunian' => $row['tipe'],
                'status' => 'aktif',
            ]);

            if ($adminIds !== []) {
                $properti->admins()->syncWithoutDetaching($adminIds);
            }

            foreach ($row['kamar'] as [$namaKamar, $kapasitas, $hargaKamar, $status]) {
                Kamar::firstOrCreate(
                    ['properti_id' => $properti->id, 'nama' => $namaKamar],
                    [
                        'kapasitas' => $kapasitas,
                        'harga_sewa_bulanan' => $hargaKamar,
                        'harga_asli' => $row['asli'] && $row['asli'] > $row['harga'] ? $row['asli'] : null,
                        'status' => $status,
                    ]
                );
            }

            if ($pengulas !== [] && $i % 3 !== 2) {
                $acak = $komentars[$i % count($komentars)];
                $penulis = array_values($pengulas)[$i % count($pengulas)];
                Ulasan::firstOrCreate(
                    ['user_id' => $penulis->id, 'properti_id' => $properti->id],
                    ['rating' => $acak[0], 'komentar' => $acak[1]]
                );
            }
        }
    }
}
