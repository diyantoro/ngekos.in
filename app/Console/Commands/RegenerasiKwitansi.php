<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use App\Services\KwitansiService;
use Illuminate\Console\Command;

class RegenerasiKwitansi extends Command
{
    protected $signature = 'app:regenerasi-kwitansi {--dry-run : Tampilkan yang akan diproses tanpa mengubah data}';

    protected $description = 'Regenerasi ulang semua PDF kwitansi pembayaran terverifikasi (mis. setelah template berubah)';

    public function handle(): int
    {
        $query = Pembayaran::where('status', 'diverifikasi');

        if ($this->option('dry-run')) {
            $this->info("Dry-run: {$query->count()} kwitansi akan diregenerasi.");

            return self::SUCCESS;
        }

        $sukses = 0;
        $gagal = 0;

        foreach ($query->lazyById(100) as $pembayaran) {
            try {
                KwitansiService::untuk($pembayaran, true);
                $sukses++;
            } catch (\Throwable $e) {
                report($e);
                $gagal++;
            }
        }

        $this->info("Kwitansi diregenerasi: {$sukses} sukses, {$gagal} gagal.");

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
