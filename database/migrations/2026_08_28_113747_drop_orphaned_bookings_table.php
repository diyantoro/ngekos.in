<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buang tabel `bookings` yang sudah tidak dipakai.
     * Model Booking telah dihapus karena alur diganti menjadi sewa langsung (penyewaans).
     */
    public function up(): void
    {
        Schema::dropIfExists('bookings');
    }

    public function down(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_kos_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->date('tanggal_booking');
            $table->string('status')->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
};
