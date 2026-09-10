<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Harga mingguan sekali-input (3-in-1): pemilik mengisi bulanan +
     * mingguan + harian sekaligus saat tambah/ubah kos & kamar.
     * Kolom nullable: periode mingguan nonaktif bila kosong.
     */
    public function up(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->decimal('harga_mingguan', 12, 2)->nullable()->after('harga_harian');
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->decimal('harga_sewa_mingguan', 12, 2)->nullable()->after('harga_sewa_harian');
        });
    }

    public function down(): void
    {
        Schema::table('propertis', function (Blueprint $table) {
            $table->dropColumn('harga_mingguan');
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->dropColumn('harga_sewa_mingguan');
        });
    }
};
