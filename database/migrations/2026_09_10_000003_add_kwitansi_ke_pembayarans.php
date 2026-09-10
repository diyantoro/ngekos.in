<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('nomor_kwitansi')->nullable()->unique()->after('status');
            $table->string('file_kwitansi')->nullable()->after('nomor_kwitansi');
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['nomor_kwitansi', 'file_kwitansi']);
        });
    }
};
