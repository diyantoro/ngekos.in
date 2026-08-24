<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('tanggal_masuk')->nullable()->after('tanggal_booking');
            $table->unsignedTinyInteger('durasi_bulan')->default(1)->after('tanggal_masuk');
        });

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->timestamp('permintaan_keluar_pada')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['tanggal_masuk', 'durasi_bulan']);
        });

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->dropColumn('permintaan_keluar_pada');
        });
    }
};
