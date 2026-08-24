<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propertis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('fasilitas')->nullable();
            $table->text('aturan')->nullable();
            $table->decimal('denda_per_hari', 12, 2)->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kamars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('properti_id')->constrained('propertis')->cascadeOnDelete();
            $table->string('nama');
            $table->unsignedSmallInteger('kapasitas')->default(1);
            $table->decimal('harga_sewa_bulanan', 12, 2);
            $table->string('status')->default('tersedia');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admin_properti', function (Blueprint $table) {
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('properti_id')->constrained('propertis')->cascadeOnDelete();
            $table->primary(['admin_id', 'properti_id']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_kos_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->date('tanggal_booking');
            $table->string('status')->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('penyewaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_kos_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->foreignId('properti_id')->constrained('propertis')->cascadeOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });

        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyewaan_id')->constrained('penyewaans')->cascadeOnDelete();
            $table->string('periode');
            $table->decimal('jumlah', 12, 2);
            $table->decimal('denda', 12, 2)->default(0);
            $table->date('jatuh_tempo');
            $table->string('status')->default('belum_bayar');
            $table->timestamps();
        });

        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihans')->cascadeOnDelete();
            $table->foreignId('anak_kos_id')->constrained('users')->cascadeOnDelete();
            $table->string('metode')->default('transfer');
            $table->decimal('jumlah', 12, 2);
            $table->string('bukti')->nullable();
            $table->string('status')->default('menunggu_verifikasi');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
        Schema::dropIfExists('tagihans');
        Schema::dropIfExists('penyewaans');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('admin_properti');
        Schema::dropIfExists('kamars');
        Schema::dropIfExists('propertis');
    }
};
