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

        // Fallback jika belum di-seed
        if (! $rina || ! $budi) {
            return;
        }

        $melati = Properti::where('nama', 'like', '%Melati%')->first();
        $mawar = Properti::where('nama', 'like', '%Mawar%')->first();

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
        ];

        foreach ($daftarBarang as $item) {
            BarangKos::updateOrCreate(
                ['nama' => $item['nama'], 'user_id' => $item['user_id']],
                $item
            );
        }
    }
}
