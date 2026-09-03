<?php

namespace App\Console\Commands;

use App\Models\Properti;
use App\Support\FacilityHelper;
use Illuminate\Console\Command;

class NormalizeFasilitas extends Command
{
    protected $signature = 'ngekos:normalize-fasilitas {--dry : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Menyeragamkan label fasilitas properti ke bentuk kanonik';

    public function handle(): int
    {
        $changed = 0;
        foreach (Properti::all() as $p) {
            $new = FacilityHelper::normalizeString($p->fasilitas);
            if ($new === $p->fasilitas) {
                continue;
            }

            $this->line("[#{$p->id}] {$p->nama}");
            $this->line('  <fg=yellow>Lama</>: '.($p->fasilitas ?? '-'));
            $this->line('  <fg=green>Baru</>: '.($new ?? '-'));

            if (! $this->option('dry')) {
                $p->update(['fasilitas' => $new]);
            }

            $changed++;
        }

        $this->info("Selesai. {$changed} properti berubah.".($this->option('dry') ? ' (mode dry-run)' : ''));

        return self::SUCCESS;
    }
}
