<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_kos_akun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang_kos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['barang_id', 'user_id']);
            $table->index('user_id');
        });

        // Barang lama otomatis tampil di toko pemiliknya agar katalog tidak
        // kosong setelah fitur kurasi barang -> akun aktif.
        $sekarang = now();

        DB::table('barang_kos')
            ->select(['id', 'user_id'])
            ->orderBy('id')
            ->chunkById(200, function ($barangs) use ($sekarang) {
                $baris = $barangs->map(fn ($barang) => [
                    'barang_id' => $barang->id,
                    'user_id' => $barang->user_id,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ])->all();

                if ($baris !== []) {
                    DB::table('barang_kos_akun')->insertOrIgnore($baris);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_kos_akun');
    }
};
