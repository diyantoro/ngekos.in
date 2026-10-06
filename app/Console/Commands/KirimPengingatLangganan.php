<?php

namespace App\Console\Commands;

use App\Services\LanggananReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class KirimPengingatLangganan extends Command
{
    protected $signature = 'app:kirim-pengingat-langganan';

    protected $description = 'Kirim pengingat H-3 paket PRO/Business akan berakhir ke pemilik';

    public function handle(): int
    {
        $hasil = LanggananReminderService::kirimHarian(Carbon::today());

        $this->info("Cek {$hasil['cek']} langganan: {$hasil['kirim']} terkirim, {$hasil['lewati']} dilewati.");

        return self::SUCCESS;
    }
}
