<?php

namespace Database\Seeders;

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use App\Models\BarangKos;
use App\Models\Properti;
use App\Models\User;
use Illuminate\Database\Seeder;

class BarangKosSeeder extends Seeder
{
    public function run(): void
    {
        $rina = User::where('email', 'anak1@ngekos.test')->first();
        $yoga = User::where('email', 'anak2@ngekos.test')->first();
        $maya = User::where('email', 'anak3@ngekos.test')->first();
        $budi = User::where('email', 'pemilik1@ngekos.test')->first();
        $siti = User::where('email', 'pemilik2@ngekos.test')->first();

        // Penjual dari kota lain.
        $dinda = User::where('email', 'anak4@ngekos.test')->first();
        $fajar = User::where('email', 'anak5@ngekos.test')->first();
        $sinta = User::where('email', 'anak6@ngekos.test')->first();
        $bima = User::where('email', 'anak7@ngekos.test')->first();
        $rudi = User::where('email', 'pemilik3@ngekos.test')->first();
        $lestari = User::where('email', 'pemilik4@ngekos.test')->first();

        // Fallback jika belum di-seed
        if (! $rina || ! $budi) {
            return;
        }

        $melati = Properti::where('nama', 'like', '%Melati%')->first();
        $mawar = Properti::where('nama', 'like', '%Mawar%')->first();
        $bandung = Properti::where('kota', 'Bandung')->first();
        $surabaya = Properti::where('kota', 'Surabaya')->first();
        $palembang = Properti::where('kota', 'Palembang')->first();
        $jakarta = Properti::where('kota', 'like', 'Jakarta%')->first();
        $malang = Properti::where('kota', 'Malang')->first();
        $semarang = Properti::where('kota', 'Semarang')->first();

        $daftarBarang = [
            [
                'user_id' => $rina->id,
                'properti_id' => $melati?->id,
                'nama' => 'Kipas Angin Berdiri Miyako 16 Inch',
                'foto' => 'images/barang/kipas-angin.jpg',
                'kategori' => BarangKategori::Elektronik,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 135000,
                'bisa_nego' => true,
                'deskripsi' => 'Dijual karena mau wisuda dan pindah ke luar kota. Kondisi normal jaya, putaran kencang, 3 tingkat kecepatan berfungsi semua, osilasi geleng kepala lancar.',
                'minus' => 'Kardus asli sudah tidak ada, pemakaian wajar 8 bulan.',
                'kota' => 'Yogyakarta',
                'lokasi_detail' => 'Dekat Kampus UGM, Kos Melati (Pogung Baru)',
                'no_wa' => $rina->no_hp ?? '081234567006',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 42,
            ],
            [
                'user_id' => $budi->id,
                'properti_id' => $melati?->id,
                'nama' => 'Kulkas Mini Portable Sharp 50L Hemat Listrik',
                'foto' => 'images/barang/kulkas-mini.jpg',
                'kategori' => BarangKategori::Elektronik,
                'kondisi' => BarangKondisi::BekasSepertiBaru,
                'harga' => 850000,
                'bisa_nego' => true,
                'deskripsi' => 'Kulkas mini bekas kamar VIP kosan yang baru selesai renovasi. Dingin sangat cepat, hemat listrik cuma 50 Watt, ada kunci pintu dan rak tempered glass.',
                'minus' => 'Tidak ada minus fungsi, body mulus 95%.',
                'kota' => 'Yogyakarta',
                'lokasi_detail' => 'Kos Melati, Jl. Kaliurang KM 5',
                'no_wa' => $budi->no_hp ?? '081234567002',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 118,
            ],
            [
                'user_id' => $yoga->id,
                'properti_id' => $melati?->id,
                'nama' => 'Meja Belajar Lipat Minimalis Kayu + Dudukan Laptop',
                'foto' => 'images/barang/meja-belajar.jpg',
                'kategori' => BarangKategori::Perabot,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 65000,
                'bisa_nego' => false,
                'deskripsi' => 'Meja lipat cocok buat rebahan atau nugas di atas kasur/lantai. Ada slot tempat tablet/HP dan tempat gelas minum. Sangat kokoh.',
                'minus' => 'Sedikit baret halus di sudut kiri bawah, tidak mengganggu.',
                'kota' => 'Yogyakarta',
                'lokasi_detail' => 'Area Pogung Dalangan / UGM',
                'no_wa' => $yoga?->no_hp ?? '081234567007',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 65,
            ],
            [
                'user_id' => $siti?->id ?? $budi->id,
                'properti_id' => $mawar?->id,
                'nama' => 'Kasur Busa Single Inoac Ukuran 90x200 cm Tebal 15 cm',
                'foto' => 'images/barang/kasur-busa.jpg',
                'kategori' => BarangKategori::Perabot,
                'kondisi' => BarangKondisi::Baru,
                'harga' => 490000,
                'bisa_nego' => false,
                'deskripsi' => 'Stok sisa pengadaan kasur kamar baru kos. Masih dalam plastik segel pabrik, busa tidak gampang kempes garansi 5 tahun. Siap ambil ke lokasi kos.',
                'minus' => null,
                'kota' => 'Yogyakarta',
                'lokasi_detail' => 'Kos Mawar, Condongcatur (Dekat UPN)',
                'no_wa' => $siti?->no_hp ?? '081234567003',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 91,
            ],
            [
                'user_id' => $maya?->id ?? $rina->id,
                'properti_id' => null,
                'nama' => 'Rice Cooker Cosmos 0.8L Mini Anak Kos',
                'foto' => 'images/barang/rice-cooker.jpg',
                'kategori' => BarangKategori::Dapur,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 120000,
                'bisa_nego' => true,
                'deskripsi' => 'Kapasitas 0.8 liter pas banget buat 1-2 orang anak kos. Lapisan panci masih hitam mulus belum terkelupas. Lengkap centong dan kabel colokan.',
                'minus' => 'Dus hilang saat pindahan.',
                'kota' => 'Yogyakarta',
                'lokasi_detail' => 'Bisa COD area UGM, UNY, atau Jakal KM 4',
                'no_wa' => $maya?->no_hp ?? '081234567008',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 84,
            ],
            [
                'user_id' => $rina->id,
                'properti_id' => $melati?->id,
                'nama' => 'Dispenser Galon Atas Miyako Hot & Normal',
                'foto' => 'images/barang/dispenser.jpg',
                'kategori' => BarangKategori::Elektronik,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 95000,
                'bisa_nego' => false,
                'deskripsi' => 'Air panas masih sangat cepat mendidih, cocok buat seduh kopi / mie instan malam-malam. Kran air lancar tanpa bocor.',
                'minus' => 'Warna sedikit menguning karena sinar lampu kosan.',
                'kota' => 'Yogyakarta',
                'lokasi_detail' => 'Kos Melati Pogung Baru',
                'no_wa' => $rina->no_hp ?? '081234567006',
                'status' => BarangStatus::Terjual,
                'dilihat' => 150,
            ],
            [
                'user_id' => $budi->id,
                'properti_id' => $melati?->id,
                'nama' => 'Jemuran Baju Lipat Aluminium 1 Meter',
                'foto' => 'images/barang/jemuran.jpg',
                'kategori' => BarangKategori::Kebersihan,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 80000,
                'bisa_nego' => true,
                'deskripsi' => 'Jemuran dinding lipat bahan aluminium tebal anti karat. Sangat praktis buat balkon atau kamar kos minimalis.',
                'minus' => 'Baut fisher dinding perlu beli sendiri (Rp 3.000 di toko bangunan).',
                'kota' => 'Yogyakarta',
                'lokasi_detail' => 'Kos Melati',
                'no_wa' => $budi->no_hp ?? '081234567002',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 37,
            ],

            // ===== Bandung =====
            [
                'user_id' => $dinda?->id,
                'properti_id' => $bandung?->id,
                'nama' => 'Blender Portable Mini USB 380ml',
                'foto' => 'images/barang/blender-portable.jpg',
                'kategori' => BarangKategori::Dapur,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 85000,
                'bisa_nego' => true,
                'deskripsi' => 'Blender portable muat di tas, cocok buat bikin jus atau smoothie di kamar kos. Bisa di-charge lewat USB, kapasitas 380 ml, mata pisau stainless masih tajam.',
                'minus' => 'Sudah tidak ada dus, tinggal unit dan kabel charger.',
                'kota' => 'Bandung',
                'lokasi_detail' => 'Cisitu Lama, dekat ITB',
                'no_wa' => $dinda?->no_hp ?? '081234567009',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 58,
            ],
            [
                'user_id' => $dinda?->id,
                'properti_id' => $bandung?->id,
                'nama' => 'Setrika Listrik Philips Anti Lengket',
                'foto' => 'images/barang/setrika-listrik.jpg',
                'kategori' => BarangKategori::Elektronik,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 95000,
                'bisa_nego' => false,
                'deskripsi' => 'Setrika listrik 350 Watt dengan alas anti lengket, panasnya cepat dan merata. Cocok buat setrika seragam kuliah atau kerja.',
                'minus' => 'Kabel masih original, body ada baret halus pemakaian.',
                'kota' => 'Bandung',
                'lokasi_detail' => 'Dago Atas, dekat ITB',
                'no_wa' => $dinda?->no_hp ?? '081234567009',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 31,
            ],

            // ===== Surabaya =====
            [
                'user_id' => $fajar?->id,
                'properti_id' => $surabaya?->id,
                'nama' => 'Lemari Plastik 3 Susun Napolly',
                'foto' => 'images/barang/lemari-plastik.jpg',
                'kategori' => BarangKategori::Perabot,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 150000,
                'bisa_nego' => true,
                'deskripsi' => 'Lemari plastik 3 susun warna netral, muat banyak baju dan perlengkapan mandi. Roda masih lancar, tidak ada retak.',
                'minus' => 'Satu gagang laci agak longgar, masih bisa dipakai normal.',
                'kota' => 'Surabaya',
                'lokasi_detail' => 'Ploso, dekat UNAIR Kampus B',
                'no_wa' => $fajar?->no_hp ?? '081234567010',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 47,
            ],

            // ===== Palembang =====
            [
                'user_id' => $sinta?->id,
                'properti_id' => $palembang?->id,
                'nama' => 'Kompor Induksi Portable Maspion 1200W',
                'foto' => 'images/barang/kompor-induksi.jpg',
                'kategori' => BarangKategori::Dapur,
                'kondisi' => BarangKondisi::BekasSepertiBaru,
                'harga' => 220000,
                'bisa_nego' => true,
                'deskripsi' => 'Kompor induksi portable 1200 Watt, pemanasan cepat dan lebih aman tanpa api. Ada pengaturan suhu dan timer, hemat listrik untuk masak harian anak kos.',
                'minus' => 'Perlu panci berbahan magnetik, dus sudah tidak ada.',
                'kota' => 'Palembang',
                'lokasi_detail' => 'Bukit Besar, dekat UNSRI',
                'no_wa' => $sinta?->no_hp ?? '081234567011',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 63,
            ],

            // ===== Jakarta =====
            [
                'user_id' => $bima?->id,
                'properti_id' => $jakarta?->id,
                'nama' => 'Kursi Kerja Ergonomis Mesh',
                'foto' => 'images/barang/kursi-kerja.jpg',
                'kategori' => BarangKategori::Perabot,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 450000,
                'bisa_nego' => true,
                'deskripsi' => 'Kursi kerja ergonomis dengan sandaran mesh, tinggi bisa disetel, dan roda lancar. Nyaman buat WFH atau nugas seharian di kamar kos.',
                'minus' => 'Ada sedikit goresan di kaki kursi, busa dudukan masih empuk.',
                'kota' => 'Jakarta Selatan',
                'lokasi_detail' => 'Tebet, dekat Stasiun MRT',
                'no_wa' => $bima?->no_hp ?? '081234567012',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 112,
            ],
            [
                'user_id' => $bima?->id,
                'properti_id' => $jakarta?->id,
                'nama' => 'Router WiFi ASUS TUF AX3000',
                'foto' => 'images/barang/router-wifi.jpg',
                'kategori' => BarangKategori::Elektronik,
                'kondisi' => BarangKondisi::BekasSepertiBaru,
                'harga' => 650000,
                'bisa_nego' => false,
                'deskripsi' => 'Router WiFi 6 dual band, sinyal kuat sampai kamar lantai atas. Cocok buat kos yang penghuninya banyak perangkat. Lengkap adaptor dan kabel LAN.',
                'minus' => 'Dus original sudah hilang, unit berfungsi normal.',
                'kota' => 'Jakarta Selatan',
                'lokasi_detail' => 'Kemang, Jakarta Selatan',
                'no_wa' => $bima?->no_hp ?? '081234567012',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 89,
            ],

            // ===== Malang =====
            [
                'user_id' => $rudi?->id,
                'properti_id' => $malang?->id,
                'nama' => 'Rak Buku Minimalis 4 Tingkat',
                'foto' => 'images/barang/rak-buku.jpg',
                'kategori' => BarangKategori::Perabot,
                'kondisi' => BarangKondisi::Baru,
                'harga' => 275000,
                'bisa_nego' => false,
                'deskripsi' => 'Rak buku minimalis 4 tingkat dari kayu olahan finishing natural. Stok pengadaan kamar kos baru, masih rapi dan kokoh. Bisa juga buat menyimpan perlengkapan belajar.',
                'minus' => null,
                'kota' => 'Malang',
                'lokasi_detail' => 'Lowokwaru, dekat Universitas Brawijaya',
                'no_wa' => $rudi?->no_hp ?? '081234567013',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 26,
            ],

            // ===== Semarang =====
            [
                'user_id' => $lestari?->id,
                'properti_id' => $semarang?->id,
                'nama' => 'Mesin Cuci Mini Portable 3kg',
                'foto' => 'images/barang/mesin-cuci.jpg',
                'kategori' => BarangKategori::Elektronik,
                'kondisi' => BarangKondisi::BekasMulus,
                'harga' => 750000,
                'bisa_nego' => true,
                'deskripsi' => 'Mesin cuci mini kapasitas 3 kg, pas untuk kebutuhan harian 1-2 orang. Pengering putar berfungsi, hemat listrik dan tidak perlu tempat luas.',
                'minus' => 'Selang pembuangan diganti baru, body mulus 90%.',
                'kota' => 'Semarang',
                'lokasi_detail' => 'Tembalang, dekat UNDIP',
                'no_wa' => $lestari?->no_hp ?? '081234567014',
                'status' => BarangStatus::Tersedia,
                'dilihat' => 74,
            ],
        ];

        foreach ($daftarBarang as $item) {
            if (empty($item['user_id'])) {
                continue;
            }

            BarangKos::updateOrCreate(
                ['nama' => $item['nama'], 'user_id' => $item['user_id']],
                $item
            );
        }
    }
}
