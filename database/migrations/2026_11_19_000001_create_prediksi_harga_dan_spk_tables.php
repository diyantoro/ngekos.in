<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema Modul Prediksi Harga Wajar (JST) dan SPK Pemeringkatan (TOPSIS).
     */
    public function up(): void
    {
        // 1. Titik Acuan (Kampus, Landmark, dll.)
        Schema::create('titik_acuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('kategori', 50)->default('kampus');
            $table->timestamps();
        });

        // 2. Data Latih Model
        Schema::create('data_latih', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->nullable()->constrained('kamars')->nullOnDelete();
            $table->enum('sumber', ['survei', 'ngekos'])->default('survei');
            $table->decimal('luas_m2', 6, 2);
            $table->decimal('jarak_km', 8, 3);
            $table->string('kawasan', 100);
            $table->string('tipe_penghuni', 50);
            $table->json('fasilitas');
            $table->decimal('harga_aktual', 12, 2);
            $table->boolean('ditandai_outlier')->default(false);
            $table->timestamps();

            $table->index('ditandai_outlier');
            $table->index('sumber');
        });

        // 3. Model Versi
        Schema::create('model_versi', function (Blueprint $table) {
            $table->id();
            $table->string('versi', 50)->unique();
            $table->string('algoritma', 100)->default('mlp');
            $table->json('hyperparameter')->nullable();
            $table->decimal('mae', 12, 2)->nullable();
            $table->decimal('rmse', 12, 2)->nullable();
            $table->decimal('r2', 6, 4)->nullable();
            $table->decimal('mape', 6, 2)->nullable();
            $table->string('artifact_path', 255)->nullable();
            $table->enum('status', ['aktif', 'arsip'])->default('arsip');
            $table->timestamp('dilatih_pada')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // 4. Prediksi Harga Wajar
        Schema::create('prediksi_harga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->foreignId('model_versi_id')->nullable()->constrained('model_versi')->nullOnDelete();
            $table->decimal('harga_prediksi', 12, 2);
            $table->decimal('selisih_persen', 6, 2);
            $table->enum('label_kewajaran', ['di_bawah_pasar', 'wajar', 'di_atas_pasar']);
            $table->timestamps();

            $table->index(['kamar_id', 'model_versi_id']);
            $table->index('label_kewajaran');
        });

        // 5. Kriteria SPK
        Schema::create('kriteria', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->enum('tipe', ['benefit', 'cost']);
            $table->decimal('bobot_default', 5, 4)->default(0.2000);
            $table->timestamps();
        });

        // 6. Profil Preferensi Pencari Kos
        Schema::create('profil_preferensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('titik_acuan_id')->constrained('titik_acuan')->cascadeOnDelete();
            $table->string('nama_profil', 100);
            $table->enum('metode', ['topsis', 'ahp'])->default('topsis');
            $table->decimal('budget_maks', 12, 2)->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        // 7. Bobot Preferensi per Kriteria
        Schema::create('bobot_preferensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_id')->constrained('profil_preferensi')->cascadeOnDelete();
            $table->foreignId('kriteria_id')->constrained('kriteria')->cascadeOnDelete();
            $table->decimal('bobot', 5, 4);
            $table->timestamps();

            $table->unique(['profil_id', 'kriteria_id']);
        });

        // 8. Sesi Pencarian SPK
        Schema::create('sesi_pencarian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('profil_id')->nullable()->constrained('profil_preferensi')->nullOnDelete();
            $table->json('filter')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
        });

        // 9. Hasil Peringkat SPK TOPSIS
        Schema::create('hasil_peringkat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_pencarian')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->decimal('skor_preferensi', 6, 5);
            $table->unsignedInteger('peringkat');
            $table->json('detail_skor')->nullable();
            $table->timestamps();

            $table->index(['sesi_id', 'peringkat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_peringkat');
        Schema::dropIfExists('sesi_pencarian');
        Schema::dropIfExists('bobot_preferensi');
        Schema::dropIfExists('profil_preferensi');
        Schema::dropIfExists('kriteria');
        Schema::dropIfExists('prediksi_harga');
        Schema::dropIfExists('model_versi');
        Schema::dropIfExists('data_latih');
        Schema::dropIfExists('titik_acuan');
    }
};
