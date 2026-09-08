<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Harga harian + harga asli (untuk tampilan diskon dicoret) pada properti
     * dan kamar. Harga sewa yang berlaku tetap harga_sewa_bulanan / harga;
     * harga_asli hanya untuk menampilkan nominal awal yang dicoret.
     */
    public function up(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->decimal('harga_harian', 12, 2)->nullable()->after('harga');
            $table->decimal('harga_asli', 12, 2)->nullable()->after('jenis_harga');
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->decimal('harga_sewa_harian', 12, 2)->nullable()->after('harga_sewa_bulanan');
            $table->decimal('harga_asli', 12, 2)->nullable()->after('jenis_harga');
        });
    }

    public function down(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->dropColumn(['harga_harian', 'harga_asli']);
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->dropColumn(['harga_sewa_harian', 'harga_asli']);
        });
    }
};
