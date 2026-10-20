<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spesifikasi tipe kamar (opsional) agar halaman detail bisa
     * menampilkan ukuran, status listrik, deposit, dan peraturan
     * khusus per tipe kamar seperti contoh Mamikos.
     */
    public function up(): void
    {
        Schema::table('kamars', function (Blueprint $table) {
            $table->string('ukuran', 50)->nullable();
            $table->boolean('termasuk_listrik')->nullable();
            $table->decimal('deposit', 12, 2)->nullable();
            $table->text('peraturan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('kamars', function (Blueprint $table) {
            $table->dropColumn(['ukuran', 'termasuk_listrik', 'deposit', 'peraturan']);
        });
    }
};
