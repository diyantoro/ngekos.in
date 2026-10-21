<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Hapus seluruh sisa fitur KTP: file lama di disk dan kolom ktp_path.
 *
 * Aplikasi tidak lagi meminta/menampilkan KTP. Data identitas sensitif
 * tidak boleh disimpan lebih lama dari yang diperlukan (UU PDP).
 */
return new class extends Migration
{
    private const TABEL_KOLOM = [
        'penyewaans' => 'ktp_path',
        'penyewaan_anggotas' => 'ktp_path',
    ];

    public function up(): void
    {
        // Jangan sentuh storage asli saat test suite berjalan.
        if (! app()->runningUnitTests()) {
            $this->hapusBerkasKtp();
        }

        foreach (self::TABEL_KOLOM as $tabel => $kolom) {
            if (! Schema::hasTable($tabel) || ! Schema::hasColumn($tabel, $kolom)) {
                continue;
            }

            DB::table($tabel)->whereNotNull($kolom)->update([$kolom => null]);

            Schema::table($tabel, function (Blueprint $table) use ($kolom) {
                $table->dropColumn($kolom);
            });
        }
    }

    public function down(): void
    {
        // Kolom boleh dibuat ulang, tetapi file & data KTP sengaja tidak
        // dipulihkan karena sudah dihapus permanen.
        foreach (self::TABEL_KOLOM as $tabel => $kolom) {
            if (! Schema::hasTable($tabel) || Schema::hasColumn($tabel, $kolom)) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table) use ($kolom) {
                $table->string($kolom)->nullable();
            });
        }
    }

    private function hapusBerkasKtp(): void
    {
        foreach (['private', 'public'] as $namaDisk) {
            $disk = Storage::disk($namaDisk);

            foreach ($disk->directories() as $direktori) {
                $nama = basename($direktori);

                if ($nama === 'ktp' || str_starts_with($nama, 'ktp.')) {
                    $disk->deleteDirectory($direktori);
                }
            }

            foreach ($disk->files() as $berkas) {
                $nama = basename($berkas);

                if ($nama === 'ktp' || str_starts_with($nama, 'ktp.')) {
                    $disk->delete($berkas);
                }
            }
        }
    }
};
