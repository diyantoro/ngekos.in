<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\Properti;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Database\Seeder;

class KatalogMamikosSeeder extends Seeder
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
                'nama' => 'Kost Habibie Ploso Tipe A', 'tipe' => 'campur', 'kota' => 'Surabaya',
                'alamat' => 'Ploso, Tambaksari', 'lat' => -7.2636, 'lng' => 112.7589,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Gang tenang di belakang UNAIR kampus B, jalan kaki 5 menit ke gerbang. Ibu kosnya enak diajak ngobrol.',
                'harga' => 1550000, 'asli' => 1472500 + 78000,
                'kamar' => [['A', 1, 1550000, 'tersedia'], ['B', 1, 1550000, 'terisi'], ['C', 2, 1750000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Mitra Jaya Bratang Tipe A', 'tipe' => 'putri', 'kota' => 'Surabaya',
                'alamat' => 'Bratang, Wonokromo', 'lat' => -7.2924, 'lng' => 112.7478,
                'fasilitas' => 'WiFi·Kasur·Akses 24 Jam',
                'deskripsi' => 'Khusus putri, lingkungannya rame anak UNAIR. Dapur bersamanya lumayan lengkap.',
                'harga' => 794500, 'asli' => 794500,
                'kamar' => [['A', 1, 794500, 'tersedia'], ['B', 1, 794500, 'terisi']],
            ],
            [
                'nama' => 'Kost Mitra Jaya Bratang Tipe B', 'tipe' => 'putri', 'kota' => 'Surabaya',
                'alamat' => 'Bratang, Wonokromo', 'lat' => -7.2931, 'lng' => 112.7485,
                'fasilitas' => 'WiFi·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Versi lebih murahnya Mitra Jaya, kamar sedikit lebih kecil tapi bersih.',
                'harga' => 632500, 'asli' => 632500,
                'kamar' => [['B1', 1, 632500, 'tersedia'], ['B2', 1, 632500, 'tersedia']],
            ],
            [
                'nama' => 'Kost Aurora Tipe C Barat', 'tipe' => 'campur', 'kota' => 'Jakarta Barat',
                'alamat' => 'Grogol Petamburan', 'lat' => -6.1637, 'lng' => 106.7928,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Dekat kampus Trisakti dan UNTAR, angkot sama busway gampang. Bangunannya masih baru.',
                'harga' => 2025000, 'asli' => 1925000 + 100000,
                'kamar' => [['C1', 1, 2025000, 'tersedia'], ['C2', 1, 2025000, 'terisi'], ['C3', 2, 2300000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Marvel BSD Tipe A', 'tipe' => 'putri', 'kota' => 'Tangerang Selatan',
                'alamat' => 'Pagedangan, BSD', 'lat' => -6.3078, 'lng' => 106.6397,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur',
                'deskripsi' => 'Khusus putri di kawasan BSD, cocok buat yang kuliah di Prasetiya Mulya atau kerja di sekitar sini.',
                'harga' => 1380000, 'asli' => 1242000 + 138000,
                'kamar' => [['A1', 1, 1380000, 'tersedia'], ['A2', 1, 1380000, 'tersedia']],
            ],
            [
                'nama' => 'Kost ABS Housing Tipe B Barat', 'tipe' => 'campur', 'kota' => 'Jakarta Barat',
                'alamat' => 'Kebon Jeruk', 'lat' => -6.1909, 'lng' => 106.7678,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Sering penuh karena lokasinya strategis, booking dulu aja sebelum survei.',
                'harga' => 2525000, 'asli' => 2275000 + 250000,
                'kamar' => [['B1', 1, 2525000, 'tersedia'], ['B2', 2, 2800000, 'tersedia']],
            ],
            [
                'nama' => 'Kost The Student CoLiving GSV Tipe A', 'tipe' => 'putra', 'kota' => 'Bogor',
                'alamat' => 'Dramaga', 'lat' => -6.5614, 'lng' => 106.7298,
                'fasilitas' => 'WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Isinya anak IPB semua, enak buat cari temen belajar. Ada coworking space di lantai 1.',
                'harga' => 1525000, 'asli' => 1360000 + 165000,
                'kamar' => [['A1', 1, 1525000, 'tersedia'], ['A2', 1, 1525000, 'terisi']],
            ],
            [
                'nama' => 'Kost The Student House GSV Hijau G16 Tipe A', 'tipe' => 'putri', 'kota' => 'Bogor',
                'alamat' => 'Dramaga', 'lat' => -6.5598, 'lng' => 106.7312,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Satu kompleks sama versi putranya, beda gedung. Security 24 jam jadi aman.',
                'harga' => 1325000, 'asli' => 1182000 + 143000,
                'kamar' => [['G16-A', 1, 1325000, 'tersedia'], ['G16-B', 1, 1325000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Griya Mahri Tipe A', 'tipe' => 'campur', 'kota' => 'Depok',
                'alamat' => 'Cimanggis', 'lat' => -6.3734, 'lng' => 106.8356,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => '15 menit ke UI naik motor, jalannya nggak macet-macet amat lewat Kukusan.',
                'harga' => 1360000, 'asli' => 1226500 + 134000,
                'kamar' => [['A1', 1, 1360000, 'tersedia'], ['A2', 1, 1360000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Griya Mahri Tipe B', 'tipe' => 'campur', 'kota' => 'Depok',
                'alamat' => 'Cimanggis', 'lat' => -6.3741, 'lng' => 106.8362,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kasur·Akses 24 Jam',
                'deskripsi' => 'Tipe non-AC-nya Griya Mahri, paling laris buat anak baru.',
                'harga' => 1085000, 'asli' => 979000 + 106000,
                'kamar' => [['B1', 1, 1085000, 'tersedia'], ['B2', 1, 1085000, 'terisi']],
            ],
            [
                'nama' => 'Kost Griya Mahri Tipe C', 'tipe' => 'campur', 'kota' => 'Depok',
                'alamat' => 'Cimanggis', 'lat' => -6.3748, 'lng' => 106.8368,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kasur·Akses 24 Jam',
                'deskripsi' => 'Paling murah di Griya Mahri, kamarnya menghadap taman belakang. Adem.',
                'harga' => 1025000, 'asli' => 925000 + 100000,
                'kamar' => [['C1', 1, 1025000, 'tersedia'], ['C2', 1, 1025000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Zire Kukusan', 'tipe' => 'putra', 'kota' => 'Depok',
                'alamat' => 'Kukusan, Beji', 'lat' => -6.3687, 'lng' => 106.8234,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kloset Duduk·Kasur',
                'deskripsi' => 'Jalan kaki ke gerbang UI Kukusan, tinggal 1 kamar yang kosong. Siapa cepat dia dapat.',
                'harga' => 925000, 'asli' => null,
                'kamar' => [['1', 1, 925000, 'tersedia'], ['2', 1, 925000, 'terisi'], ['3', 1, 925000, 'terisi']],
            ],
            [
                'nama' => 'Kost Cahaya Surya Kukusan Tipe B', 'tipe' => 'campur', 'kota' => 'Depok',
                'alamat' => 'Kukusan, Beji', 'lat' => -6.3698, 'lng' => 106.8212,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Baru renov tahun lalu, cat masih kinclong dan airnya kenceng.',
                'harga' => 1075000, 'asli' => 1043500 + 32000,
                'kamar' => [['B1', 1, 1075000, 'tersedia'], ['B2', 1, 1075000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Puri Gading 1 UI Tipe B', 'tipe' => 'putra', 'kota' => 'Depok',
                'alamat' => 'Kukusan, Beji', 'lat' => -6.3712, 'lng' => 106.8189,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kasur',
                'deskripsi' => 'Langganan anak FMIPA dari tahun ke tahun, bapak kosnya baik banget.',
                'harga' => 948000, 'asli' => 920310 + 28000,
                'kamar' => [['B1', 1, 948000, 'tersedia'], ['B2', 1, 948000, 'terisi']],
            ],
            [
                'nama' => 'Kost Dtc Tipe E', 'tipe' => 'campur', 'kota' => 'Depok',
                'alamat' => 'Pancoran Mas', 'lat' => -6.4023, 'lng' => 106.8198,
                'fasilitas' => 'WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Kamar mandi luar tapi bersih banget, disikat tiap hari sama mbaknya.',
                'harga' => 1225000, 'asli' => 1105000 + 120000,
                'kamar' => [['E1', 1, 1225000, 'tersedia'], ['E2', 1, 1225000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Kukusan Ahmad Dahlan Tipe B', 'tipe' => 'putra', 'kota' => 'Depok',
                'alamat' => 'Kukusan, Beji', 'lat' => -6.3667, 'lng' => 106.8267,
                'fasilitas' => 'WiFi·AC·Kasur·Akses 24 Jam',
                'deskripsi' => 'Nempel sama warteg legendaris, nggak bakal kelaperan malem-malem.',
                'harga' => 1655000, 'asli' => 1606100 + 49000,
                'kamar' => [['B1', 1, 1655000, 'tersedia'], ['B2', 1, 1655000, 'terisi']],
            ],
            [
                'nama' => 'Kost Joy Living Zion Kg Deluxe Utara', 'tipe' => 'campur', 'kota' => 'Jakarta Utara',
                'alamat' => 'Kelapa Gading', 'lat' => -6.1623, 'lng' => 106.9087,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur',
                'deskripsi' => 'Buat yang magang di Kelapa Gading, deket ke mall dan kantor-kantor. Promo 2026 masih jalan.',
                'harga' => 2700000, 'asli' => null,
                'kamar' => [['Deluxe-1', 1, 2700000, 'tersedia'], ['Deluxe-2', 1, 2700000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Purple House', 'tipe' => 'putri', 'kota' => 'Malang',
                'alamat' => 'Lowokwaru', 'lat' => -7.9467, 'lng' => 112.6145,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Gangnya rame anak UB, ke kampus 10 menit. Cat ungunya ikonik, gampang dicari.',
                'harga' => 1275000, 'asli' => null,
                'kamar' => [['1', 1, 1275000, 'tersedia'], ['2', 1, 1275000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Stayhome Executive', 'tipe' => 'putri', 'kota' => 'Semarang',
                'alamat' => 'Banyumanik', 'lat' => -7.0634, 'lng' => 110.4234,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Eksklusif khusus putri, ada pantry tiap lantai. Deket UNDIP Tembalang, mewah tapi harganya masuk akal.',
                'harga' => 1850000, 'asli' => null,
                'kamar' => [['Ex-1', 1, 1850000, 'tersedia'], ['Ex-2', 1, 1850000, 'terisi'], ['Ex-3', 1, 1850000, 'tersedia']],
            ],
            [
                'nama' => 'Kost GnF Modern Tipe A', 'tipe' => 'campur', 'kota' => 'Tangerang',
                'alamat' => 'Kelapa Dua', 'lat' => -6.2878, 'lng' => 106.6345,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Buat anak UPH atau yang kerja di Gading Serpong, 10 menit doang. Diskon 10% bulan ini.',
                'harga' => 1350000, 'asli' => 1500000,
                'kamar' => [['A1', 1, 1350000, 'tersedia'], ['A2', 1, 1350000, 'tersedia'], ['A3', 1, 1350000, 'terisi']],
            ],
            [
                'nama' => 'Kost Zeal 1 Tipe B', 'tipe' => 'campur', 'kota' => 'Tangerang',
                'alamat' => 'Kosambi', 'lat' => -6.1678, 'lng' => 106.689,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Deket bandara, cocok buat kru atau pekerja shift. Sewa per 2 bulan lebih murah.',
                'harga' => 2700000, 'asli' => null,
                'kamar' => [['B1', 1, 2700000, 'tersedia'], ['B2', 1, 2700000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Tyaa', 'tipe' => 'putri', 'kota' => 'Malang',
                'alamat' => 'Lowokwaru', 'lat' => -7.9498, 'lng' => 112.6112,
                'fasilitas' => 'WiFi·Kloset Duduk·Kasur',
                'deskripsi' => 'Kos legendaris anak UB dari dulu, murah meriah. Bayar per semester jatuhnya lebih murah.',
                'harga' => 550000, 'asli' => null,
                'kamar' => [['1', 1, 550000, 'tersedia'], ['2', 1, 550000, 'tersedia'], ['3', 1, 550000, 'terisi']],
            ],
            [
                'nama' => 'Kost Zeal 1 Tipe A', 'tipe' => 'campur', 'kota' => 'Tangerang',
                'alamat' => 'Benda', 'lat' => -6.1145, 'lng' => 106.6834,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Tipe paling luas di Zeal 1, jendelanya gede jadi terang. Sewa per 2 bulan ada diskon.',
                'harga' => 2400000, 'asli' => null,
                'kamar' => [['A1', 1, 2400000, 'tersedia'], ['A2', 1, 2400000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Tya Lowokwaru', 'tipe' => 'campur', 'kota' => 'Malang',
                'alamat' => 'Lowokwaru', 'lat' => -7.9501, 'lng' => 112.6098,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur·Akses 24 Jam',
                'deskripsi' => 'Satu gang sama Tyaa tapi yang ini campur. Ada parkiran mobil, jarang ada di Lowokwaru.',
                'harga' => 1600000, 'asli' => null,
                'kamar' => [['1', 1, 1600000, 'tersedia'], ['2', 1, 1600000, 'terisi']],
            ],
            [
                'nama' => 'Kost Pogung Dalangan UGM', 'tipe' => 'putri', 'kota' => 'Sleman',
                'alamat' => 'Pogung, Mlati', 'lat' => -7.7698, 'lng' => 110.3745,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kasur·Lemari',
                'deskripsi' => 'Jalan kaki ke UGM, gangnya full anak kos jadi rame terus. Warung makan berjejer.',
                'harga' => 875000, 'asli' => 925000,
                'kamar' => [['1', 1, 875000, 'tersedia'], ['2', 1, 875000, 'tersedia'], ['3', 1, 875000, 'terisi']],
            ],
            [
                'nama' => 'Kost Merapi View Jakal', 'tipe' => 'putra', 'kota' => 'Sleman',
                'alamat' => 'Jl. Kaliurang KM 8', 'lat' => -7.7589, 'lng' => 110.3856,
                'fasilitas' => 'WiFi·Kamar mandi dalam·Kasur·Parkir motor',
                'deskripsi' => 'View Merapi dari jemuran lantai 3. Anak UGM, UNY, UII banyak yang ngekos di sini.',
                'harga' => 750000, 'asli' => null,
                'kamar' => [['1', 1, 750000, 'tersedia'], ['2', 1, 750000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Sapphire Tembalang', 'tipe' => 'campur', 'kota' => 'Semarang',
                'alamat' => 'Tembalang', 'lat' => -7.0512, 'lng' => 110.4401,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur·Akses 24 Jam',
                'deskripsi' => '5 menit ke gerbang UNDIP, bawahnya indomaret. Praktis banget buat anak rantau.',
                'harga' => 1150000, 'asli' => 1250000,
                'kamar' => [['A', 1, 1150000, 'tersedia'], ['B', 1, 1150000, 'terisi'], ['C', 2, 1350000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Sekar Arum Tembalang', 'tipe' => 'putri', 'kota' => 'Semarang',
                'alamat' => 'Tembalang', 'lat' => -7.0534, 'lng' => 110.4389,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kasur·Dapur bersama',
                'deskripsi' => 'Khusus putri, ibu kosnya cerewet tapi perhatian. Anak kos sering dibagi masakan.',
                'harga' => 680000, 'asli' => null,
                'kamar' => [['1', 1, 680000, 'tersedia'], ['2', 1, 680000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Dago Asri ITB', 'tipe' => 'campur', 'kota' => 'Bandung',
                'alamat' => 'Dago', 'lat' => -6.8845, 'lng' => 107.6123,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur·Akses 24 Jam',
                'deskripsi' => '10 menit jalan ke ITB, Dago bawah deket ke mana-mana. Anak rantau Bandung wajib survei.',
                'harga' => 1450000, 'asli' => 1575000,
                'kamar' => [['1', 1, 1450000, 'tersedia'], ['2', 1, 1450000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Cisitu Lama', 'tipe' => 'putra', 'kota' => 'Bandung',
                'alamat' => 'Cisitu, Dago', 'lat' => -6.8812, 'lng' => 107.6089,
                'fasilitas' => 'WiFi·Kasur·Kamar mandi luar·Parkir motor',
                'deskripsi' => 'Kos senior di Cisitu, murah dan bebas. Udah puluhan angkatan ngekos di sini.',
                'harga' => 600000, 'asli' => null,
                'kamar' => [['1', 1, 600000, 'tersedia'], ['2', 1, 600000, 'tersedia'], ['3', 1, 600000, 'terisi']],
            ],
            [
                'nama' => 'Kost Jatinangor Town Square', 'tipe' => 'campur', 'kota' => 'Sumedang',
                'alamat' => 'Jatinangor', 'lat' => -6.9358, 'lng' => 107.7723,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur',
                'deskripsi' => 'Seberang UNPAD, tinggal nyebrang doang. Sorenya rame banget, nggak bakal sepi.',
                'harga' => 1250000, 'asli' => 1370000,
                'kamar' => [['1', 1, 1250000, 'tersedia'], ['2', 1, 1250000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Griya Jatinangor', 'tipe' => 'putri', 'kota' => 'Sumedang',
                'alamat' => 'Jatinangor', 'lat' => -6.9378, 'lng' => 107.7698,
                'fasilitas' => 'K. Mandi Dalam·WiFi·Kasur·Akses 24 Jam',
                'deskripsi' => 'Khusus putri, gerbang dikunci jam 10 malem. Ortu di rumah jadi tenang.',
                'harga' => 950000, 'asli' => null,
                'kamar' => [['1', 1, 950000, 'tersedia'], ['2', 1, 950000, 'terisi']],
            ],
            [
                'nama' => 'Kost Sunset Road Denpasar', 'tipe' => 'campur', 'kota' => 'Denpasar',
                'alamat' => 'Kuta', 'lat' => -8.6789, 'lng' => 115.2123,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur·Kolam renang',
                'deskripsi' => 'Ada kolam renangnya, jarang-jarang kos ada kolam. Deket kampus Udayana Sudirman.',
                'harga' => 1900000, 'asli' => 2050000,
                'kamar' => [['1', 1, 1900000, 'tersedia'], ['2', 1, 1900000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Puri Bali Jimbaran', 'tipe' => 'putri', 'kota' => 'Badung',
                'alamat' => 'Jimbaran', 'lat' => -8.7878, 'lng' => 115.1689,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur',
                'deskripsi' => 'Buat anak Udayana Jimbaran, 7 menit ke kampus. Lingkungannya tenang, cocok buat skripsian.',
                'harga' => 1350000, 'asli' => null,
                'kamar' => [['1', 1, 1350000, 'tersedia'], ['2', 1, 1350000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Polonia Medan', 'tipe' => 'putra', 'kota' => 'Medan',
                'alamat' => 'Polonia', 'lat' => 3.5789, 'lng' => 98.6789,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kasur',
                'deskripsi' => 'Deket USU lewat jalan belakang, angkot lewat depan gang. Bapak kosnya orang Batak yang ramah.',
                'harga' => 850000, 'asli' => null,
                'kamar' => [['1', 1, 850000, 'tersedia'], ['2', 1, 850000, 'tersedia']],
            ],
            [
                'nama' => 'Kost Setia Budi Medan', 'tipe' => 'campur', 'kota' => 'Medan',
                'alamat' => 'Setia Budi', 'lat' => 3.5912, 'lng' => 98.6545,
                'fasilitas' => 'K. Mandi Dalam·WiFi·AC·Kloset Duduk·Kasur',
                'deskripsi' => 'Daerah elitnya anak kos Medan, kafe-kafe pada deket. Kamarnya luas-luas.',
                'harga' => 1500000, 'asli' => 1639500,
                'kamar' => [['1', 1, 1500000, 'tersedia'], ['2', 1, 1500000, 'terisi'], ['3', 2, 1700000, 'tersedia']],
            ],
        ];

        $komentars = [
            [5, 'Kamarnya sesuai foto, air sama wifi lancar. Ibu kosnya baik.'],
            [5, 'Udah 2 tahun di sini, nggak ada komplain berarti. Recommended.'],
            [4, 'Lumayan lah buat harga segini. Parkiran agak sempit aja.'],
            [5, 'Bersih, aman, tetangga kosnya asik-asik. Betah.'],
            [4, 'Deket kampus banget, jalan kaki 5 menit. Kamar mandinya kecil dikit.'],
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

            // Ulasan contoh biar rating 4.x muncul di kartu (maks 2 per kos).
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
