<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status');
            $table->index(['anak_kos_id', 'status']);
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->index(['properti_id', 'status']);
        });

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('tagihans', function (Blueprint $table) {
            $table->index(['penyewaan_id', 'status']);
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->index(['anak_kos_id', 'status']);
            $table->index(['status', 'verified_at']);
        });

        Schema::table('propertis', function (Blueprint $table) {
            $table->index(['pemilik_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['anak_kos_id', 'status']);
            $table->dropIndex(['status']);
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->dropIndex(['properti_id', 'status']);
        });

        Schema::table('penyewaans', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropIndex(['penyewaan_id', 'status']);
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropIndex(['anak_kos_id', 'status']);
            $table->dropIndex(['status', 'verified_at']);
        });

        Schema::table('propertis', function (Blueprint $table) {
            $table->dropIndex(['pemilik_id', 'status']);
        });
    }
};
