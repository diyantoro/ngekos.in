<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_pengingat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihans')->cascadeOnDelete();
            $table->string('jenis', 10);
            $table->date('tanggal_kirim');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['tagihan_id', 'jenis', 'tanggal_kirim']);
            $table->index('tanggal_kirim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_pengingat');
    }
};
