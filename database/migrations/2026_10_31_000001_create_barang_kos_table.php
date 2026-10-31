<?php

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_kos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('properti_id')->nullable()->constrained('propertis')->nullOnDelete();
            $table->string('nama', 150);
            $table->enum('kategori', BarangKategori::values())->default(BarangKategori::Lainnya->value)->index();
            $table->enum('kondisi', BarangKondisi::values())->default(BarangKondisi::BekasMulus->value)->index();
            $table->unsignedBigInteger('harga');
            $table->boolean('bisa_nego')->default(false);
            $table->text('deskripsi');
            $table->text('minus')->nullable();
            $table->string('foto', 255)->nullable();
            $table->json('foto_tambahan')->nullable();
            $table->string('kota', 60)->index();
            $table->string('lokasi_detail', 255)->nullable();
            $table->string('no_wa', 25);
            $table->enum('status', BarangStatus::values())->default(BarangStatus::Tersedia->value)->index();
            $table->unsignedInteger('dilihat')->default(0);
            $table->timestamps();

            $table->index(['status', 'kota']);
            $table->index(['user_id', 'status']);
            $table->index(['kategori', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_kos');
    }
};
