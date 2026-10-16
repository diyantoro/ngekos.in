<?php

namespace App\Console\Commands;

use App\Models\Properti;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class EnkripsiRekening extends Command
{
    protected $signature = 'ngekos:rekening-enkripsi {--dry : Tampilkan yang akan diubah tanpa menyimpan}';

    protected $description = 'Enkripsi nilai rekening plaintext lama ke cast encrypted (idempoten, lewati yang sudah terenkripsi)';

    private const KOLOM = ['bank_nama', 'no_rekening', 'rekening_nama'];

    public function handle(): int
    {
        $diubah = 0;

        foreach (Properti::all() as $p) {
            $kotor = false;

            foreach (self::KOLOM as $kolom) {
                $mentah = $p->getRawOriginal($kolom);

                if (blank($mentah) || $this->sudahTerenkripsi($mentah)) {
                    continue;
                }

                $this->line("[#{$p->id}] {$p->nama}: {$kolom} plaintext -> terenkripsi");

                if (! $this->option('dry')) {
                    // Tulis ulang nilai mentah yang sama: cast 'encrypted'
                    // mengenkripsinya saat disimpan. (Jangan baca via
                    // $p->{$kolom}: cast akan mencoba dekrip plaintext
                    // dan melempar DecryptException.)
                    $p->{$kolom} = $mentah;
                    $kotor = true;
                }

                $diubah++;
            }

            if ($kotor && ! $this->option('dry')) {
                $p->save();
            }
        }

        $this->info("Selesai. {$diubah} nilai dienkripsi.".($this->option('dry') ? ' (mode dry-run)' : ''));

        return self::SUCCESS;
    }

    /**
     * Ciphertext Laravel selalu lolos decrypt; plaintext selalu gagal
     * (MAC tidak valid). Catatan: cast 'encrypted' memakai
     * encrypt/decrypt TANPA serialize, jadi deteksi harus memakai
     * parameter yang sama. Tangkap Throwable agar payload korup
     * tidak meledakkan command (dianggap belum terenkripsi).
     */
    private function sudahTerenkripsi(?string $nilai): bool
    {
        if (blank($nilai)) {
            return true;
        }

        try {
            Crypt::decrypt($nilai, false);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
