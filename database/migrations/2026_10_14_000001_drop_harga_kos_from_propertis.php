<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus harga level kos: sumber harga resmi hanya di kamars
     * (harga_sewa_bulanan/mingguan/harian + harga_asli). Kolom ringkasan
     * di propertis tidak lagi dipakai sejak form kos dihapus fieldnya.
     */
    public function up(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->dropColumn(['harga', 'harga_mingguan', 'harga_harian', 'harga_asli']);
        });
    }

    public function down(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->decimal('harga', 12, 2)->nullable();
            $table->decimal('harga_mingguan', 12, 2)->nullable();
            $table->decimal('harga_harian', 12, 2)->nullable();
            $table->decimal('harga_asli', 12, 2)->nullable();
        });
    }
};
