<?php

use App\Enums\AnggotaStatus;
use App\Enums\KamarStatus;
use App\Enums\ModeHunian;
use App\Enums\PembayaranMetode;
use App\Enums\PembayaranStatus;
use App\Enums\PenyewaanStatus;
use App\Enums\PropertiStatus;
use App\Enums\TagihanStatus;
use App\Enums\TipeHunian;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema domain ternormalisasi (hasil squash):
     * - Pilihan tetap memakai native ENUM (role/status/jenis/tipe/metode).
     * - Varchar dirapatkan (nama kos 100, kota 50, alamat 500, nama kamar 50,
     *   periode 100, nomor kwitansi 50); konten panjang memakai TEXT
     *   (deskripsi/fasilitas/aturan/catatan/keterangan).
     * - Harga + fasilitas ada di KAMAR (per kamar); harga di properti hanya
     *   ringkasan tampilan (nullable). Kolom jenis_harga yang redundan dibuang:
     *   periode aktif ditentukan dari kolom harga yang terisi.
     * - Tabel bookings yang sudah tidak dipakai tidak dibuat lagi.
     * - Kolom foto tunggal dipertahankan sebagai cache cover galeri.
     */
    public function up(): void
    {
        Schema::create('propertis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilik_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('kota', 50)->nullable();
            $table->string('alamat', 500)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('fasilitas')->nullable();
            $table->text('aturan')->nullable();
            $table->decimal('denda_per_hari', 12, 2)->nullable();
            // Ringkasan harga tampilan (sumber utama harga ada di kamars).
            $table->decimal('harga', 12, 2)->nullable();
            $table->decimal('harga_mingguan', 12, 2)->nullable();
            $table->decimal('harga_harian', 12, 2)->nullable();
            $table->decimal('harga_asli', 12, 2)->nullable();
            $table->enum('status', PropertiStatus::values())->default(PropertiStatus::Aktif->value);
            $table->enum('tipe_hunian', TipeHunian::values())->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['pemilik_id', 'status']);
            $table->index(['status', 'kota']);
            $table->index('tipe_hunian');
        });

        Schema::create('kamars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('properti_id')->constrained('propertis')->cascadeOnDelete();
            $table->string('nama', 50);
            $table->unsignedSmallInteger('kapasitas')->default(1);
            // Harga per kamar: bulanan wajib, mingguan/harian opsional.
            $table->decimal('harga_sewa_bulanan', 12, 2);
            $table->decimal('harga_sewa_mingguan', 12, 2)->nullable();
            $table->decimal('harga_sewa_harian', 12, 2)->nullable();
            $table->decimal('harga_asli', 12, 2)->nullable();
            // Fasilitas per kamar (TEXT, koma-dipisah seperti properti).
            $table->text('fasilitas')->nullable();
            $table->enum('status', KamarStatus::values())->default(KamarStatus::Tersedia->value);
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['properti_id', 'status']);
        });

        Schema::create('admin_properti', function (Blueprint $table) {
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('properti_id')->constrained('propertis')->cascadeOnDelete();
            $table->primary(['admin_id', 'properti_id']);
        });

        Schema::create('penyewaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_kos_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->foreignId('properti_id')->constrained('propertis')->cascadeOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', PenyewaanStatus::values())->default(PenyewaanStatus::Aktif->value);
            $table->timestamp('permintaan_keluar_pada')->nullable();
            $table->string('ktp_path')->nullable();
            $table->enum('mode_hunian', ModeHunian::values())->default(ModeHunian::Tunggal->value);
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyewaan_id')->constrained('penyewaans')->cascadeOnDelete();
            $table->string('periode', 100);
            $table->decimal('jumlah', 12, 2);
            $table->decimal('denda', 12, 2)->default(0);
            $table->date('jatuh_tempo');
            $table->enum('status', TagihanStatus::values())->default(TagihanStatus::BelumBayar->value);
            $table->timestamps();
            $table->index(['penyewaan_id', 'status']);
        });

        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihans')->cascadeOnDelete();
            $table->foreignId('anak_kos_id')->constrained('users')->cascadeOnDelete();
            $table->enum('metode', PembayaranMetode::values())->default(PembayaranMetode::Transfer->value);
            $table->decimal('jumlah', 12, 2);
            $table->string('bukti')->nullable();
            $table->enum('status', PembayaranStatus::values())->default(PembayaranStatus::MenungguVerifikasi->value);
            $table->string('nomor_kwitansi', 50)->nullable()->unique();
            $table->string('file_kwitansi')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['anak_kos_id', 'status']);
            $table->index(['status', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
        Schema::dropIfExists('tagihans');
        Schema::dropIfExists('penyewaans');
        Schema::dropIfExists('admin_properti');
        Schema::dropIfExists('kamars');
        Schema::dropIfExists('propertis');
    }
};
