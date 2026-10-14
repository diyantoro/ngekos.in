<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema DP Rp100.000 + sewa bulanan 1 bulan + perpanjangan H-3.
     * - penyewaans.tanggal_berakhir: akhir masa sewa 1 bulan (masuk + durasi - 1 hari).
     * - penyewaans.perpanjangan_status: null | ditawarkan | tidak
     *   (lanjut langsung memperpanjang tanggal_berakhir lalu direset ke null).
     * - penyewaans.perpanjangan_ditawarkan_pada: tanggal tawaran H-3 dikirim.
     * - tagihans.jenis: 'dp' | 'sewa' (DP mengurangi tagihan bulan pertama).
     */
    public function up(): void
    {
        Schema::table('penyewaans', function (Blueprint $table) {
            $table->date('tanggal_berakhir')->nullable()->after('tanggal_keluar');
            $table->string('perpanjangan_status', 20)->nullable()->after('tanggal_berakhir');
            $table->date('perpanjangan_ditawarkan_pada')->nullable()->after('perpanjangan_status');
        });

        Schema::table('tagihans', function (Blueprint $table) {
            $table->string('jenis', 10)->default('sewa')->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->dropColumn(['tanggal_berakhir', 'perpanjangan_status', 'perpanjangan_ditawarkan_pada']);
        });
    }
};
