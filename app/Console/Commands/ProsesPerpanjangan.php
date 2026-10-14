<?php

namespace App\Console\Commands;

use App\Services\PerpanjanganService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ProsesPerpanjangan extends Command
{
    protected $signature = 'app:proses-perpanjangan';

    protected $description = 'Tawarkan perpanjangan H-3 sewa bulanan dan tutup otomatis sewa yang berakhir (kamar kembali tersedia)';

    public function handle(): int
    {
        $hasil = PerpanjanganService::prosesHarian(Carbon::today());

        $this->info("Perpanjangan: {$hasil['ditawarkan']} ditawarkan, {$hasil['ditutup']} ditutup otomatis.");
        $this->components->info('Proses perpanjangan selesai.');

        return self::SUCCESS;
    }
}
