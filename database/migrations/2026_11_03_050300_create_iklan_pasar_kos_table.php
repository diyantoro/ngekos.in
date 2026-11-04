<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iklan_pasar_kos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('barang_kos_id')->nullable()->constrained('barang_kos')->onDelete('set null');
            $table->string('judul', 100)->nullable();
            $table->string('deskripsi', 255)->nullable();
            $table->string('gambar', 255)->nullable();
            $table->string('link', 500)->nullable();
            $table->string('gradient', 100)->default('from-brand-700 via-brand-600 to-emerald-600');
            $table->enum('posisi', ['banner_atas', 'katalog_sisipan'])->default('banner_atas')->index();
            $table->unsignedTinyInteger('akun_jumlah')->default(1)->comment('Berapa akun ditampilkan');
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamp('tayang_dimulai')->nullable()->comment('Waktu mulai tayang');
            $table->timestamp('tayang_sampai')->nullable()->comment('Waktu selesai tayang');
            $table->unsignedTinyInteger('durasi_hari')->default(7)->comment('Durasi tayang maksimal 7 hari');
            $table->integer('biaya')->nullable()->comment('Biaya iklan dalam rupiah');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iklan_pasar_kos');
    }
};
