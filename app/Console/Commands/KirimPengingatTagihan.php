<?php

namespace App\Console\Commands;

use App\Services\TagihanReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class KirimPengingatTagihan extends Command
{
    protected $signature = 'app:kirim-pengingat-tagihan';

    protected $description = 'Kirim pengingat tagihan H-3, H-1, H0, dan peringatan telat harian ke anak kos';

    public function handle(): int
    {
        $hasil = TagihanReminderService::kirimHarian(Carbon::today());

        $this->info("Cek {$hasil['cek']} tagihan: {$hasil['kirim']} terkirim, {$hasil['lewati']} dilewati.");
        $this->components->info('Pengingat tagihan selesai.');

        return self::SUCCESS;
    }
}
