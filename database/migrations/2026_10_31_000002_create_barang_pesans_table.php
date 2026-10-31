<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_pesans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_kos_id')->constrained('barang_kos')->cascadeOnDelete();
            $table->foreignId('penjual_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pembeli_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
            $table->text('pesan');
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            $table->index(['barang_kos_id', 'pembeli_id', 'created_at']);
            $table->index(['penjual_id', 'dibaca_pada']);
            $table->index(['pembeli_id', 'dibaca_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_pesans');
    }
};
