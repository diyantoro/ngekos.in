<?php

namespace Database\Seeders;

use App\Models\IklanPasarKos;
use App\Models\User;
use Illuminate\Database\Seeder;

class IklanPasarKosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil beberapa user anak_kos untuk membuat iklan
        $users = User::where('role', 'anak_kos')->limit(3)->get();

        foreach ($users as $index => $user) {
            // Buat 2 iklan per user (mencapai batas maksimal)
            for ($i = 1; $i <= 2; $i++) {
                IklanPasarKos::create([
                    'user_id' => $user->id,
                    'judul' => "Iklan Katalog {$index}-{$i}: Promo Spesial",
                    'deskripsi' => "Promo spesial untuk barang berkualitas dengan harga termurah. Jangan lewatkan kesempatan ini!",
                    'gambar' => null, // Tidak ada gambar untuk test
                    'link' => 'https://example.com',
                    'gradient' => 'from-amber-500 via-orange-500 to-red-500',
                    'posisi' => 'katalog_sisipan',
                    'akun_jumlah' => 1,
                    'urutan' => $index * 2 + $i,
                    'tayang_dimulai' => now()->subDays(2),
                    'tayang_sampai' => now()->addDays(5),
                    'durasi_hari' => 7,
                    'biaya' => 50000,
                    'status' => true,
                ]);
            }
        }

        $this->command->info('Iklan pasar kos berhasil dibuat!');
    }
}
